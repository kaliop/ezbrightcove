{ezcss_require( array( 'klpbc_common.css', 'klpbc_brightcove_browse.css' ) )}
{ezscript_require( array( 'lib/mustache.js', 'klpbc_paginator.js', 'klpbc_brightcove_browse.js' ) )}
<div id="{$html_id}" class="klpbc_video_input_block">
    <div class="block">
        <div class="element">
            <label>{'Brightcove Video ID'|i18n( $tr )}:</label>
            <input type="text"
                   value="{$attribute.content.brightcove_id|wash}"
                   id="{$html_id}_brightcove_id"
                   name="{$html_name}_brightcove_id" />
        </div>
        <div class="element">
            <input class="button klpbc_browse_brightcove"
                   type="button"
                   id="{$html_id}_browse"
                   value="{'Browse Brightcove for a video…'|i18n( $tr )}" />
        </div>
        <div class="float-break"></div>
    </div>
    <div id="{$html_id}_browse_panel" class="klpbc_browse_panel" style="display:none">
        <div class="yui3-widget-hd">
            {"Select a video from your Brightcove Library"|i18n( $tr )}
        </div>
        <div class="yui3-widget-bd">
            <div class="klpbc-loader">
                <div style="display: none"></div>
            </div>
            <div class="search-container">
                <input class="searchinput" id="{$html_id}_search_input" name="search_term" type="text" placeholder="Search for videos…" />
                <input class="defaultbutton" id="{$html_id}_search_button" name="search_button" type="submit" value="Search" />
                <div class="float-break"></div>
            </div>
            <div class="video-container" id="{$html_id}_video-container"></div>
            <div class="paginator-container"></div>
        </div>
    </div>
</div>


{* Mustache template for each individual video *}
<script id="{$html_id}_browse_video_template" type="text/x-mustache-template">
{literal}{{=<% %>=}}{/literal}
<div class="block klpbc_browse_video">
    <% #items %>
        <div class="video element">
            <p class="name"><% name %> <span class="separator"> | </span><span class="length"><% length %></span></p>
            <img src=<% thumbnailURL %> />
            <p class="description"><% shortDescription %></p>
            <input type="hidden" name="video_id" value="<% id %>" />
        </div>
    <% /items %>
    <div class="float-break"></div>
</div>
</script>

{* Mustache template for the browse video paginator *}
<script id="{$html_id}_browse_paginator_template" type="text/x-mustache-template">
{literal}{{=<% %>=}}{/literal}
<div class="block paginator">
    <% #paginator.canShowPrevious %>
        <span class="previous">
            <a class="previous"
               href="#<% paginator.previousPage %>">{"&larr; Previous page"|i18n( $tr )}</a>
         </span>
    <% /paginator.canShowPrevious %>
    <% ^paginator.canShowPrevious %>
        <span class="previous">{"&larr; Previous page"|i18n( $tr )}</span>
    <% /paginator.canShowPrevious %>

    <span class="page">
        {"Page %currentPage of %pageCount"|i18n( $tr, '', hash(
            '%currentPage', '<% paginator.currentPage %>',
            '%pageCount', '<% paginator.pageCount %>'
        ))}
    </span>

    <% #paginator.canShowNext %>
        <span class="next">
            <a class="next" href="#<% paginator.nextPage %>">{"Next page &rarr;"|i18n( $tr )}</a>
        </span>
    <% /paginator.canShowNext %>
    <% ^paginator.canShowNext %>
        <span class="next">{"Next page &rarr;"|i18n( $tr )}</span>
    <% /paginator.canShowNext %>
</div>
</script>

<script type="text/javascript" charset="utf-8">
    klpBcBrowse = {ldelim}
        actionUrl:  {"/ezjscore/call/klpbcbrightcoveinputtype::videos"|ezurl},
        searchUrl:  {"/ezjscore/call/klpbcbrightcoveinputtype::search"|ezurl},
        loaderSelector: ".klpbc-loader div"
    {rdelim}
</script>
