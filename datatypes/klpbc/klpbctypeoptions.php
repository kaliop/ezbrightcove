<?php
/**
 * File containing the klpBcTypeOptions class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * klpBcTypeOptions handles all the options/configurations the user can make with the
 * klpBcType datatype.
 */
class klpBcTypeOptions
{
    /**
     * Holds the properties of this class.
     *
     * @var array( string=>mixed )
     */
    private $properties = array();

    /**
     * List of valid properties names for this class
     *
     * @var array( string )
     */
    private $validProperties = array();

    /**
     * List of requires properties names for this class
     *
     * @var array( string )
     */
    private $requiredProperties = array();

    /**
     * Creates a new instance of this class
     *
     * @param array|null $validProperties List of class properties
     * @param array|null $requiredProperties List of requiredProperties
     **/
    public function __construct( $validProperties, $requiredProperties = array() )
    {
        $this->validProperties = $validProperties;
        $this->requiredProperties = $requiredProperties;
    }

    /**
     * Serializes the data
     *
     * @return string Serialized data
     */
    public function toJson()
    {
        return json_encode( $this->properties );
    }

    /**
     * Unserializes the object using $data
     *
     * @param string $data Serialized data
     */
    public function fromJson( $data )
    {
        $this->properties = json_decode( $data, true );
    }

    /**
     * Serialises all properties to XML
     *
     * @param DOMElement $rootNode
     **/
    public function toXml( $rootNode )
    {
        $dom = $rootNode->ownerDocument;
        foreach( $this->properties as $key => $value )
        {
            $node = $dom->createElement( $key );
            $node->appendChild( $dom->createTextNode( $value ) );

            $rootNode->appendChild( $node );
        }
    }

    /**
     * Loads properties from XML
     *
     * @param DOMElement $rootNode DOM element to read properties from
     **/
    public function fromXml( $rootNode )
    {
        foreach( $this->validProperties as $option )
        {
            $node = $rootNode->getElementsByTagName( $option )->item( 0 );
            if ( $node )
                $this->{$option} = $node->textContent;
        }

        return $this;
    }

    /**
     * Returns list of all properties for this class
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->validProperties;
    }

    public function getRequiredProperties()
    {
        return $this->requiredProperties;
    }

    /**
     * Wraps around $this->__get() so we can use this send instances of this
     * class directly to eZ Publish templates.
     *
     * @see self::__get()
     */
    public function attribute( $name )
    {
        return $this->__get( $name );
    }

    /**
     * Wraps around $this->__set() so we can use this send instances of this
     * class directly to eZ Publish templates.
     *
     * @see self::__set()
     */
    public function setAttribute( $name, $value )
    {
        return $this->__set( $name, $value );
    }

    /**
     * Wraps around $this->__isset() so we can use this send instances of this
     * class directly to eZ Publish templates.
     *
     * @see self::__set()
     */
    public function hasAttribute( $name )
    {
        return $this->__isset( $name );
    }

    /**
     * Returns the value of the property $name.
     *
     * @throws ezcBasePropertyNotFoundException if the property does not exist.
     * @param string $name
     * @ignore
     */
    public function __get( $name )
    {
        if ( $name == "requiredProperties" )
            return $this->requiredProperties;

        if ( !in_array( $name, $this->validProperties ) )
            throw new ezcBasePropertyNotFoundException( $name );

        return $this->properties[$name];
    }

    /**
     * Sets the property $name to $value.
     *
     * @throws ezcBasePropertyNotFoundException if the property does not exist.
     * @param string $name
     * @param mixed $value
     * @ignore
     */
    public function __set( $name, $value )
    {
        if ( $name == "requiredProperties" )
            return $this->requiredProperties = $value;

        if ( !in_array( $name, $this->validProperties ) )
            throw new ezcBasePropertyNotFoundException( $name );

        $this->properties[$name] = $value;
    }

    /**
     * Returns true if the property $name is set, otherwise false.
     *
     * @param string $name
     * @return bool
     * @ignore
     */
    public function __isset( $name )
    {
        if ( !in_array( $name, $this->validProperties ) )
            throw new ezcBasePropertyNotFoundException( $name );

        return isset( $this->properties[$name] );
    }
}
