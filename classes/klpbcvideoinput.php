<?php
/**
 * File containing the klpBcVideoInput class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Provides a way to manage the list of video input types
 **/
class klpBcVideoInput
{
    /**
     * Holds the input types for this class.
     *
     * @var array( string identifier => mixed )
     */
    private $inputTypes = array();

    /**
     * Holds all available input types
     *
     * @var array( string identifier => mixed )
     **/
    private $availableInputTypes = array();

    /**
     * Registers a new video input handler
     *
     * @param object $input Object that conforms to the klpBcVideoInputType interface
     * @return object Self is return
     */
    public function registerInputType( $identifier, $input )
    {
        $this->inputTypes[$identifier] = $input;

        return $this;
    }

    /**
     * Marks an input type as available
     *
     * The input type must be registered first before calling this method.
     *
     * @param string Input identifier
     **/
    public function makeAvailable( $identifier )
    {
        $this->availableInputTypes[$identifier] = $this->getInput( $identifier );
    }

    /**
     * Marks an input type as unavailable
     *
     * An unavailable input type will still be available via inputTypes().
     * The input type must be registered first before calling this method.
     *
     * @param string Input identifier
     **/
    public function makeUnavailable( $identifier )
    {
        unset( $this->availableInputTypes[$identifier] );
    }

    /**
     * Returns list of all registered inputs
     *
     * @return array (string identifier => mixed)
     */
    public function inputTypes()
    {
        return $this->inputTypes;
    }

    /**
     * Returns list of all available input types
     *
     * @return array (string identifier => mixed)
     */
    public function getAvailable()
    {
        return $this->availableInputTypes;
    }

    /**
     * Get an input by the identifier
     *
     * @param string Input identifier
     * @return object
     */
    public function getInput( $input )
    {
        return $this->inputTypes[$input];
    }
}
