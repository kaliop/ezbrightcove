<?php
/**
 * File containing the klpBcVideoMeta class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Retrieves meta data for a video via a relationship to attributes set up on
 * the content class.
 **/
class klpBcVideoMeta
{
    /**
     * Returns the video's name
     *
     * @param klpBcVideo A video
     * @return string|null Video name
     **/
    public function getName( $video )
    {
        $nameAttribute = $this->fetchRelationAttribute(
            "metaNameIdentifier", $this->fetchVideoAttribute(
                $video->attribute( 'contentobject_attribute_id' ),
                $video->attribute( 'version' )
            )
        );

        if ( $nameAttribute )
            return $nameAttribute->attribute( 'data_text' );
    }

    /**
     * Returns the video's description
     *
     * @param klpBcVideo A video
     * @return string|null Video description
     **/
    public function getDescription( $video )
    {
        $nameAttribute = $this->fetchRelationAttribute(
            "metaDescriptionIdentifier", $this->fetchVideoAttribute(
                $video->attribute( 'contentobject_attribute_id' ),
                $video->attribute( 'version' )
            )
        );

        if ( $nameAttribute )
            return $nameAttribute->attribute( 'data_text' );
    }

    /**
     * Fetches the content object attribute that we've formed a relationship to
     * by storing its identifier in the class options.
     *
     * @param string $relationName Name of the class option that holds the
     * identifier of the attribute that we're after
     * @param eZContentObjectAttribute $attribute The video's content object
     * attribute
     * @return eZContentObjectAttribute
     **/
    protected function fetchRelationAttribute( $relationName, $attribute )
    {
        if ( !$attribute )
            return;

        $classAttribute = $attribute->contentClassAttribute();
        $classAttributeIdentifier = $classAttribute->content()->$relationName;

        $attributeArray = $attribute->object()->fetchAttributesByIdentifier(
            array( $classAttributeIdentifier )
        );
        $attributeArray = array_values( $attributeArray );

        return $attributeArray[0];
    }

    /**
     * Fetches the content object attribute for a video
     *
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     * @return eZContentObjectAttribute|null
     **/
    protected function fetchVideoAttribute( $id, $version )
    {
        return eZContentObjectAttribute::fetch( $id, $version );
    }
}
