YUI({
    // ezjscore seems to be able to pull in the .css so tell YUI to not pull in
    // the CSS again
    skin: {
        overrides: {
            'gallery-yui3treeview-ng': []
        }
    }
}).use('node', 'event', 'panel', 'io', 'json-parse', 'node-event-simulate', 'gallery-yui3treeview-ng', function (Y) {
    var baseId;
    var panels = {};
    var loader;
    var trees = {};

    // Register the complete handler once to make sure it's only called once.
    Y.on('io:complete', requestComplete, Y);

    Y.on('domready', function() {
        loader = klpBcServerFile.loaderSelector;

        var browseButton = Y.all(".klpbc_browse_server");
        browseButton.on('click', function(e) {
            baseId = e.target.get('id').replace("_browse_server", "");
            Y.one(getHtmlId()).addClass('yui3-skin-sam');
            showPanel(getHtmlId("browse_server_panel"));
        });
    });

    function showPanel(panelId) {
        if (!panels[baseId]) {
            Y.one(panelId).show();

            panel = new Y.Panel({
                srcNode: panelId,
                width: 700,
                height: 600,
                centered: true,
                modal: true
            });

            var selectButton = {
                value: klpBcServerFile.selectFileLabel,
                action: function(e) {
                    e.preventDefault();
                    selectFile();
                    panels[baseId].hide();
                },
                isDefault: true,
                section: Y.WidgetStdMod.FOOTER
            };
            panel.addButton(selectButton);

            panel.render();
            panels[baseId] = panel;

            requestData();
        }

        panels[baseId].show();
    }

    function selectFile() {
        var checkedNode;
        function findCheckedNode(node) {
            if (node.get('checked') === 30) {
                checkedNode = node;
                return true;
            }

            node.each(function(n) {
                findCheckedNode(n);
            });

            return false;
        }
        trees[baseId].each(function(node) {
            findCheckedNode(node);
        });

        if (checkedNode) {
            var filePath = checkedNode.get('nodeId');
            Y.one(getHtmlId("serverfile")).set('value', filePath);
        }
    }

    function requestData() {
        showLoader();
        var url = klpBcServerFile.action;
        url += "?ContentType=json";
        var request = Y.io(url);
    }

    function requestComplete(id, o, args) {
        var response = Y.JSON.parse(o.responseText);
        if (!response.content) return;

        setupTreeview(response.content);

        hideLoader();
    }

    function setupTreeview(root)
    {
        var tree = new Y.CheckBoxTreeView({
            startCollapsed: true,
            checkOnLabelClick: true,
            toggleOnLabelClick: true,
            children: generateTree(root)
        });

        function uncheck(child) {
            child.set('checked', 10);
            child.each(function(c) {
                uncheck(c);
            });
        }

        tree.on('check', function(e) {
            var state = e.treenode.get('checked');
            trees[baseId].each(function(child) {
                uncheck(child);
            });
            if (state === 10)
                e.treenode.set('checked', 30);

            e.preventDefault();
        });

        tree.render(getTreeContainer());
        trees[baseId] = tree;

        Y.one(getHtmlId() + " .yui3-treeview").on('click', function(e) {
            if (e.target.hasClass("yui3-treenode-label")) {
                var checkbox = e.target.one(".yui3-checkboxtreenode-checkbox");
                if (checkbox)
                    checkbox.simulate('click');
            }
        });
    }

    function generateTree(root) {
        nodes = [];
        for (var i = 0; i < root.length; i++) {
            var node = {};

            if (root[i].hasOwnProperty('children'))
                node = generateSubTree(root[i]);
            else
                node = createTreeNode(root[i]);

            nodes.push(node);
        }

        return nodes;
    }

    function generateSubTree(parentObject) {
        loopStack = [];
        loopStack.push(parentObject);

        nodeStack = [];
        nodeStack.push(createTreeNode(parentObject, []));

        while ( loopStack.length > 0 ) {
            object = loopStack.pop();

            if (!(object.hasOwnProperty('children')) || !(object.children instanceof Array))
                continue;

            var tempStack = [];
            for (var i = 0; i < object.children.length; i++) {
                if ( object.children[i].hasOwnProperty('children') ) {
                    loopStack.push(object.children[i]);

                    var item = createTreeNode(object.children[i], []);
                    tempStack.push(item);
                    continue;
                }

                var child = nodeStack.pop();
                child.add(createTreeNode(object.children[i]));
                nodeStack.push(child);
            }
            nodeStack = nodeStack.concat(tempStack);
        }

        function labelSort(a,b) {
            if (a.get('clabel').toLowerCase() < b.get('clabel').toLowerCase()) return -1;
            if (a.get('clabel').toLowerCase() > b.get('clabel').toLowerCase()) return 1;

            return 0;
        }

        while ( nodeStack.length > 1 ) {
            var subParent = nodeStack.pop();
            var parent = nodeStack.pop();

            parent.add(subParent);
            parent._items.sort(labelSort);

            nodeStack.push(parent);
        }

        return nodeStack;
    }

    function createTreeNode(object, children) {
        function label(filename, size) {
            var label = '';

            if ( object.isfile ) {
                label += klpBcServerFile.videoImage;
                label += filename;
                label += '<span class="size">' + bytesToSize(size) + '</span>';
            }
            else {
                label += klpBcServerFile.folderImage;
                label += filename;
            }

            return label;
        }

        var node;
        if (object.isfile) {
            node = new Y.CheckBoxTreeNode({
                label: label(object.filename, object.size),
                clabel: object.filename,
                nodeId: object.relativepath
            });
        } else {
            node = new Y.TreeNode({
                label: label(object.filename, object.size),
                clabel: object.filename,
                children: children
            });
        }

        return node;
    }

    function getHtmlId(suffix) {
        if (suffix)
            return "#" + baseId + "_" + suffix;
        else
            return "#" + baseId;
    }

    function getTreeContainer() {
        var selector = getHtmlId("browse_server_panel");
        selector += " .yui3-widget-bd .tree-container";

        return selector;
    }

    function showLoader() {
        Y.one(getHtmlId("browse_server_panel") + " " + loader).show();
    }

    function hideLoader() {
        Y.one(getHtmlId("browse_server_panel") + " " + loader).hide();
    }

    function bytesToSize(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes === 0) return '';
        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10);
        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    }
});
