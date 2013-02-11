{def $data=$attribute.content.original_video}
<tr>
    <th class="tight">{'Path to file on server'|i18n( $tr )}</th>
    <td colspan="3">
        {if and($data, $data.filepath)}
            {$data.filepath|wash}
        {else}
            <em>{'No video path has been specified'|i18n( $tr )}</em>
        {/if}
    </td>
</tr>
