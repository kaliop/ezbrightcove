{def $attr = $class_attribute.content
     $tr = "design/standard/class/datatype/klpbc"}

<div class="block">
    <fieldset>
        <legend>Video meta data</legend>
            <div class="element">
                <label>{"Name attribute:"|i18n( $tr )}</label>
                <p>{$attr.metaNameIdentifier|wash}</p>
            </div>
            <div class="element">
                <label>{"Description attribute:"|i18n( $tr )}</label>
                <p>{$attr.metaDescriptionIdentifier|wash}</p>
            </div>
            <div class="float-break"></div>
    </fieldset>
</div>

<div class="block">
    <fieldset>
        <legend>Video player</legend>
        <div class="block">
            <div class="element">
                <label>{"ID:"|i18n( $tr )}</label>
                <p>{$attr.playerId|wash}</p>
            </div>
            <div class="element">
                <label>{"Key:"|i18n( $tr )}</label>
                <p>{$attr.playerKey|wash}</p>
            </div>
            <div class="float-break"></div>
        </div>
        <div class="block">
            <div class="element">
                <label>{"Width:"|i18n( $tr )}</label>
                <p>{$attr.playerWidth|wash}px</p>
            </div>
            <div class="element">
                <label>{"Height:"|i18n( $tr )}</label>
                <p>{$attr.playerHeight|wash}px</p>
            </div>
            <div class="element">
                <label>{"Background color:"|i18n( $tr )}</label>
                <p>#{$attr.playerBgColor|wash}</p>
            </div>
            <div class="float-break"></div>
        </div>
    </fieldset>
</div>

<div class="block">
    <fieldset>
        <legend>Upload settings</legend>
        <label>{"Max video file size:"|i18n( $tr )}</label>
        <p>{$attr.maxVideoSize|mul( 1024, 1024 )|si( byte )}</p>
    </fieldset>
</div>
