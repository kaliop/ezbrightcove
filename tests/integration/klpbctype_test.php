<?php
/**
 * File containing the klpBcTypeTest class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

class klpBcTypeTest extends klpBcTestCase
{
    public function setUp()
    {
        parent::setUp();

        $class = new ezpClass();
        $classAttribute = $class->add(
            "Brightcove",
            "brightcove_video",
            klpBcType::DATA_TYPE_STRING
        );
        $class->store();
        $class = $class->class;

        $this->attr = $class->fetchAttributeByIdentifier( 'brightcove_video' );
        $this->dataType = $this->attr->dataType();
    }

    public function tearDown()
    {
        parent::tearDown();

        // Reset dic and inputs
        $this->dataType->dic = klpBcDiContainereZTiein::getInstance();
        $this->dataType->inputs = $this->dic->getVideoInput();
    }

    public function testClassAttributeContentNoContent()
    {
        $this->attr->setContent(null);
        $content = $this->attr->content();
        $this->assertInstanceOf(
            'klpBcTypeOptions', $content,
            "Expected class attribute content to be of type eZBightCoveTypeOptions"
        );
    }

    public function testValidateClassAttributeHTTPInputEmptyData()
    {
        $status = $this->validateClassAttribute( $this->attr );
        $this->assertEquals(
            eZInputValidator::STATE_INVALID, $status,
            "Empty input should be invalid"
        );
    }

    public function testValidateClassAttributeHTTPInputValidData()
    {
        $_POST = $this->validClassInputData( $this->attr );
        $status = $this->validateClassAttribute( $this->attr );

        $this->assertEquals(
            eZInputValidator::STATE_ACCEPTED, $status,
            "Correct class attribute input should be valid"
        );
    }

    public function testFetchClassAttributeHTTPInput()
    {
        $_POST = $this->validClassInputData( $this->attr );
        $dataType = $this->attr->dataType();
        $dataType->fetchClassAttributeHTTPInput(
            eZHTTPTool::instance(), 'ContentClass', $this->attr
        );

        $options = $this->attr->content();
        $this->assertEquals(
            "123", $options->playerId,
            "Player ID should have been set and returned"
        );
    }

    public function testInitializeObjectAttribute()
    {
        $version1Attribute = new eZContentObjectAttribute();
        $version1Attribute->setAttribute( 'id', 100 );
        $version1Attribute->setAttribute( 'version', 1 );

        $version2Attribute = clone $version1Attribute;
        $version2Attribute->setAttribute( 'version', 2 );

        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', 100 );
        $video->setAttribute( 'version', 1 );
        $video->store();

        $dataType = $this->attr->dataType();
        $dataType->initializeObjectAttribute(
            $version2Attribute, 1, $version1Attribute
        );

        $newVideo = klpBcVideo::fetch( 100, 2 );
        $this->assertInstanceOf( 'klpBcVideo', $newVideo,
            "Expected to get a new video for version 2"
        );

        $this->assertEquals( 2, (int) $newVideo->attribute( 'version' ),
            "Expected a version 2 of the attribute to exists"
        );
    }

    public function testObjectAttributeContent()
    {
        $attribute = new eZContentObjectAttribute();
        $attribute->setAttribute( 'id', 100 );
        $attribute->setAttribute( 'version', 1 );

        $dataType = $this->attr->dataType();
        $content = $dataType->objectAttributeContent( $attribute );

        $this->assertEquals( 100, $content->attribute( 'contentobject_attribute_id' ),
            "Expected content's attribute id to be 100"
        );
        $this->assertEquals( 1, $content->attribute( 'version' ),
            "Expected content's version to be 1"
        );

        $attribute->setAttribute( 'id', 200 );
        $attribute->setAttribute( 'version', 2 );

        $content->setAttribute( 'contentobject_attribute_id', 200 );
        $content->setAttribute( 'version', 2 );
        $content->store();

        $content = $dataType->objectAttributeContent( $attribute );

        $this->assertEquals( 200, $content->attribute( 'contentobject_attribute_id' ),
            "Expected content's attribute id to be 100"
        );
        $this->assertEquals( 2, $content->attribute( 'version' ),
            "Expected content's version to be 1"
        );
    }

    public function testValidateObjectAttributeHTTPInput()
    {
        $objectAttributeMock = $this->getMock( 'eZContentObjectAttribute' );
        $objectAttributeMock->expects( $this->any() )
                            ->method( 'validateIsRequired' )
                            ->will( $this->returnValue( true ) );

        $videoMock = $this->getMock( 'klpBcVideo' );
        $videoMock->expects( $this->any() )
                  ->method( 'hasOriginalVideo' )
                  ->will( $this->returnValue( true ) );

        $objectAttributeMock->expects( $this->any() )
                            ->method( 'content' )
                            ->will( $this->returnValue( $videoMock ) );

        $inputTypeMock = $this->getMock( 'klpBcUploadVideoInputType' );
        $inputTypeMock->expects( $this->any() )
                      ->method( 'isInputDataValid' )
                      ->with( array(), true, true );

        $this->dataType->inputs = $this->createInputsMock( $inputTypeMock );

        $this->dataType->validateObjectAttributeHTTPInput(
            eZHTTPTool::instance(), '', $objectAttributeMock
        );
    }

    public function testValidateObjectAttributeHTTPInputNoCurrentInputType()
    {
        $http = $this->getMock( 'stdClass', array( 'postVariable' ) );
        $http->expects( $this->once() )
             ->method( 'postVariable' )
             ->will( $this->returnValue( '' ) );

        $objectAttributeMock = $this->getMock( 'eZContentObjectAttribute' );
        $objectAttributeMock->expects( $this->any() )
                            ->method( 'validateIsRequired' )
                            ->will( $this->returnValue( true ) );

        $result = $this->dataType->validateObjectAttributeHTTPInput(
            $http, '', $objectAttributeMock
        );

        $this->assertEquals( eZInputValidator::STATE_INVALID, $result );
    }

    public function testFetchObjectAttributeHTTPInput()
    {
        // Make sure video input's type store() is called
        $http = $this->getMock( 'stdClass', array( 'postVariable' ) );
        $http->expects( $this->once() )
             ->method( 'postVariable' )
             ->will( $this->returnValue( 'some_input_type' ) );

        $inputTypeMock = $this->createInputTypeMockStore( true );
        $this->dataType->inputs = $this->createInputsMock( $inputTypeMock );

        $video = new klpBcVideo();
        $video->setAttribute( 'state', klpBcVideo::STATE_COMPLETED  );

        $attribute = $this->getMock(
            'eZContentObjectAttribute', array( 'content', 'store' )
        );
        $attribute->setAttribute( 'id', 600 );
        $attribute->setAttribute( 'version', 6 );
        $attribute->expects( $this->any() )
                  ->method( 'content' )
                  ->will( $this->returnValue( $video ) );
        $attribute->expects( $this->once() )
                  ->method( 'store' );

        $this->dataType->fetchObjectAttributeHTTPInput(
            $http, '', $attribute
        );

        // Make sure video is stored
        $video = klpBcVideo::fetch( 600, 6 );

        $this->assertEquals( 600, (int) $video->attribute( 'contentobject_attribute_id' ),
            "Expected video's content object attribute id = 300"
        );
        $this->assertEquals( 6, (int) $video->attribute( 'version' ),
            "Expected video's version = 3"
        );
        $this->assertSame( (string) klpBcVideo::STATE_DRAFT, $video->attribute( 'state' ),
            "Expected state to have changed to to DRAFT"
        );
    }

    public function testFetchObjectAttributeHTTPInputChangeInputType()
    {
        $inputTypeMock = $this->createInputTypeMockStore( false );
        $this->dataType->inputs = $this->createInputsMock( $inputTypeMock );

        $video = new klpBcVideo();
        $video->setAttribute( 'contentobject_attribute_id', 324 );
        $video->setAttribute( 'version', 1 );
        $video->setAttribute( 'input_type_identifier', 'input_type_a' );
        $video->setAttribute( 'brightcove_id', 1234 );
        $video->setAttribute( 'state', 3 );
        $video->store();

        $attribute = $this->getMock(
            'eZContentObjectAttribute', array( 'content', 'store' )
        );
        $attribute->setAttribute( 'id', 324 );
        $attribute->setAttribute( 'version', 1 );
        $attribute->expects( $this->any() )
                  ->method( 'content' )
                  ->will( $this->returnValue( $video ) );

        $http = $this->getMock( 'stdClass', array( 'postVariable' ) );
        $http->expects( $this->once() )
             ->method( 'postVariable' )
             ->will( $this->returnValue( 'input_type_b' ) );

        $this->dataType->fetchObjectAttributeHTTPInput( $http, '', $attribute );

        $fetchedVideo = klpBcVideo::fetch( 324, 1 );

        $this->assertEquals( '', $fetchedVideo->attribute( 'brightcove_id' ),
            "Expected brightcove id to be empty"
        );
    }

    public function testOnPublish()
    {
        $attribute = $this->getMock(
            'eZContentObjectAttribute', array( 'content' )
        );
        $attribute->setAttribute( 'id', 600 );
        $attribute->setAttribute( 'version', 6 );
        $attribute->expects( $this->any() )
                  ->method( 'content' )
                  ->will( $this->returnValue( new klpBcVideo ) );

        $queue = $this->getMock('klpBcQueue');
        $queue->expects( $this->once() )
              ->method( 'insert' );

        $this->dic->{'queue.instance'} = $queue;

        $dataType = $this->attr->dataType();
        $dataType->inputs = $inputsMock;

        $dataType->onPublish( $attribute, null, null );
    }

    public function testDeleteStoredObjectAttributeOneVersion()
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', 42 );
        $attribute = $this->createAttribute( 42, 1, $video );

        // Set up expectation: $input->inputTypes()->delete( $id, $version )
        $this->dataType->inputs = $this->createInputTypesMock(
            array( $this->createInputTypeMockDelete( 42, 1 ) )
        );
        // Set up expectation: $dic->getQueue()->delete( $video, $version )
        $this->dataType->dic = $this->createQueueDicMock(
            $this->createDeleteQueueMock( $video, 1 )
        );

        $this->dataType->deleteStoredObjectAttribute( $attribute, 1 );
    }

    public function testDeleteStoredObjectAttributeAllVersions()
    {
        $video = new klpBcVideo( false );
        $video->setAttribute( 'contentobject_attribute_id', 43 );
        $attribute = $this->createAttribute( 43, 1, $video );

        // Set up expectation: $input->inputTypes()->delete( $id, $version )
        $this->dataType->inputs = $this->createInputTypesMock(
            array(
                $this->createInputTypeMockDelete( 43, null ),
                $this->createInputTypeMockDelete( 43, null )
            )
        );
        // Set up expectation: $dic->getQueue()->delete( $video, $version )
        $this->dataType->dic = $this->createQueueDicMock(
            $this->createDeleteQueueMock( $video, null )
        );

        $this->dataType->deleteStoredObjectAttribute( $attribute, null );
    }

    public function testSerializeContentClassAttribute()
    {
        $node = new DOMElement( "testnode" );

        $options = $this->getMock( 'stdClass', array( 'toXml' ) );
        $options->expects( $this->once() )
                ->method( 'toXml' )
                ->with( $this->equalTo( $node ) );

        $attribute = $this->getMock( 'stdClass', array( 'content' ) );
        $attribute->expects( $this->once() )
                  ->method( 'content' )
                  ->will( $this->returnValue( $options ) );

        $this->dataType->serializeContentClassAttribute(
            $attribute, $node, $node
        );
    }

    public function testUnserializeContentClassAttribute()
    {
        $node = new DOMElement( "testnode" );

        $options = $this->getMock( 'klpBcTypeOptions', array( 'fromXml' ) );
        $options->expects( $this->once() )
                ->method( 'fromXml' )
                ->with( $this->equalTo( $node ) )
                ->will( $this->returnSelf() );

        $attribute = $this->getMock( 'stdClass',
            array( 'content', 'setAttribute', 'store' )
        );
        $attribute->expects( $this->once() )
                  ->method( 'content' )
                  ->will( $this->returnValue( $options ) );
        $attribute->expects( $this->once() )
                  ->method( 'setAttribute' );
        $attribute->expects( $this->once() )
                  ->method( 'store' );

        $this->dataType->unserializeContentClassAttribute(
            $attribute, $node, $node
        );
    }

    public function testFromString()
    {
        $video = new klpBcVideo( false );

        $attribute = $this->getMock(
            'eZContentObjectAttribute', array( 'content', 'store' )
        );
        $attribute->setAttribute( 'id', 27 );
        $attribute->setAttribute( 'version', 1 );
        $attribute->setAttribute(
            'data_type_string', klpBcType::DATA_TYPE_STRING
        );
        $attribute->expects( $this->any() )
                  ->method( 'content' )
                  ->will( $this->returnValue( $video ) );

        // Set up expectation: $input->getInput( $input )->fromString()
        $this->dataType->inputs = $this->createInputsMock(
            $this->createInputTypeMockFromString( 27, 1, "somepath" )
        );

        // Set up expectation: $dic->getQueue()->insert( $video )
        $this->dataType->dic = $this->createQueueDicMock(
            $this->createInsertQueueMock()
        );

        $this->dataType->fromString( $attribute, "sometype|somepath" );
    }

    public function testFromStringWithBrightcoveId()
    {
        $attribute = new eZContentObjectAttribute();
        $attribute->setAttribute( 'id', 32 );
        $attribute->setAttribute( 'version', 1 );
        $attribute->setAttribute('data_type_string',
            klpBcType::DATA_TYPE_STRING
        );

        $string = "sometype|somepath|2006221000001";

        // Set up expectation: $input->getInput( $input )->fromString()
        $this->dataType->inputs = $this->createInputsMock(
            $this->createInputTypeMockFromString( 32, 1, "somepath" )
        );

        $this->dataType->fromString( $attribute, $string );

        $video = klpBcVideo::fetch( 32, 1 );
        $this->assertEquals(
            klpBcVideo::STATE_COMPLETED, $video->attribute( 'state' ),
            "Expected state to be completed"
        );
    }

    public function testToString()
    {
        $video = $this->getMock( 'klpBcVideo', array( 'getFileUrl' ) );
        $video->expects( $this->once() )
               ->method( 'getFileUrl' )
               ->will( $this->returnValue( '/some/path' ) );

        $video->setAttribute( 'input_type_identifier', 'sometype' );
        $video->setAttribute( 'brightcove_id', '1234' );

        $attribute = $this->getMock(
            'eZContentObjectAttribute', array( 'content' )
        );
        $attribute->expects( $this->any() )
                  ->method( 'content' )
                  ->will( $this->returnValue( $video ) );

        $string = $this->dataType->toString( $attribute );

        $this->assertEquals( "sometype|/some/path|1234", $string );
    }

    public function testStoredFileInformation()
    {
        $this->dataType->inputs = $this->createInputsMock(
            $this->createInputTypeMockGetFileInfo( 241, 2 )
        );

        $attribute = $this->createAttribute( 241, 2, new klpBcVideo( false ) );

        $this->dataType->storedFileInformation(
            null, null, null, $attribute
        );
    }

    public function testHasStoredFileInformation()
    {
        $this->dataType->inputs = $this->createInputsMock(
            $this->createInputTypeMockCanDownload( true )
        );

        $attribute = $this->createAttribute( 13, 1, new klpBcVideo( false ) );

        $this->assertTrue(
            $this->dataType->hasStoredFileInformation(
                null, null, null, $attribute
            )
        );
    }

    public function testHasOriginalVideoDifferentInputType()
    {
        $videoMock = $this->getMock( 'klpBcVideo', array( 'hasOriginalVideo' ) );
        $videoMock->expects( $this->any() )
                  ->method( 'hasOriginalVideo' )
                  ->will( $this->returnValue( true ) );
        $videoMock->setAttribute( 'input_type_identifier', "input_type_a" );

        $objectAttributeMock = $this->getMock( 'eZContentObjectAttribute' );
        $objectAttributeMock->expects( $this->any() )
                            ->method( 'content' )
                            ->will( $this->returnValue( $videoMock ) );

        $this->assertFalse(
            $this->dataType->hasOriginalVideo(
                $objectAttributeMock, "input_type_b"
            )
        );
    }

    public function testHasOriginalVideoSameInputType()
    {
        $videoMock = $this->getMock( 'klpBcVideo', array( 'hasOriginalVideo' ) );
        $videoMock->expects( $this->any() )
                  ->method( 'hasOriginalVideo' )
                  ->will( $this->returnValue( true ) );
        $videoMock->setAttribute( 'input_type_identifier', "input_type_a" );

        $objectAttributeMock = $this->getMock( 'eZContentObjectAttribute' );
        $objectAttributeMock->expects( $this->any() )
                            ->method( 'content' )
                            ->will( $this->returnValue( $videoMock ) );

        $this->assertTrue(
            $this->dataType->hasOriginalVideo(
                $objectAttributeMock, "input_type_a"
            )
        );
    }

    protected function createDeleteQueueMock( $video, $version )
    {
        $queue = $this->getMock( 'klpQueue', array( 'delete' ) );
        $queue->expects( $this->atLeastOnce() )
              ->method( 'delete' )
              ->with( $this->equalTo( $video ), $this->equalTo( $version ) );

        return $queue;
    }

    protected function createInsertQueueMock()
    {
        $queue = $this->getMock( 'klpQueue', array( 'insert' ) );
        $queue->expects( $this->once() )
              ->method( 'insert' )
              ->with( $this->anything() );

        return $queue;
    }

    protected function createQueueDicMock( $queue )
    {
        $dic = $this->getMock( 'klpDiContainereZTiein', array( 'getQueue' ) );
        $dic->expects( $this->atLeastOnce() )
            ->method( 'getQueue' )
            ->will( $this->returnValue( $queue ) );

        return $dic;
    }

    protected function createAttribute( $id, $version, $video )
    {
        $attribute = new eZContentObjectAttribute();
        $attribute->setAttribute( 'id', $id );
        $attribute->setAttribute( 'version', $version );
        $attribute->setContent( $video );

        return $attribute;
    }

    protected function createInputsMock( $inputType )
    {
        $inputsMock = $this->getMock( 'stdClass', array( 'getInput' ) );
        $inputsMock->expects( $this->any() )
                   ->method( 'getInput' )
                   ->will( $this->returnValue( $inputType ) );

        return $inputsMock;
    }

    protected function createInputTypesMock( $inputType )
    {
        $inputsMock = $this->getMock( 'stdClass', array( 'inputTypes' ) );
        $inputsMock->expects( $this->any() )
                   ->method( 'inputTypes' )
                   ->will( $this->returnValue( $inputType ) );

        return $inputsMock;
    }

    protected function createInputTypeMockDelete( $id, $version )
    {
        $inputTypeMock = $this->getMock( 'stdClass', array( 'delete' ) );
        $inputTypeMock->expects( $this->atLeastOnce() )
                      ->method( 'delete' )
                      ->with( $this->equalTo( $id ), $this->equalTo( $version ) );

        return $inputTypeMock;
    }

    protected function createInputTypeMockCanDownload( $returnValue )
    {
        $inputTypeMock = $this->getMock( 'stdClass', array( 'canDownload' ) );
        $inputTypeMock->expects( $this->once() )
                      ->method( 'canDownload' )
                      ->will( $this->returnValue( $returnValue ) );

        return $inputTypeMock;
    }

    protected function createInputTypeMockGetFileInfo( $id, $version )
    {
        $inputTypeMock = $this->getMock( 'stdClass', array( 'getFileInfo' ) );
        $inputTypeMock->expects( $this->once() )
                      ->method( 'getFileInfo' )
                      ->with(
                          $this->equalTo( $id ), $this->equalTo( $version )
                      );

        return $inputTypeMock;
    }

    protected function createInputTypeMockStore( $storeResult )
    {
        $inputTypeMock = $this->getMock( 'stdClass', array( 'store' ) );
        $inputTypeMock->expects( $this->once() )
                      ->method( 'store' )
                      ->will( $this->returnValue( $storeResult ) );

        return $inputTypeMock;
    }

    protected function createInputTypeMockFromString( $id, $version, $value )
    {
        $inputTypeMock = $this->getMock( 'stdClass', array( 'fromString' ) );
        $inputTypeMock->expects( $this->once() )
                      ->method( 'fromString' )
                      ->with(
                          $this->equalTo( $id ),
                          $this->equalTo( $version ),
                          $this->equalTo( $value )
                      )
                      ->will( $this->returnValue( true ) );

        return $inputTypeMock;
    }

    protected function validateClassAttribute( $attribute )
    {
        $dataType = $attribute->dataType();
        $status = $dataType->validateClassAttributeHTTPInput(
            eZHTTPTool::instance(), 'ContentClass', $attribute
        );

        return $status;
    }

    protected function validClassInputData( $attribute )
    {
        $playerIdName = klpBcType::postVarName(
            "ContentClass", $attribute->ID, "playerId"
        );
        $playerKeyName = klpBcType::postVarName(
            "ContentClass", $attribute->ID, "playerKey"
        );
        $playerWidthName = klpBcType::postVarName(
            "ContentClass", $attribute->ID, "playerWidth"
        );
        $playerHeightName = klpBcType::postVarName(
            "ContentClass", $attribute->ID, "playerHeight"
        );
        $metaName = klpBcType::postVarName(
            "ContentClass", $attribute->ID, "metaNameIdentifier"
        );
        $metaDescription = klpBcType::postVarName(
            "ContentClass", $attribute->ID, "metaDescriptionIdentifier"
        );

        $data = array(
            $playerIdName => "123",
            $playerKeyName => "Testing",
            $playerWidthName => 500,
            $playerHeightName => 600,
            $metaName => "My video",
            $metaDescription => "My video description"
        );

        return $data;
    }

    protected function postVarName( $base, $string, $id )
    {
        return $base . "_" .  klpBcType::DATA_TYPE_STRING . "_{$string}_{$id}";
    }
}
