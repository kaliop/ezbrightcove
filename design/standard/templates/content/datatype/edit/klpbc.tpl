{ezcss_require( array( 'klpbc_edit.css' ) )}
{ezscript_require( array( 'ezjsc::jquery', 'klpbc_edit.js' ) )}

{def $options = $attribute.contentclass_attribute.content
     $video=$attribute.content
     $video_inputs = $attribute.contentclass_attribute.data_type.video_inputs
     $tr = "design/standard/class/datatype/klpbc"
     $tpl_base = "design:klpbc/video_inputs/"
     $name = concat( $attribute_base, "_klpbc_video_input_", $attribute.id )}

<div class="block">
    {if $video.is_completed}
        {if $video.brightcove_id|eq('')|not()}
            {include uri='design:klpbc/player.tpl'
                     klpbc_bgcolor    = $options.playerBgColor
                     klpbc_width      = $options.playerWidth
                     klpbc_height     = $options.playerHeight
                     klpbc_player_id  = $options.playerId
                     klpbc_player_key = $options.playerKey
                     klpbc_video_id   = $video.brightcove_id}
        {/if}
    {/if}

    {if $video.requires_processing}
        <p>{"State"|i18n( $tr )}: {$video.state_label}</p>
    {/if}

    {if $video.brightcove_id|eq('')|not()}
        <p>{"Brightcove ID"|i18n( $tr )}: {$video.brightcove_id}</p>
    {/if}
    {if $video.has_error}
        <p>{"Error log"|i18n( $tr )}: {$video.error_log}</p>
    {/if}
</div>

<div class="block">
    {def $is_checked=false()}
    {if $video.input_type_identifier|not()}
        {set $is_checked=true()}
    {/if}

    <div class="block klpbc-input-selector">
        {foreach $video_inputs as $identifier => $input}
            {set $is_checked=or( $is_checked, $identifier|eq( $video.input_type_identifier ) )}

            {include uri=concat( $tpl_base, $identifier, "_content_edit_label.tpl" )
                     attribute=$attribute
                     identifier=$identifier
                     html_name=$name
                     html_class="klpbc-input-type"
                     html_id=concat( $name, "_", $identifier )
                     is_checked=$is_checked
                     tr=$tr}

            {set $is_checked=false()}
        {/foreach}
    </div>

    <div class="block klpbc-input-types">
        {foreach $video_inputs as $identifier => $input}
            {set $is_checked=or( $is_checked, $identifier|eq( $video.input_type_identifier ) )}
            {include uri=concat( $tpl_base, $identifier, "_content_edit.tpl" )
                     attribute=$attribute
                     html_name=$name
                     html_id=concat( $name, "_", $identifier, "_block" )
                     is_checked=$is_checked
                     tr=$tr
                     options=$options}

            {set $is_checked=false()}
        {/foreach}
    </div>
</div>
