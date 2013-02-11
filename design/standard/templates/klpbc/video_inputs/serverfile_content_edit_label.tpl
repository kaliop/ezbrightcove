<input type="radio" value="{$identifier}" name="{$html_name}" id="{$html_id}"
       class="klpbc_video_input_switcher"
       {if $is_checked}checked="checked"{/if}
/>
<label class="{$html_class}" for="{$html_id}">
    {"Upload video from server"|i18n( $tr )}
</label>
