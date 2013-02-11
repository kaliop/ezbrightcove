{def $attr = $class_attribute.content
     $base = "ContentClass_klpbc"
     $id = $class_attribute.id
     $tr = "design/standard/class/datatype/klpbc"}

{def $valid_meta_attributes=array()}
{foreach $#attributes as $attribute}
    {if $attribute.data_type_string|eq( "ezstring" )}
        {set $valid_meta_attributes=$valid_meta_attributes|append( $attribute )}
    {/if}
{/foreach}

<div class="block">
    <fieldset>
        <legend>Video meta data</legend>
        <div class="element">
            <label>{"Name attribute:"|i18n( $tr )}
                <span class="required">{"(required)"|i18n( $tr )}<span>
            </label>
            {if $valid_meta_attributes}
                <select name="{$base}_metaNameIdentifier_{$id}">
                <option value="">{"Please select attribute..."|i18n( $tr )}</option>
                {foreach $valid_meta_attributes as $attribute}
                    {if $attribute.identifier|eq( $class_attribute.content.metaNameIdentifier )}
                        <option selected="selected"
                                value="{$attribute.identifier}">
                             {$attribute.name} [{$attribute.data_type.information.name}]
                        </option>
                    {else}
                        <option value="{$attribute.identifier}">
                            {$attribute.name} [{$attribute.data_type.information.name}]
                        </option>
                    {/if}
                {/foreach}
                </select>
            {else}
                <p>{"No suitable attribute for the video name was found"|i18n( $tr )}</p>
            {/if}
        </div>
        <div class="element">
            <label>{"Description attribute:"|i18n( $tr )}
                <span class="required">{"(required)"|i18n( $tr )}<span>
            </label>
            {if $valid_meta_attributes}
                <select name="{$base}_metaDescriptionIdentifier_{$id}">
                <option value="">{"Please select attribute..."|i18n( $tr )}</option>
                {foreach $valid_meta_attributes as $attribute}
                    {if $attribute.identifier|eq( $class_attribute.content.metaDescriptionIdentifier )}
                        <option selected="selected"
                                value="{$attribute.identifier}">
                             {$attribute.name} [{$attribute.data_type.information.name}]
                        </option>
                    {else}
                        <option value="{$attribute.identifier}">
                            {$attribute.name} [{$attribute.data_type.information.name}]
                        </option>
                    {/if}
                {/foreach}
                </select>
            {else}
                <p>{"No suitable attribute for the video description was found"|i18n( $tr )}</p>
            {/if}
        </div>
        <div class="break"></div>
    </fieldset>
</div>

<div class="block">
    <fieldset>
        <legend>Video player</legend>
        <div class="block">
            <div class="element">
                <label>{"ID:"|i18n( $tr )}
                    <span class="required">{"(required)"|i18n( $tr )}<span>
                </label>
                <input type="text" value="{$attr.playerId|wash}" name="{$base}_playerId_{$id}" />
            </div>
            <div class="element">
                <label>{"Key:"|i18n( $tr )}
                    <span class="required">{"(required)"|i18n( $tr )}<span>
                </label>
                <input type="text" size="90" value="{$attr.playerKey|wash}" name="{$base}_playerKey_{$id}" />
            </div>
            <div class="break"></div>
        </div>
        <div class="block">
            <div class="element">
                <label>{"Width:"|i18n( $tr )}
                    <span class="required">{"(required)"|i18n( $tr )}<span>
                </label>
                <span><input type="text" value="{$attr.playerWidth|wash}" name="{$base}_playerWidth_{$id}" />px</span>
            </div>
            <div class="element">
                <label>{"Height:"|i18n( $tr )}
                    <span class="required">{"(required)"|i18n( $tr )}<span>
                </label>
                <span><input type="text" value="{$attr.playerHeight|wash}" name="{$base}_playerHeight_{$id}" />px</span>
            </div>
            <div class="break"></div>
        </div>
        <div class="block">
            <div class="element">
                <label>{"Background color:"|i18n( $tr )}</label>
                <span>#<input type="text" value="{$attr.playerBgColor|wash}" name="{$base}_playerBgColor_{$id}" /></span>
            </div>
            <div class="break"></div>
        </div>
    </div>
    </fieldset>
</div>

<div class="block">
    <fieldset>
        <legend>Upload settings</legend>
        <label>{"Max video file size:"|i18n( $tr )}
            <span class="required">{"(required)"|i18n( $tr )}<span>
        </label>
        <span><input type="text" value="{$attr.maxVideoSize|wash}" name="{$base}_maxVideoSize_{$id}" />MB</span>
    </fieldset>
</div>
