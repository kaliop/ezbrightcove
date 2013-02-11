<?php
/**
 * File containing the klpBcFileBrowser class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Recursively scans a directory and returns an array of all files and
 * directories. What information is included in the array is decided by
 * a separate 'formatter' class.
 **/
class klpBcFileBrowser
{
    /**
     * Scans $directory for files and directories and returns an array
     * formatted using $fileInfoFormatter.
     *
     * Example:
     * File structure:
     * - A/
     * - A/file1.text
     * - file2.txt
     *
     * Resulting array:
     * array(
     *     0 => array(
     *         <Dir A/, contents decided by $fileInfoFormatter>
     *         'children' => array(
     *             0 => array(
     *                 <File file1.txt, contents decided by $fileInfoFormatter>
     *             )
     *         )
     *     ),
     *     1 => array(
     *         <File file2.txt, contents decided by $fileInfoFormatter>
     *     )
     * )
     *
     * @param string $directory Path to directory
     * @param mixed $fileInfoFormatter File info formatter. Must have a method
     *                                 'getArray' that returns an array.
     * @return array|string If string then there's been an error and the string
     *                      is the error message.
     **/
    public function scan( $directory, $fileInfoFormatter )
    {
        $directory = $this->normalizeDirectory( $directory );

        try
        {
            $files = array();
            $it = new DirectoryIterator( $directory );
            foreach( $it as $fileInfo )
            {
                $info = $this->getChildren( $fileInfo, $fileInfoFormatter );
                if ( $info )
                    $files[] = $info;
            }
        }
        catch ( UnexpectedValueException $e )
        {
            return $e->getMessage();
        }

        usort( $files, array( $this, 'sort' ) );
        return $files;
    }

    /**
     * Recursively generates an array for a directory
     *
     * @param SplFileInfo $fileInfo The current file/dir.
     * @param mixed $fileInfoFormatter File info formatter. Must have a method
     *                                 'getArray' that returns an array.
     * @param bool $isRecursive Flag used internally in the method to know if 
     *                          it's called by itself or some other function. 
     *                          Don't touch this flag.
     * @return array
     **/
    protected function getChildren( $fileInfo, $formatter, $isRecursive = false )
    {
        if ( $fileInfo->isDot() )
            return;

        if ( $fileInfo->isFile() )
            return $formatter->getArray( $fileInfo );

        if ( !$isRecursive )
            $info = $formatter->getArray( $fileInfo );

        $it = new DirectoryIterator( $fileInfo->getPathname() );
        foreach( $it as $subFileInfo )
        {
            if ( $subFileInfo->isDot() )
                continue;

            $infoArray = $formatter->getArray( $subFileInfo );

            if ( $subFileInfo->isDir() ) {
                $infoArray['children'] = $this->getChildren(
                    $subFileInfo, $formatter, true
                );
                usort( $infoArray['children'], array( $this, 'sort' ) );
            }

            if ( $isRecursive )
                $info[] = $infoArray;
            else
            {
                $info['children'][] = $infoArray;
                usort( $info['children'], array( $this, 'sort' ) );
            }
        }

        return $info;
    }

    /**
     * Appends directory separator to end of $dir if not present
     *
     * @param string $dir Directory path
     * @return Path with trailing directory separator
     **/
    protected function normalizeDirectory( $dir )
    {
        if ( substr( $dir, -1, 1 ) === DIRECTORY_SEPARATOR )
            return $dir;

        return $dir . DIRECTORY_SEPARATOR;
    }

    /**
     * Sort files by filename A-Z case-insensitive
     *
     * @param array $a File a
     * @param array $b File b
     * @return 1, 0, -1
     **/
    protected function sort($a, $b)
    {
        if ( !isset( $a['filename'] ) or !isset( $b['filename'] ) )
            return 0;

        return strcasecmp( basename($a['filename']), basename($b['filename']) );
    }
}
