{ezcss_require( array( 'lib/gallery-yui3treeview-ng/gallery-yui3treeview-ng-core.css', 'lib/gallery-yui3treeview-ng/skins/sam/gallery-yui3treeview-ng.css', 'klpbc_common.css', 'klpbc_server_browse.css' ) )}
{ezscript_require( array( 'lib/gallery-yui3treeview-ng/gallery-yui3treeview-ng.js', 'klpbc_server_browse.js' ) )}

{def $original_video=$attribute.content.original_video
     $filepath=''
     $serverfile=$attribute.contentclass_attribute.data_type.video_inputs['serverfile']
}
{if and( $is_checked, $original_video.filepath )}
    {def $filepath=$original_video.filepath}
{/if}

<div id="{$html_id}" class="klpbc_video_input_block">
    <div class="block">
        <div class="element">
            <label>{'Video Path'|i18n( $tr )}:</label>
            <input type="text"
                   value="{$filepath|wash}"
                   id="{$html_id}_serverfile"
                   class="no-autosave"
                   name="{$html_name}_serverfile" />
        </div>
        <div class="element">
            {if $serverfile.is_enabled}
                <input class="button klpbc_browse_server"
                       type="button"
                       id="{$html_id}_browse_server"
                       value="{'Browse files on this server…'|i18n( $tr )}" />
            {else}
                <input class="button klpbc_browse_server button-disabled"
                       disabled="disabled"
                       type="button"
                       id="{$html_id}_browse_server"
                       title='{"Browsing videos is disabled because the root directory is not configured"|i18n( $tr )}'
                       value="{'Browse files on this server…'|i18n( $tr )}" />
            {/if}
        </div>
        <div class="float-break"></div>
    </div>
    <div id="{$html_id}_browse_server_panel" class="klpbc_browse_server_panel" style="display:none">
        <div class="yui3-widget-hd">
            {"Select a video from the server"|i18n( $tr )}
        </div>
        <div class="yui3-widget-bd">
            <div class="klpbc-loader">
                <div style="display: none"></div>
            </div>
            <div class="tree-container">
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" charset="utf-8">
    klpBcServerFile = {ldelim}
        action:  {"/ezjscore/call/klpbcbrightcoveinputtype::files"|ezurl},
        loaderSelector: ".klpbc-loader div",
        folderImage: '<img src={"folder.png"|ezimage} />',
        videoImage: '<img src={"video.png"|ezimage} />',
        selectFileLabel: '{"Select file"|i18n( $tr }'
    {rdelim}
</script>
