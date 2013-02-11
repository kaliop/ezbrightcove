<?php
/**
 * File containing the klpBcServerFileConfig class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Struct like class for holding a few configuration variables related to the 
 * 'upload from server' video input type.
 **/
class klpBcServerFileConfig
{
    /**
     * Creates a new instance of this class
     *
     * @param $ini Optional instance of eZINI
     **/
    public function __construct( $ini = null )
    {
        if ( !$ini )
            $ini = eZINI::instance( 'ezbrightcove.ini' );

        $this->ini = $ini;
    }

    /**
     * Returns the value of the property $name.
     *
     * @param string $name
     * @ignore
     */
    public function __get( $name )
    {
        switch( $name )
        {
            case 'isEnabled':
                return $this->isEnabled();
            case 'rootDirectory':
                return $this->rootDirectory();

        }
    }

     /**
      * Returns if the feature is enabled
      *
      * @return bool
      **/
    protected function isEnabled()
    {
        $dir = $this->rootDirectory();

        return !empty( $dir );
    }

    /**
     * Returns the root directory for videos
     *
     * @return string Root directory
     **/
    protected function rootDirectory()
    {
        $rootDir = $this->ini->variable( 'ServerFileBrowse', 'RootDirectory' );

        return $rootDir; 
    }
}
