{def $tr="design/standard/class/datatype/klpbc"
     $video=$attribute.content
     $options=$attribute.contentclass_attribute.content}

{if and( $video.latest_video, $video.latest_video.brightcove_id )}
    <div class="block">
        {include uri='design:klpbc/player.tpl'
                 klpbc_bgcolor = $options.playerBgColor
                 klpbc_width = $options.playerWidth
                 klpbc_height = $options.playerHeight
                 klpbc_player_id = $options.playerId
                 klpbc_player_key = $options.playerKey
                 klpbc_video_id = $video.latest_video.brightcove_id}
    </div>
{elseif $video.state|ne(0)}
    <div class="block">
        <p>{'Video is currently being processed, please check back again soon.'|i18n( $tr )}
    </div>
{/if}
