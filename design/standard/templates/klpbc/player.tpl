{if is_set( $klpbc_bgcolor )|not()}
    {def $klpbc_bgcolor = ''}
{/if}
{if is_set( $klpbc_width )|not()}
    {def $klpbc_width = 0}
{/if}
{if is_set( $klpbc_height )|not()}
    {def $klpbc_height = 0}
{/if}
{if is_set( $klpbc_player_id )|not()}
    {def $klpbc_player_id = ''}
{/if}
{if is_set( $klpbc_player_key )|not()}
    {def $klpbc_player_key = ''}
{/if}
{if is_set( $klpbc_video_id )|not()}
    {def $klpbc_video_id = ''}
{/if}

<!-- Start of Brightcove Player -->
<div style="display:none">

</div>
<!-- By use of this code snippet, I agree to the Brightcove Publisher T and C found at https://accounts.brightcove.com/en/terms-and-conditions/. -->

<script type="text/javascript">
    var bJsHost = (("https:" == document.location.protocol ) ? "https://sadmin." : "http://admin.");
    document.write(unescape("%3Cscript src='" + bJsHost + "brightcove.com/js/BrightcoveExperiences.js' type='text/javascript'%3E%3C/script%3E"));
</script>

<object id="myExperience{$klpbc_video_id}" class="BrightcoveExperience">
    <param name="bgcolor" value="#{$klpbc_bgcolor}" />
    <param name="width" value="{$klpbc_width}" />
    <param name="height" value="{$klpbc_height}" />
    <param name="playerID" value="{$klpbc_player_id}" />
    <param name="playerKey" value="{$klpbc_player_key}" />
    <param name="isVid" value="true" />
    <param name="isUI" value="true" />
    <param name="dynamicStreaming" value="true" />
    <param name="includeAPI" value="true" />
    <param name="templateLoadHandler" value="klpBcOnTemplateLoaded" />
    <param name="@videoPlayer" value="{$klpbc_video_id}" />
    <param name="secureConnections" value="true" /> 
</object>
<script type="text/javascript">brightcove.createExperiences();</script>
<!-- End of Brightcove Player -->

{undef $klpbc_bgcolor
       $klpbc_width
       $klpbc_height
       $klpbc_player_id
       $klpbc_player_key
       $klpbc_video_id}
