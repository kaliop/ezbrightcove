<?php
/**
 * File containing the klpBcTypeClassInputValidator class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Helper class for validating http input for class attributes
 */
class klpBcTypeClassInputValidator
{
    /**
     * Creates a new instance of this class
     *
     * @param eZHTTPTool $http
     * @param string $base Base string for http input
     * @param string $dataTypeString Datatype identifier
     * @param int $classAttributeId Content class attribute id
     */
    public function __construct( $http, $base, $dataTypeString, $classAttributeId )
    {
        $this->http = $http;
        $this->base = $base;
        $this->dataTypeString = $dataTypeString;
        $this->classAttributeId = $classAttributeId;
    }

    /**
     * Returns true if all requires options validates succesfully
     *
     * @param array $requiredOptions Array of requires class input option
     * @return bool
     */
    public function isValid( $requiredOptions )
    {
        foreach( $requiredOptions as $option)
        {
            $postVar = $this->postVarName( $option );
            if ( !$this->http->hasPostVariable( $postVar ) )
                return false;

            $value = $this->http->postVariable( $postVar );
            if ( empty( $value ) )
                return false;
        }

        return true;
    }

    /**
     * Returns the HTTP POST variable name for an option
     *
     * @param string $option Name of the option
     * @return string The complete post variable name
     */
    protected function postVarName( $option )
    {
        return "{$this->base}_{$this->dataTypeString}_{$option}_{$this->classAttributeId}";
    }
}

