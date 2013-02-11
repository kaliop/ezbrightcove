YUI().use('node', 'event', 'panel', 'io', 'json-parse', 'node-event-simulate', function (Y) {
    var baseId;
    var panels = {};
    var loader;
    var requestUrl;

    Y.on( 'domready', function() {
        loader = klpBcBrowse.loaderSelector;
        requestUrl = klpBcBrowse.actionUrl;

        var browseButton = Y.all(".klpbc_browse_brightcove");
        browseButton.on( 'click', function(e) {
            baseId = e.target.get('id').replace("_browse", "");
            Y.one(getHtmlId()).addClass('yui3-skin-sam');
            showPanel(getHtmlId("browse_panel"));
        });
    });

    function showPanel( panelId ) {
        if ( !panels[baseId] ) {
            Y.one(panelId).show();

            panel = new Y.Panel({
                srcNode: panelId,
                width: 1034,
                height: 692,
                centered: true,
                modal: true,
                zIndex: 2
            });

            panel.render();
            panels[baseId] = panel;

            requestUrl = klpBcBrowse.actionUrl;
            requestData();
            handleSearch();
        }

        panels[baseId].show();
    }

    function requestData(page) {
        showLoader();
        Y.on('io:complete', requestComplete, Y);

        if (!page)
            page = 0;
        else
            page = page - 1; // must be zero indexed

        url = requestUrl + "::" + page;
        url += "?ContentType=json";
        var request = Y.io(url);
    }

    function requestComplete(id, o, args) {
        var browsePanelSource = Y.one(getHtmlId("browse_video_template")).get('innerHTML'),
            paginatorSource = Y.one(getHtmlId("browse_paginator_template")).get('innerHTML'),
            items = [];

        response = Y.JSON.parse( o.responseText );
        if (!response.content.videos) return;

        for (var i = 0; i < response.content.videos.length; i++) {
            items.push({
                id: response.content.videos[i].id,
                name: response.content.videos[i].name,
                length: convertMilliSecsToString( response.content.videos[i].length ),
                shortDescription: response.content.videos[i].shortDescription,
                thumbnailURL: response.content.videos[i].thumbnailURL,
                rowClass: i % 2 === 0 ? 'bglight' : 'bgdark'
            });
        }

        var paginator = new klpbcPaginator(
            response.content.page_size,
            response.content.page_number,
            response.content.total_count
        );

        var videosHtml = Mustache.render(
            browsePanelSource, { "items": items }
        );
        var paginatorHtml = Mustache.render(
            paginatorSource, { "paginator": paginator }
        );

        hideLoader();

        Y.one(getVideoContainer()).set('innerHTML', videosHtml);
        Y.one(getPaginatorContainer()).set('innerHTML', paginatorHtml);

        registerVideoClicks();
        registerPaginationButtons();
    }

    function registerVideoClicks() {
        var videos = Y.all(getHtmlId("browse_panel") + " .video");
        videos.on('click', function(e) {
            var brightcoveId = e.currentTarget.one("input").get('value');
            insertVideoId(brightcoveId);
            panels[baseId].hide();
            e.preventDefault();
        });
    }

    function registerPaginationButtons() {
        paginationAction = function(e) {
            var page = e.target.get('hash').replace("#","");
            page = parseInt(page, 10);

            requestData(page);

            e.preventDefault();
        }

        var prevButton = Y.one(
            getHtmlId("browse_panel") + " .paginator .previous"
        );
        if (prevButton) {
            prevButton.on('click', paginationAction);
        }

        var nextButton = Y.one(
            getHtmlId("browse_panel") + " .paginator .next"
        );
        if (nextButton) {
            nextButton.on('click', paginationAction);
        }
    }

    function insertVideoId(id) {
        Y.one( getHtmlId( "brightcove_id" ) ).set('value', id);
    }

    function getHtmlId(suffix) {
        if (suffix)
            return "#" + baseId + "_" + suffix;
        else
            return "#" + baseId;
    }

    function getVideoContainer() {
        var selector = getHtmlId("browse_panel");
        selector += " .yui3-widget-bd .video-container";

        return selector;
    }

    function getPaginatorContainer() {
        var selector = getHtmlId("browse_panel");
        selector += " .paginator-container";

        return selector;
    }

    function showLoader() {
        Y.one(getHtmlId("browse_panel") + " " + loader).setStyle('display', 'block');
        Y.one(getHtmlId("video-container")).hide();
    }

    function hideLoader() {
        Y.one(getHtmlId("browse_panel") + " " + loader).setStyle('display', 'none');
        Y.one(getHtmlId("video-container")).show();
    }

    function convertMilliSecsToString(milli) {
        var seconds = Math.floor(milli / 1000);

        var minutes = Math.floor(seconds / 60);
        var remainderSeconds = seconds - (minutes * 60);

        if (minutes.toString().length == 1)
            minutes = "0" + minutes.toString();

        if (remainderSeconds.toString().length == 1)
            remainderSeconds = "0" + remainderSeconds.toString();

        return minutes + ":" + remainderSeconds;
    }

    function handleSearch() {
        Y.one(getHtmlId("search_button")).on('click', function(e) {
            var searchValue = Y.one(getHtmlId("search_input")).get('value');

            if (searchValue === "") {
                requestUrl = klpBcBrowse.actionUrl;
            } else {
                requestUrl = klpBcBrowse.searchUrl + "::" + encodeURIComponent(searchValue);
            }

            requestData();
            e.preventDefault();
        });

        Y.one(getHtmlId("search_input")).on('blur', function(e) {
            if (e.target.get('value') === '') {
                if (requestUrl !== klpBcBrowse.actionUrl) {
                    requestUrl = klpBcBrowse.actionUrl;
                    requestData();
                }
            }
        });

        Y.one(document.body).on('key', function(e) {
            if( e.target == Y.one(getHtmlId("search_button")) || e.target == Y.one(getHtmlId("search_input")) ) {
                Y.one(getHtmlId("search_button")).simulate('click');
                e.preventDefault();
            }
        }, 'enter');
    }
});
