<?php
/**
 * File containing the klpBcServerFileVideoInputType class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Class for supporting videos via arbitrary ServerFiles
 */
class klpBcServerFileVideoInputType implements klpBcVideoInputTypeInterface
{
    /**
     * Creates a new instance of this class
     *
     * @param string $identifier String that identifies this input type
     */
    public function __construct( $identifier, $config = null )
    {
        $this->tr = 'extension/klpbc/inputtype/serverfile';
        $this->identifier = $identifier;
        $this->lastError = '';

        $this->serverfileInstance = new klpBcServerFile( false );

        if ( !$config )
            $config = klpDiContainer::getInstance()->getServerFileConfig();

        $this->config = $config;
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
            return $this->error( ezpI18n::tr( $this->tr, "An URL is required" ) );

        if ( empty( $data ) && !$isRequired )
            return true;

        if ( !isset( $data['serverfile'] ) && $isRequired )
            return $this->error( ezpI18n::tr( $this->tr, "Missing URL" ) );

        if ( empty( $data['serverfile'] ) && $isRequired )
            return $this->error( ezpI18n::tr( $this->tr, "Missing URL" ) );

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
        return $this->runServerFileMethod( 'fetch', $id, $version );
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
        $oldServerFile = $this->runServerFileMethod( 'fetch', $id, $currentVersion );
        if ( $oldServerFile )
        {
            $oldServerFile->setAttribute( 'contentobject_attribute_id', $id );
            $oldServerFile->setAttribute( "version", $newVersion );
            $oldServerFile->store();
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
        if ( !isset( $data['serverfile'] ) )
            return false;

        $serverfile = $this->fetch( $id, $version );
        if ( !$serverfile )
            $serverfile = $this->serverfileInstance;

        $serverfile->setAttribute( 'contentobject_attribute_id', $id );
        $serverfile->setAttribute( 'version', $version );
        $serverfile->setAttribute( 'filepath', $data['serverfile'] );
        $serverfile->store();

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
        $this->runServerFileMethod( 'delete', $id, $version );
    }

    /**
     * Stores a serverfile
     *
     * @param int $id Id of attribute to associate the file with
     * @param int $version Which version to associate the file with
     * @param string $serverfile ServerFile to video
     * @return bool False if file could not be stored
     */
    public function fromString( $id, $version, $serverfile )
    {
        $this->store( array( 'serverfile' => $serverfile ), $id, $version );
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
        if ( !$file )
            return null;

        $rootDir = $this->config->rootDirectory;
        $rootDir = rtrim( $rootDir, DIRECTORY_SEPARATOR );
        $filePath = ltrim( $file->attribute( 'filepath' ), DIRECTORY_SEPARATOR );
        $path = $rootDir . DIRECTORY_SEPARATOR . $filePath;

        return $path;
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
        return false;
    }

    /**
     * We support file download
     *
     * @return bool True
     **/
    public function canDownload()
    {
        return false;
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
     * Implements eZPersistentObjet attribute interface to allow this object to
     * be embedded in eZ templates.
     */
    public function attributes()
    {
        return array( 'is_enabled' );
    }

    /**
     * Implements eZPersistentObjet attribute interface to allow this object to
     * be embedded in eZ templates.
     */
    public function attribute( $name )
    {
        return $this->config->isEnabled;
    }

    /**
     * Implements eZPersistentObjet attribute interface to allow this object to
     * be embedded in eZ templates.
     */
    public function hasAttribute( $name )
    {
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

    /**
     * Runs a method on the ServerFile persistence class
     *
     * This method takes a variable number or arguments. Any arguments after
     * $method will be passed on to the ServerFile class
     *
     * @param string $method Name of method to run
     * @return mixed Return value of the api method
     */
    protected function runServerFileMethod( $method /* ... */ )
    {
        $args = func_get_args();
        array_shift( $args ); // Get rid of first arg which is $method

        return call_user_func_array(
            array( $this->serverfileInstance, $method ), $args
        );
    }
}
