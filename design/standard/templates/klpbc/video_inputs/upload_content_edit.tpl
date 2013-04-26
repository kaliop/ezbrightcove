{ezcss_require( array( 'klpbc_common.css' ) )}
{ezscript_require( array( 'klpbc_upload.js' ) )}
<div id="{$html_id}" class="klpbc_video_input_block">
    {def $original_video=$attribute.content.original_video}
    <div class="block">
        <label>{'Current file'|i18n( $tr )}:</label>
        {if and( $is_checked, $original_video )}
            <table class="list" cellspacing="0">
                <tr>
                    <th>{'Filename'|i18n( $tr )}</th>
                    <th>{'MIME type'|i18n( $tr )}</th>
                    <th>{'Size'|i18n( $tr )}</th>
                </tr>
                <tr>
                    <td>{$original_video.original_filename|wash( xhtml )}</td>
                    <td>{$original_video.mime_type|wash( xhtml )}</td>
                    <td>{$original_video.filesize|si( byte )}</td>
                </tr>
            </table>

            <input class="button"
                   type="submit"
                   name="CustomActionButton[{$attribute.id}_delete]"
                   value="{'Remove'|i18n( $tr )}"
                   title="{'Remove the file from this draft.'|i18n( $tr )}" />
        {else}
            <p>{'There is no file.'|i18n( $tr )}</p>
            <input class="button-disabled"
                   type="submit"
                   class="no-autosave"
                   name="CustomActionButton[{$attribute.id}_delete]"
                   value="{'Remove'|i18n( $tr )}"
                   disabled="disabled" />
        {/if}
    </div>

    <div class="block">
        <div class="element">
            <label>{'New file for upload'|i18n( $tr )}:</label>
            <input type="hidden" name="MAX_FILE_SIZE" value="{$options.maxVideoSize|mul( 1024, 1024 )}" />
            <input type="file" name="{$html_name}_file" />
        </div>
        <div class="element klpbc-loader">
            <div style="display: none"></div>
        </div>
        <div class="float-break"></div>
    </div>
</div>
