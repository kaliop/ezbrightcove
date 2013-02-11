YUI().use('node', 'event', 'node-event-simulate', function (Y) {
    Y.on('contentready', function () {
        var datatypes = Y.all('.ezcca-edit-datatype-klpbc');
        datatypes.each( function(datatype) {

            datatype.all('.klpbc_video_input_block').hide();

            datatype.all('.klpbc_video_input_switcher').on( 'click', (function(e) {
                datatype.all('.klpbc_video_input_block').hide();
                datatype.one('#' + e.target.get('id') + '_block').show();
            }));

            datatype.all('.klpbc_video_input_switcher').each(function(node) {
                if (node.get('checked')) { node.simulate('click'); }
            });
        });
    }, '.ezcca-edit-datatype-klpbc' );
});
