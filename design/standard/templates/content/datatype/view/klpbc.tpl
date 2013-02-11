{ezcss_require( array( 'klpbc_view.css' ) )}
{def $tr="design/standard/class/datatype/klpbc"
     $tpl_base = "design:klpbc/video_inputs/"
     $video=$attribute.content
     $video_inputs = $attribute.contentclass_attribute.data_type.video_inputs
     $options=$attribute.contentclass_attribute.content}

<div class="block klpbc">
    <table class="list">
    <tr>
        <th class="firstrow tight">State</th>
        <td>{$video.state_label}</td>
        <th class="secondrow tight">Error</th>
        <td>
            {if $video.has_error}
                <span class="error">{$video.error_log}</span>
            {else}
                <em>{"No error"|i18n( $tr )}</em>
            {/if}
        </td>
    </tr>
    <tr>
        <th class="firstrow tight">{"Brightcove ID"|i18n( $tr )}</th>
        <td>
            {if $video.brightcove_id}
                {$video.brightcove_id}
            {else}
                <em>{"No ID"|i18n( $tr )}</em>
            {/if}
        </td>
        <th class="secondrow tight">Pending meta data push to Brightcove</th>
        <td>
            {if $video.need_meta_update}
                {"Yes"|i18n( $tr )}
            {else}
                <em>{"No"|i18n( $tr )}</em>
            {/if}
        </td>
    </tr>

    {def $input_type=$attribute.content.input_type_identifier}
    {include uri=concat( $tpl_base, $input_type, "_content_view.tpl" )
             attribute=$attribute
             identifier=$input_type
             tr=$tr}

    </table>

    {if and( $video.latest_video, $video.latest_video.brightcove_id )}
        <fieldset class="currentvideo">
            <legend>{"Currently available video:"|i18n( $tr )}</legend>
                    {include uri='design:klpbc/player.tpl'
                             klpbc_bgcolor = $options.playerBgColor
                             klpbc_width = 300
                             klpbc_height = 200
                             klpbc_player_id = $options.playerId
                             klpbc_player_key = $options.playerKey
                             klpbc_video_id = $video.latest_video.brightcove_id}
            <p>{"Brightcove ID"|i18n( $tr )}: {$video.latest_video.brightcove_id}</p>
        </fieldset>
    {/if}

</div>
