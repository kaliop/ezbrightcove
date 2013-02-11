<?php
/**
 * File containing the klpBcDiContainereZTiein class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Sets up the Dependency Injector Container using settings from 
 * ezbrightcove.ini.
 */
class klpBcDiContainereZTiein extends klpDiContainer
{
    protected static $hasInitialized = false;

    public function __construct()
    {
        $ini = eZINI::instance( 'ezbrightcove.ini' );
        $iniSettings = $ini->variable( 'DependencyInjection', 'Settings' );

        parent::__construct( $iniSettings );
    }

    /**
     * Sets and returns a configured instance of klpDiContainer
     *
     * @param bool $force Force a new instance to be created
     * @return klpDiContainer
     */
    public static function getInstance( $force = false )
    {
        if ( !self::$hasInitialized || $force )
        {
            $class = __CLASS__;
            $dic = new $class();
            klpDiContainer::setInstance( $dic );

            self::$hasInitialized = true;
        }

        return klpDiContainer::getInstance();
    }
}

