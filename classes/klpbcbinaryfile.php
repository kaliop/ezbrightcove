<?php
/**
 * File containing the klpBcBinaryFile class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Handles storing of uploaded binary files.
 *
 * Files are stored under the 'brightcove' subdirectory inside var/storage.
 */
class klpBcBinaryFile
{
    /**
     * Which subdirectory under var/storage/ to use
     *
     * @var string
     */
    const STORAGE_SUB_DIR = "original";

    /**
     * Stores a binary file using eZBinaryFile from a HTTP upload
     *
     * @param array $data
     * @param int $id Id of attribute to associate the file with
     * @param int $version Which version to associate the file with
     * @return bool False if store failed
     */
    public function storeHttpFile( $data, $id, $version )
    {
        $tempFile = new eZHTTPFile( "file", $data );
        $mime = $this->mime( $tempFile );
        $tempFile->setMimeType( $mime );

        $extension = eZFile::suffix( $tempFile->attribute( "original_filename" ) );
        if ( !$tempFile->store( self::STORAGE_SUB_DIR, $extension ) )
            return false;

        $binary = $this->findOrCreateBinary( $id, $version );
        $binary->setAttribute( "mime_type", $mime );
        $binary->setAttribute( "version", $version );
        $binary->setAttribute( "contentobject_attribute_id", $id );
        $binary->setAttribute(
            "filename", basename( $tempFile->attribute( "filename" ) )
        );
        $binary->setAttribute(
            "original_filename", $tempFile->attribute( "original_filename" )
        );
        $binary->store();

        $filePath = $tempFile->attribute( 'filename' );
        $fileHandler = eZClusterFileHandler::instance();
        $fileHandler->fileStore( $filePath, 'binaryfile', true, $mime );

        return true;
    }

    /**
     * Stores a local binary file using eZBinaryFile
     *
     * @param int $id Id of attribute to associate the file with
     * @param int $version Which version to associate the file with
     * @param string $filePath Full path to local file
     * @return bool False if store failed
     */
    public function store( $id, $version, $filePath )
    {
        $mimeData = eZMimeType::findByFileContents( $filePath );
        $newFullpath = $this->copyFileToStorageDir( $filePath, $mimeData['name'] );
        if ( !$newFullpath )
            return false;

        $binary = $this->findOrCreateBinary( $id, $version );
        $binary->setAttribute( "mime_type", $mimeData['name'] );
        $binary->setAttribute( "version", $version );
        $binary->setAttribute( "contentobject_attribute_id", $id );
        $binary->setAttribute(
            "filename", basename( $newFullpath )
        );
        $binary->setAttribute(
            "original_filename", basename( $filePath )
        );
        $binary->store();

        return true;
    }

    /**
     * Deletes a binary file reference and the physical file if there's no
     * references to it anymore.
     *
     * If $version is passed in as null all versions are removed.
     *
     * @param int $id Id of attribute to associate the file with
     * @param int $version Which version to associate the file with
     **/
    public function remove( $id, $version = null )
    {
        $sys = eZSys::instance();
        $storage_dir = $sys->storageDirectory();

        if ( $version == null )
        {
            $binaryFiles = eZBinaryFile::fetch( $id );
            eZBinaryFile::removeByID( $id, null );

            foreach ( $binaryFiles as $binaryFile )
            {
                $mimeType =  $binaryFile->attribute( "mime_type" );
                list( $prefix, $suffix ) = explode('/', $mimeType );
                $orig_dir = $storage_dir . '/original/' . $prefix;
                $fileName = $binaryFile->attribute( "filename" );

                // Check if there are any other records in ezbinaryfile that point to that fileName.
                $binaryObjectsWithSameFileName = eZBinaryFile::fetchByFileName( $fileName );

                $filePath = $orig_dir . "/" . $fileName;
                $file = eZClusterFileHandler::instance( $filePath );

                if ( $file->exists() and count( $binaryObjectsWithSameFileName ) < 1 )
                    $file->delete();
            }
        }
        else
        {
            $count = 0;
            $binaryFile = eZBinaryFile::fetch( $id, $version );
            if ( $binaryFile != null )
            {
                $mimeType =  $binaryFile->attribute( "mime_type" );
                list( $prefix, $suffix ) = explode('/', $mimeType );
                $orig_dir = $storage_dir . "/original/" . $prefix;
                $fileName = $binaryFile->attribute( "filename" );

                eZBinaryFile::removeByID( $id, $version );

                // Check if there are any other records in ezbinaryfile that point to that fileName.
                $binaryObjectsWithSameFileName = eZBinaryFile::fetchByFileName( $fileName );

                $filePath = $orig_dir . "/" . $fileName;
                $file = eZClusterFileHandler::instance( $filePath );

                if ( $file->exists() and count( $binaryObjectsWithSameFileName ) < 1 )
                    $file->delete();
            }
        }
    }

    /**
     * Tries to figure out the mime type for a file
     *
     * @param eZHTTPFile $tempFile
     * @return string Mime type
     */
    protected function mime( $tempFile )
    {
        $mimeData = eZMimeType::findByFileContents(
            $tempFile->attribute( "original_filename" )
        );
        $mime = $mimeData['name'];

        if ( $mime == '' )
            $mime = $tempFile->attribute( "mime_type" );

        return $mime;
    }

    /**
     * Tries to find a eZBinaryFile or creates it if not found
     *
     * @param $attributeId Content object attribute id
     * @param $version Content object attribute version
     * @return eZBinaryFile
     */
    protected function findOrCreateBinary( $attributeId, $version )
    {
        $binary = eZBinaryFile::fetch( $attributeId, $version );
        if ( $binary === null )
            $binary = eZBinaryFile::create( $attributeId, $version );

        return $binary;
    }

    /**
     * Copies a file to the correct directory under var/.../storage.
     *
     * @param string $fullPath Full path to file to store
     * @param string $mimeType The mime type of the file
     * @return bool True if the copy was successful
     **/
    protected function copyFileToStorageDir( $fullPath, $mimeType )
    {
        if ( !file_exists( $fullPath ) )
            return false;

        list( $group, $type ) = explode( '/', $mimeType );
        $dirname = self::getStorageDir() . '/' . self::STORAGE_SUB_DIR . "/{$group}";
        if ( !is_dir( $dirname ) )
            mkdir( $dirname, 0777, true );

        $newFullpath = $dirname
                       . '/'
                       . md5( $fullPath )
                       . '.'
                       . $this->getFileExtension( $fullPath );

        if ( copy( $fullPath, $newFullpath ) )
        {
            $mimeData = eZMimeType::findByFileContents( $newFullpath );
            $fileHandler = eZClusterFileHandler::instance();
            $fileHandler->fileStore( $newFullpath, 'binaryfile', true, $mimeData );

            return $newFullpath;
        }

        return false;
    }

    /**
     * Return eZ Publish's storage directory for binary files
     *
     * @return string Path to storage directory
     **/
    protected function getStorageDir()
    {
        $siteIni = eZINI::instance( 'site.ini' );
        $varDir = $siteIni->variable( 'FileSettings', 'VarDir' );
        $storageDir = $siteIni->variable( 'FileSettings', 'StorageDir' );
        return $varDir.'/'.$storageDir;
    }

    /**
     * Returns the file extension for a file
     *
	 * @param string $fullPath Path to file
	 * @return string
	 */
	protected function getFileExtension( $fullPath )
	{
		if ( is_string( $fullPath ) && !empty( $fullPath ) )
		{
			$pathParts = pathinfo( $fullPath );
			return $pathParts['extension'];
		}

		return '';
	}
}
