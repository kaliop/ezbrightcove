<?php
/**
 * File containing the klpBcBrightcoveVideoInputType class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Class for handling videos sourced from Brightcove
 */
class klpBcBrightcoveVideoInputType implements klpBcVideoInputTypeInterface
{
    /**
     * Creates a new instance of this class
     *
     * @param string $identifier String that identifies this input type
     */
    public function __construct( $identifier )
    {
        $this->tr = 'extension/klpbc/inputtype/brightcove';
        $this->identifier = $identifier;
        $this->lastError = '';
    }

    /**
     * Validates input data (typically form data)
     *
     * @param array $data Array of POST data
     * @param bool $isRequired Whether data is required to be present or not
     * @param bool $hasOriginalData True if we've already have stored some data
     * @return bool
     */
    public function isInputDataValid( $data, $isRequired, $hasOriginalData )
    {
        if ( empty( $data ) && $isRequired )
            return $this->error( ezpI18n::tr( $this->tr, "Missing data" ) );

        if ( empty( $data ) && !$isRequired )
            return true;

        if ( !$this->hasBrightcoveId( $data ) && $isRequired )
            return $this->error( ezpI18n::tr( $this->tr, "Missing Brightcove ID" ) );

        if ( empty( $data['brightcove_id'] ) && $isRequired )
            return $this->error( ezpI18n::tr( $this->tr, "Missing Brightcove ID" ) );

        return true;
    }

    /**
     * Returns the last validation error
     *
     * @return string Validation error or '' if no error
     */
    public function lastError()
    {
        return $this->lastError;
    }

    /**
     * Fetch object by content object attribute id and version
     *
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     * @return object
     */
    public function fetch( $id, $version )
    {
        return klpbcVideo::fetch( $id, $version );
    }

    /**
     * Initializes new value for a new version
     *
     * @param int $id Content object attribute id
     * @param int $currentVersion Current content object attribute version
     * @param int $newVersion New content object attribute version
     */
    public function initialize( $id, $currentVersion, $newVersion )
    {
    }

    /**
     * Stores the uploaded file
     *
     * @param array $data
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     * @return bool True if file was stored, false if not.
     */
    public function store( $data, $id, $version )
    {
        if ( !$this->hasBrightcoveId( $data ) )
            return false;

        $video = $this->fetch( $id, $version );
        if ( !$video )
        {
            $video = new klpbcVideo( false );
            $video->setAttribute( 'contentobject_attribute_id', $id );
            $video->setAttribute( 'version', $version );
        }

        // Don't save anything if the value hasn't changed.
        if ( $video->attribute( 'brightcove_id' ) == $data['brightcove_id'] )
            return false;

        $video->setAttribute( 'brightcove_id', $data['brightcove_id'] );
        $video->store();

        return true;
    }

    /**
     * Deletes the brightcove id
     *
     * @param int $id Content object attribute id
     * @param int|null $version Content object attribute version
     **/
    public function delete( $id, $version )
    {
        $video = $this->fetch( $id, $version );
        if ( $video )
        {
            $video->setAttribute( 'brightcove_id', '' );
            $video->store();
        }
    }

    /**
     * Does nothing as the datatype takes care of restoring the brightcove id
     * for us.
     *
     * @param int $id Id of attribute to associate the file with
     * @param int $version Which version to associate the file with
     * @param string $filePath Full path to local file
     * @return bool False if file could not be stored
     */
    public function fromString( $id, $version, $filePath )
    {
    }

    /**
     * Does nothing as we do not have a file url
     *
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     * @return string or null if there's no video file
     */
    public function getFileUrl( $id, $version )
    {
    }

    /**
     * Returns array of file information
     *
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     * @return false
     **/
    public function getFileInfo( $id, $version )
    {
        return false;
    }

    /**
     * We don't support file download
     *
     * @return bool False
     **/
    public function canDownload()
    {
        return false;
    }

    /**
     * Indicates that this video input type does not require processing
     *
     * @return false
     */
    public function requiresProcessing()
    {
        return false;
    }

    /**
     * Checks that the brightcove id is in $data
     *
     * @param $array $data
     * @return bool
     **/
    protected function hasBrightcoveId( $data )
    {
        if ( isset( $data['brightcove_id'] ) )
            return true;

        return false;
    }

    /**
     * Sets an error message and returns false
     *
     * @param string $message Error message
     * @return false
     * @author Me
     */
    protected function error( $message )
    {
        $this->lastError = $message;
        return false;
    }
}
