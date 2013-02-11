<?php
/**
 * File containing the klpBcType class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Datatype for integrating with the Brightcove Video Service.
 */
class klpBcType extends eZDataType
{
    /**
     * Holds the datatype string
     *
     * @var string
     */
    const DATA_TYPE_STRING	= 'klpbc';

    /**
     * Holds the name of the database column for where to store the class options.
     *
     * @var string
     */
    const CLASS_OPTIONS_FIELD = 'data_text5';

    /**
     * List of valid class options names for this datatype
     *
     * @var array( string )
     */
    private $validOptions = array(
        'playerId',
        'playerKey',
        'playerWidth',
        'playerHeight',
        'playerBgColor',
        'maxVideoSize',
        'metaNameIdentifier',
        'metaDescriptionIdentifier'
    );

    /**
     * List of required options names for this datatype
     *
     * @var array( string )
     */
    private $requiredOptions = array(
        'playerId',
        'playerKey',
        'playerWidth',
        'playerHeight',
        'metaNameIdentifier',
        'metaDescriptionIdentifier'
    );

    /**
     * Constructor
     *
     * @see eZDataType::__construct()
     */
    public function __construct()
    {
        $this->tr = 'extension/klpbc/datatypes';

        parent::__construct(
            self::DATA_TYPE_STRING, ezpI18n::tr( $this->tr, 'Brightcove media' )
        );

        // DI container
        $this->dic = klpBcDiContainereZTiein::getInstance();

        // Initialize class options
        $this->options = $this->dic->getTypeOptions(
            $this->validOptions, $this->requiredOptions
        );

        // Video input system
        $this->inputs = $this->dic->getVideoInput();
        $this->Attributes['video_inputs'] = $this->inputs->getAvailable();
    }

    /**
     * Returns the expected POST variable name for an string
     *
     * @param string $base
     * @param int $classAttribute
     * @param string $string
     * @return string POST variable name
     */
    public static function postVarName( $base, $classAttributeId, $string )
    {
        return "{$base}_" . self::DATA_TYPE_STRING . "_{$string}_{$classAttributeId}";
    }

    /**
     * @see eZDataType::classAttributeContent()
     */
    public function classAttributeContent( $classAttribute )
    {
        $serializedData = $classAttribute->attribute( self::CLASS_OPTIONS_FIELD );

        if ( !empty( $serializedData ) )
            $this->options->fromJson( $serializedData );

        return $this->options;
    }

    /**
     * @see eZDataType::validateClassAttributeHTTPInput()
     */
    public function validateClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $validator = $this->dic->getTypeClassInputValidator(
            $http, $base, self::DATA_TYPE_STRING, $classAttribute->ID
        );
        $isValid = $validator->isValid( $this->requiredOptions );

        return $isValid ? eZInputValidator::STATE_ACCEPTED : eZInputValidator::STATE_INVALID;
    }

    /**
     * @see eZDataType::fetchClassAttributeHTTPInput()
     */
    public function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
        $options = $this->classAttributeContent( $classAttribute );

        foreach( $this->validOptions as $option )
        {
            $optionVarName = $this->postVarName( $base, $classAttribute->ID, $option );
            if ( $http->hasPostVariable( $optionVarName ) )
                $options->setAttribute( $option, $http->postVariable( $optionVarName ) );
        }

        $classAttribute->setAttribute( self::CLASS_OPTIONS_FIELD, $options->toJson() );
        $classAttribute->store();
    }

    /**
     * @see eZDataType::initializeObjectAttribute()
     */
    public function initializeObjectAttribute( $attribute, $currentVersion, $originalAttribute )
    {
        if ( !$currentVersion )
            return;

        $attributeId = $originalAttribute->attribute( "id" );
        $newVersion = $attribute->attribute( "version" );

        $oldVideo = $this->dic->getVideo()->fetch(
            $attributeId, $currentVersion
        );

        if ( $oldVideo )
        {
            $oldVideo->setAttribute( 'contentobject_attribute_id', $attributeId );
            $oldVideo->setAttribute( "version", $newVersion );
            $oldVideo->store();

            if ( $oldVideo->attribute( 'input_type_identifier' ) )
            {
                $this->inputs->getInput(
                    $oldVideo->attribute( 'input_type_identifier' )
                )->initialize( $attributeId, $currentVersion, $newVersion );
            }
        }
    }

    /**
     * @see eZDataType::objectAttributeContent()
     */
    public function objectAttributeContent( $contentObjectAttribute )
    {
        $video = $this->dic->getVideo()->fetch(
            $contentObjectAttribute->attribute( "id" ),
            $contentObjectAttribute->attribute( "version" )
        );

        if ( !$video )
        {
            $video = $this->dic->getVideo();
            $video->setAttribute(
                'contentobject_attribute_id', $contentObjectAttribute->attribute( "id" )
            );
            $video->setAttribute(
                'version', $contentObjectAttribute->attribute( "version" )
            );
        }

        return $video;
    }

    /**
     * @see eZDataType::validateObjectAttributeHTTPInput()
     */
    public function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $isRequired = $contentObjectAttribute->validateIsRequired();

        $currentInputType = $http->postVariable(
            $this->inputTypeHttpVariable( $base, $contentObjectAttribute->ID )
        );

        if ( !$currentInputType )
        {
            $contentObjectAttribute->setValidationError(
                ezpI18n::tr( $this->tr, "No video input type was selected" )
            );
            return eZInputValidator::STATE_INVALID;
        }

        $hasOriginalData = $this->hasOriginalVideo(
            $contentObjectAttribute, $currentInputType
        );

        $inputPostData = $this->postDataForInput(
            $currentInputType, $base, $contentObjectAttribute->ID
        );

        $isValid = $this->inputs->getInput( $currentInputType )->isInputDataValid(
            $inputPostData, $isRequired, $hasOriginalData
        );

        if ( !$isValid )
        {
            $contentObjectAttribute->setValidationError(
                ezpI18n::tr( $this->tr,
                    $this->inputs->getInput( $currentInputType )->lastError()
                )
            );

            return eZInputValidator::STATE_INVALID;
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    /**
     * @see eZDataType::fetchObjectAttributeHTTPInput()
     */
    public function fetchObjectAttributeHTTPInput( $http, $base, $attribute )
    {
        $currentInputType = $http->postVariable(
            $this->inputTypeHttpVariable( $base, $attribute->ID )
        );

        if ( !$currentInputType )
            return;

        $video = $attribute->content();
        $video = $this->setupVideo( $video, $attribute, $currentInputType );
        $video->store();

        $attribute->setContent( $video );
        $attribute->store();

        $post = $this->postDataForInput( $currentInputType, $base, $attribute->ID );
        $inputWasStored = $this->inputs->getInput( $currentInputType )->store(
            $post, $attribute->attribute( 'id' ), $attribute->attribute( 'version' )
        );

        // Make sure the attribute's content is not cached in case the
        // input type has changed the video
        $attribute->setContent( null );

        if ( $inputWasStored )
            $this->dic->getQueue()->moveToStart( $attribute->content() );
    }

    /**
     * @see eZDataType::onPublish()
     */
    public function onPublish( $contentObjectAttribute, $contentObject, $publishedNodes )
    {
        $video = $contentObjectAttribute->content();
        $this->dic->getQueue()->insert( $video );
    }

    /**
     * @see eZDataType::customObjectAttributeHTTPAction()
     *
     * Currently a 'delete' hook is supported. This hook will call
     * deleteStoredObjectAttribute()
     */
    public function customObjectAttributeHTTPAction( $http, $action, $attribute, $params )
    {
        if ( $action == "delete" )
        {
            $contentObjectAttributeID = $attribute->attribute( "id" );
            $version = $attribute->attribute( "version" );
            $this->deleteStoredObjectAttribute( $attribute, $version );
        }
    }

    /**
     * @see eZDataType::deleteStoredObjectAttribute()
     */
    public function deleteStoredObjectAttribute( $objectAttribute, $version = null )
    {
        $video = $objectAttribute->content();

        foreach( $this->inputs->inputTypes() as $input )
        {
            $input->delete(
                $video->attribute( 'contentobject_attribute_id' ), $version
            );
        }

        $this->dic->getQueue()->delete( $video, $version );
    }

    /**
     * @see eZDataType::serializeContentClassAttribute()
     */
    public function serializeContentClassAttribute( $classAttribute, $attributeNode, $paramNode )
    {
        $classAttribute->content()->toXml( $paramNode );
    }

    /**
     * @see eZDataType::unserializeContentClassAttribute()
     */
    public function unserializeContentClassAttribute( $classAttribute, $attributeNode, $paramNode )
    {
        $options = $classAttribute->content()->fromXml( $paramNode );

        $classAttribute->setAttribute( self::CLASS_OPTIONS_FIELD, $options->toJson() );
        $classAttribute->store();
    }

    /**
     * Populates the brightcove datatype with the correct values
     * based upon the string passed in $string.
     *
     * The string that must be passed looks like the following:
     * "input type|input type value|<brightcove id>"
     *
     * Example:
     * <code>
     * upload|/tmp/file.mp4
     * </code>
     * <code>
     * upload|/tmp/file.mp4|2006221000001
     * </code>
     *
     * If a brightcove id is passed in the video will be set to completed. If
     * no brightcove id is specified the video will be sent for processing.
     *
     * @param object eZContentObjectAttribute
     * @param string $string The string as described in the example.
     * @return object|false The newly video object or false on error
     * @see eZDataType::fromString()
     */
    public function fromString( $contentObjectAttribute, $string )
    {
        list( $result, $inputType, $inputTypeValue, $bId ) =
            $this->parseFromStringInput( $string );

        if ( !$result )
            return false;

        $result = $this->inputs->getInput( $inputType )->fromString(
            $contentObjectAttribute->attribute( 'id' ),
            $contentObjectAttribute->attribute( 'version' ),
            $inputTypeValue
        );

        if ( !$result )
            return false;

        $video = $contentObjectAttribute->content();
        $video->setAttribute( 'input_type_identifier', $inputType );
        $video->setAttribute( 'brightcove_id', $bId );

        if ( $bId )
        {
            // @TODO: Change to $queue->moveToCompleted()?
            $video->setAttribute( 'state', $video->getStateValue( 'COMPLETED' ) );
            $video->store();
        }
        else
        {
            $this->dic->getQueue()->insert( $video );
        }

        return $video;
    }

    /**
     * Returns simplified string representation of an attribute
     *
     * Note that not all videos have a brightcove id (this depends on the
     * video's state)
     *
     * @param eZContentObjectAttribute $contentObjectAttribute
     * @return string "input type|input type value|brightcove id"
     * @see eZDataType::toString()
     **/
    public function toString( $contentObjectAttribute )
    {
        $video = $contentObjectAttribute->content();

        $string = array(
            $video->attribute( 'input_type_identifier' ),
            $video->getFileUrl(),
            $video->attribute( 'brightcove_id' )
        );

        return implode( "|", $string );
    }

    /**
     * @see eZDataType::storedFileInformation()
     **/
    public function storedFileInformation( $object, $version, $language, $attribute )
    {
        $video = $attribute->content();
        $inputType = $video->attribute( 'input_type_identifier' );

        return $this->inputs->getInput( $inputType )->getFileInfo(
            $attribute->attribute( "id" ),
            $attribute->attribute( "version" )
        );
    }

    /**
     * @see eZDataType::hasStoredFileInformation()
     **/
    public function hasStoredFileInformation( $object, $version, $language, $attribute )
    {
        $video = $attribute->content();
        $inputType = $video->attribute( 'input_type_identifier' );

        return $this->inputs->getInput( $inputType )->canDownload();
    }

    /**
     * @see eZDataType::objectDisplayInformation()
     */
    public function objectDisplayInformation( $objectAttribute, $mergeInfo = false )
    {
        $info = array(
            'edit' => array( 'grouped_input' => true ),
            'view' => array( 'grouped_input' => true ),
        );
        return eZDataType::objectDisplayInformation( $objectAttribute, $info );
    }

    /**
     * Returns whether the attribute has a original video set
     *
     * If the input type is different then we assume no original video.
     *
     * @param eZContentObjectAttribute $attribute
     * @param string $currentInputType Current input type
     * @return bool
     **/
    public function hasOriginalVideo( $attribute, $currentInputType )
    {
        $video = $attribute->content();

        if ( $currentInputType != $video->attribute( 'input_type_identifier' ) )
            return false;

        return $video->hasOriginalVideo();
    }

    /**
     * Sets up video with data not specific to a video input type
     *
     * @param klpBcVideo Video
     * @param eZContentObjectAttribute $attribute
     * @param string $currentInputType Current input type
     * @return klpBcVideo video with attributes set (not stored)
     **/
    public function setupVideo( $video, $attribute, $currentInputType )
    {
        if ( $currentInputType != $video->attribute( 'input_type_identifier' ) )
            $video->setAttribute( 'brightcove_id', null );

        $video->setAttribute( 'input_type_identifier', $currentInputType );
        $video->setAttribute( 'version', $attribute->attribute( 'version' ) );
        $video->setAttribute(
            'contentobject_attribute_id', $attribute->attribute( 'id' )
        );

        return $video;
    }

    /**
     * Returns an array of all the parts of the fromString input
     *
     * @param string $string The fromString input string
     * @return array(result, input type, input type value, brightcove id)
     **/
    protected function parseFromStringInput( $string )
    {
        $parts = explode( "|", $string );

        if ( count( $parts ) < 2 )
            return array( false, null, null, null );

        $inputType = $parts[0];
        $inputTypeValue = $parts[1];
        $bId = isset( $parts[2] ) ? $parts[2] : null;

        return array( true, $inputType, $inputTypeValue, $bId );
    }

    /**
     * Returns all POST data related to the input type
     *
     * @param string $inputTypeName Video input type identifier
     * @param string $base
     * @param int $classAttribute
     * @return array( post variable name => post data )
     */
    protected function postDataForInput( $inputTypeName, $base, $contentObjectAttributeId )
    {
        $baseInputVarName = $this->postVarName(
            $base, $contentObjectAttributeId, "video_input"
        );

        $result = array();
        foreach ( array( $_POST, $_FILES ) as $inputArray )
        {
            foreach( $inputArray as $postVar => $postData )
            {
                if ( strpos( $postVar, $baseInputVarName ) === false )
                    continue;

                $key = str_replace( $baseInputVarName, "", $postVar );
                $key = trim( $key, "_" );
                $result[$key] = $postData;
            }
        }

        return $result;
    }

    /**
     * Returns the name of the 'video input type' http post variable
     *
     * @param string $base
     * @param int $classAttribute
     * @return string POST variable name
     */
    protected function inputTypeHttpVariable( $base, $classAttributeID )
    {
        return $this->postVarName( $base, $classAttributeID, "video_input" );
    }
}

eZDataType::register( klpBcType::DATA_TYPE_STRING, 'klpBcType' );
