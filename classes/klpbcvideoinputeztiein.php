<?php
/**
 * File containing the klpBcVideoInputeZTiein class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Brightcove Video Input handler that knows how to register input 
 * handlers from ezbrightcove.ini
 */
class klpBcVideoInputeZTiein extends klpBcVideoInput
{
    public function __construct()
    {
        $ini = eZINI::instance( 'ezbrightcove.ini' );
        $this->registerInputTypesFromIni( $ini, 'VideoInputTypes', 'Types' );
        $this->registerAvailableInputs( $ini, 'VideoInputTypes', 'AvailableTypes' );
    }

    /**
     * Register and instantiates all video input types from an ini file
     *
     * @param eZINI $ini Instance of eZINI with the list of input types
     * @param string $variable Name of ini variable to read from
     * @param string $variable Name of ini section to read from
     */
    protected function registerInputTypesFromIni( $ini, $variable, $section )
    {
        $inputTypes = $ini->variable( $variable, $section );
        foreach( $inputTypes as $identifier => $inputType )
        {
            if ( class_exists( $inputType ) )
            {
                $instance = new $inputType( $identifier );
                $this->registerInputType( $identifier, $instance );
            }
            else
            {
                eZDebug::writeError(
                    "Brightcove video input type {$inputType} does not exists",
                    __METHOD__
                );
            }
        }
    }

    /**
     * Marks all available input types in ini file as available in the input
     * type manager.
     *
     * @param eZINI $ini Instance of eZINI with the list of available input types
     * @param string $variable Name of ini variable to read from
     * @param string $variable Name of ini section to read from
     **/
    protected function registerAvailableInputs( $ini, $variable, $section )
    {
        $inputTypes = $ini->variable( $variable, $section );
        foreach( $inputTypes as $identifier )
        {
            $this->makeAvailable( $identifier );
        }
    }
}

