<?php
/**
 * File containing the klpBcVideoMetaTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpBcVideoMetaTest extends klpBcTestCase
{
    public function setUp()
    {
        parent::setUp();

        $class = $this->createClass();
        $object = $this->createContentObject( $class );
        $version = $this->createContentVersion( $object );

        $nameAttribute = $this->createNameObjectAttribute(
            $this->createNameClassAttribute( $class ), $object,
            "My video name"
        );
        $descAttribute = $this->createDescObjectAttribute(
            $this->createDescClassAttribute( $class ), $object,
            "My video description"
        );
        $videoAttribute = $this->createVideoObjectAttribute(
            $this->createVideoClassAttribute( $class ), $object
        );

        $this->video = $this->createVideo( $videoAttribute );
        $this->meta = new klpBcVideoMeta();
    }

    public function testGetName()
    {
        $this->assertEquals(
            "My video name", $this->meta->getName( $this->video ),
            "Expected correct video name to be returned"
        );
    }

    public function testGetDescription()
    {
        $this->assertEquals(
            "My video description", $this->meta->getDescription( $this->video ),
            "Expected correct video description to be returned"
        );
    }

    protected function createClass()
    {
        $class = eZContentClass::create();
        $class->setAttribute( 'version', 0 );
        $class->store();

        return $class;
    }

    protected function createNameClassAttribute( $class )
    {
        $nameClassAttribute = eZContentClassAttribute::create(
            $class->attribute( 'id' ), 'ezstring'
        );
        $nameClassAttribute->setAttribute( 'identifier', 'name' );
        $nameClassAttribute->setAttribute( 'version', 0 );
        $nameClassAttribute->store();

        return $nameClassAttribute;
    }

    protected function createDescClassAttribute( $class )
    {
        $descClassAttribute = eZContentClassAttribute::create(
            $class->attribute( 'id' ), 'ezstring'
        );
        $descClassAttribute->setAttribute( 'identifier', 'description' );
        $descClassAttribute->setAttribute( 'version', 0 );
        $descClassAttribute->store();

        return $descClassAttribute;
    }

    protected function createVideoClassAttribute( $class )
    {
        $videoClassAttribute = eZContentClassAttribute::create(
            $class->attribute( 'id' ), klpBcType::DATA_TYPE_STRING
        );
        $videoClassAttribute->setAttribute( 'identifier', 'brightcove' );
        $videoClassAttribute->setAttribute( 'version', 0 );

        $options = $videoClassAttribute->content();
        $options->metaNameIdentifier = "name";
        $options->metaDescriptionIdentifier = "description";
        $videoClassAttribute->setAttribute(
            klpBcType::CLASS_OPTIONS_FIELD, $options->toJson()
        );
        $videoClassAttribute->store();

        return $videoClassAttribute;
    }

    protected function createContentObject( $class )
    {
        $object = eZContentObject::create(
            "Video", $class->attribute( 'id' ), 14
        );
        $object->setAttribute( 'version', 1 );
        $object->store();

        return $object;
    }

    protected function createContentVersion( $object )
    {
        $version = eZContentObjectVersion::create(
            $object->attribute( 'id' ), 14, 1
        );
        $version->setAttribute( 'language_mask', 3 );
        $version->store();

        return $version;
    }

    protected function createNameObjectAttribute( $nameClassAttribute, $object, $text )
    {
        $nameAttribute = eZContentObjectAttribute::create(
            $nameClassAttribute->attribute( 'id' ), $object->attribute( 'id' ), 1
        );
        $nameAttribute->setAttribute( 'data_text', $text );
        $nameAttribute->setAttribute( 'language_id', 1 );
        $nameAttribute->store();

        return $nameAttribute;
    }

    protected function createDescObjectAttribute( $descClassAttribute, $object, $text )
    {
        $descAttribute = eZContentObjectAttribute::create(
            $descClassAttribute->attribute( 'id' ), $object->attribute( 'id' ), 1
        );
        $descAttribute->setAttribute( 'data_text', $text );
        $descAttribute->setAttribute( 'language_id', 1 );
        $descAttribute->store();

        return $descAttribute;
    }

    protected function createVideoObjectAttribute( $videoClassAttribute, $object )
    {
        $videoAttribute = eZContentObjectAttribute::create(
            $videoClassAttribute->attribute( 'id' ), $object->attribute( 'id' ), 1
        );
        $videoAttribute->store();

        return $videoAttribute;
    }

    protected function createVideo( $videoObjectAttribute )
    {
        $video = new klpbcVideo();
        $video->setAttribute(
            'contentobject_attribute_id', $videoObjectAttribute->attribute( 'id' )
        );
        $video->setAttribute(
            'version', $videoObjectAttribute->attribute( 'version' )
        );

        return $video;
    }

}
