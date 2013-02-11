<?php
/**
 * File containing the klpBcUploadVideoInputType class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Class for handling local video uploads (that is videos uploaded from your own
 * computer).
 */
class klpBcUploadVideoInputType implements klpBcVideoInputTypeInterface
{
    /**
     * Creates a new instance of this class
     *
     * @param string $identifier String that identifies this input type
     */
    public function __construct( $identifier )
    {
        $this->tr = 'extension/klpbc/inputtype/upload';
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
        if ( $hasOriginalData )
            $isRequired = false;

        if ( empty( $data ) && $isRequired )
            return $this->error( ezpI18n::tr( $this->tr, "Missing data" ) );

        if ( empty( $data ) && !$isRequired )
            return true;

        if ( !isset( $data['file'] ) )
            return $this->error( ezpI18n::tr( $this->tr, "Missing file data" ) );

        $error = $data['file']['error'];

        if ( $error == UPLOAD_ERR_NO_FILE && $isRequired )
            return $this->error( ezpI18n::tr( $this->tr, "Please choose a file to upload" ) );

        if ( $error == UPLOAD_ERR_NO_FILE && !$isRequired )
            return true;

        if ( $error == UPLOAD_ERR_FORM_SIZE || $error == UPLOAD_ERR_INI_SIZE )
            return $this->error( ezpI18n::tr( $this->tr, "File exceeds the max file size" ) );

        if ( $error >= 1 )
            return $this->error( ezpI18n::tr( $this->tr,
                "There was an problem with the uploaded file ({$error})" )
            );

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
     * Fetch binary file by content object attribute id and version
     *
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     * @return object
     */
    public function fetch( $id, $version )
    {
        return eZBinaryFile::fetch( $id, $version );
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
        $oldFile = eZBinaryFile::fetch( $id, $currentVersion );
        if ( $oldFile )
        {
            $oldFile->setAttribute( 'contentobject_attribute_id', $id );
            $oldFile->setAttribute( "version", $newVersion );
            $oldFile->store();
        }
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
        if ( !$this->hasUploadedFile( $data['file'] ) )
            return false;

        $binaryFile = new klpBcBinaryFile();
        $binaryFile->storeHttpFile( $data['file'], $id, $version );

        return true;
    }

    /**
     * Deletes the uploaded video and any references to it
     *
     * @param int $id Content object attribute id
     * @param int|null $version Content object attribute version
     **/
    public function delete( $id, $version )
    {
        $binaryFile = new klpBcBinaryFile();
        $binaryFile->remove( $id, $version );
    }

    /**
     * Stores a local binary file
     *
     * @param int $id Id of attribute to associate the file with
     * @param int $version Which version to associate the file with
     * @param string $filePath Full path to local file
     * @return bool False if file could not be stored
     */
    public function fromString( $id, $version, $filePath )
    {
        $binaryFile = new klpBcBinaryFile();
        return $binaryFile->store( $id, $version, $filePath );
    }

    /**
     * Returns path to the original video file
     *
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     * @return string or null if there's no video file
     */
    public function getFileUrl( $id, $version )
    {
        $file = $this->fetch( $id, $version );

        if ( $file )
            return $file->attribute( 'filepath' );
    }

    /**
     * Returns array of file information
     *
     * @param int $id Content object attribute id
     * @param int $version Content object attribute version
     * @return array ( mime_type => string,
     *                 filename => string,
     *                 original_filename => string,
     *                 filepath => string )
     **/
    public function getFileInfo( $id, $version )
    {
        $file = $this->fetch( $id, $version );
        if ( $file )
            return $file->storedFileInfo();

        return false;
    }

    /**
     * We support file download
     *
     * @return bool True
     **/
    public function canDownload()
    {
        return true;
    }

    /**
     * Indicates that this video input type requires processing
     *
     * @return true
     */
    public function requiresProcessing()
    {
        return true;
    }

    /**
     * Checks if $fileData actually contains a uploaded file
     *
     * @param array $fileData $_FILES variable structure
     * @return bool
     */
    protected function hasUploadedFile( $fileData )
    {
        if ( empty( $fileData ) )
            return false;
        if ( $fileData['error'] == UPLOAD_ERR_NO_FILE )
            return false;

        return true;
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
