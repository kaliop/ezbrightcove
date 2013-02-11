{def $uploaded_video=$attribute.content.original_video}
<tr>
    <th class="tight">{'Uploaded video'|i18n( $tr )}</th>
    <td colspan="3">
        {if $uploaded_video}
            <a href={concat( 'content/download/', $attribute.contentobject_id, '/', $attribute.id,'/version/', $attribute.version , '/file/', $uploaded_video.original_filename|urlencode )|ezurl}>{$uploaded_video.original_filename|wash( xhtml )}</a>&nbsp;({$uploaded_video.filesize|si( byte )})
        {else}
            <em>{'No uploaded video'|i18n( $tr )}</em>
        {/if}
    </td>
</tr>
