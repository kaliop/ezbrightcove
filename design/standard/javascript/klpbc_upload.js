YUI().use('node', 'event', function (Y) {
    Y.one('.ezcca-edit-datatype-klpbc').ancestor('form').on('submit', function(e) {
        var files = Y.all('.klpbc_video_input_block input[type=file]');
        files.each( function(fileNode) {
            if (fileNode.get("value").length > 0) {
                var container = fileNode.ancestor('.ezcca-edit-datatype-klpbc');
                var loader = container.one( '.klpbc-loader div');
                loader.setStyle( 'display', 'block' );
            }
        });
    });
});
