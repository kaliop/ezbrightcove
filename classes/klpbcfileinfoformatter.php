<?php
/**
 * File containing the klpBcFileInfoFormatter class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Generates a array of information for a given file (SplFileInfo)
 **/
class klpBcFileInfoFormatter
{
    /**
     * Creates a new instance of this class
     *
     * @param string $baseDirectory Base directory to use for generating
     *                              relative paths
     **/
    public function __construct( $baseDirectory )
    {
        $this->baseDir = $baseDirectory;

        if ( substr( $this->baseDir, -1, 1 ) !== DIRECTORY_SEPARATOR )
            $this->baseDir .= DIRECTORY_SEPARATOR;
    }

    /**
     * Returns an array with filename and file size
     *
     * @param SplFileInfo $fileInfo File to format
     * @return array Array( <filename> => array( 'filename' => <filename>,
     *                                           'filesize' => <file size> )
     **/
    public function getArray( $fileInfo )
    {
        $info = array( 
            'filename' => $fileInfo->getFilename(),
            'size' => $fileInfo->getSize(),
            'isfile' => $fileInfo->isFile(),
            'path' => $fileInfo->getPathname(),
            'relativepath' => $this->calcRelativePath( $fileInfo->getPathName() ),
        );

        return $info;
    }

    /**
     * Removes $this->baseDir from $absolutePath
     *
     * @parma string $absolutePath Absolute path to file
     * @return string Relative path
     **/
    protected function calcRelativePath( $absolutePath )
    {
        return str_replace( $this->baseDir, "", $absolutePath );
    }
}
