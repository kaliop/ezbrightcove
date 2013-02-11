<?php
/**
 * File containing the klpDiContainer class
 *
 * @copyright Copyright (C) 1999-2013 by Kaliop. All rights reserved. http://www.kaliop.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 */

/**
 * Brightcove dependency injection container
 */
class klpDiContainer
{
    /**
     * Holds all the configuration parameters required to initalizse all the
     * dependecy services/objects.
     *
     * @var array( string=>mixed )
     */
    public $parameters = array();

    /**
     * Holds any shared instances
     *
     * @var array( string=>mixed )
     */
    static protected $shared = array();

    /**
     * Creates a new instance of this class
     *
     * @param array $parameters
     */
    public function __construct( $parameters )
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns a shared instance of this class
     *
     * Note that a shared instance must be set before this method can be called
     * as this will not create a new instance if no instance exists.
     *
     * @return klpDiContainer
     */
    public static function getInstance()
    {
        return self::$shared['container.instance'];
    }

    /**
     * Sets an instance as the shared instance of this class
     *
     * @param klpDiContainer $instance
     * @return klpDiContainer
     */
    public static function setInstance( $instance )
    {
        self::$shared = array();
        return self::$shared['container.instance'] = $instance;
    }

    /**
     * Returns a datatype class option container instance
     *
     * @params mixed variable All arguments will be passed to the constructor
     * @return object
     */
    public function getTypeOptions( /* ... */ )
    {
        $args = func_get_args();
        $class = $this->parameters['typeoptions.class'];

        $reflectionClass = new ReflectionClass( $class );
        return $reflectionClass->newInstanceArgs( $args );
    }

    /**
     * Returns a datetype class option input validator instance
     *
     * @params mixed variable All arguments will be passed to the constructor
     * @return object
     */
    public function getTypeClassInputValidator( /* ... */ )
    {
        $args = func_get_args();
        $class = $this->parameters['typeclassinputvalidator.class'];

        $reflectionClass = new ReflectionClass( $class );
        return $reflectionClass->newInstanceArgs( $args );
    }

    /**
     * Returns a Brightcove Video Input instance
     *
     * @return object
     */
    public function getVideoInput()
    {
        $class = $this->parameters['videoinput.class'];
        return new $class();
    }

    /**
     * Returns a Brightcove Video instance
     *
     * @return object
     */
    public function getVideo()
    {
        $class = $this->parameters['video.class'];
        return new $class(false);
    }

    /**
     * Returns a Brightcove Video Meta instance
     *
     * @return object
     */
    public function getVideoMeta()
    {
        $class = $this->parameters['videometa.class'];
        return new $class(false);
    }

    /**
     * Returns a Brightcove Queue instance
     *
     * @return object
     */
    public function getQueue()
    {
        if ( isset( self::$shared['queue.instance'] ) )
            return self::$shared['queue.instance'];

        $class = $this->parameters['queue.class'];
        $instance = new $class( $this );

        return self::$shared['queue.instance'] = $instance;
    }

    /**
     * Returns a Brightcove API instance
     *
     * @return object
     */
    public function getBcApi()
    {
        $class = $this->parameters['bcapi.class'];
        return new $class();
    }

    /**
     * Returns a Internal Brightcove API instance
     *
     * @return object
     */
    public function getInternalBcApi()
    {
        $class = $this->parameters['internalbcapi.class'];
        return new $class();
    }

    /**
     * Returns a File Browser instance
     *
     * @return object
     */
    public function getFileBrowser()
    {
        $class = $this->parameters['filebrowser.class'];
        return new $class();
    }

    /**
     * Returns a File Information Formatter instance
     *
     * @return object
     */
    public function getFileInfoFormatter()
    {
        $args = func_get_args();
        $class = $this->parameters['fileinfoformatter.class'];
        $reflectionClass = new ReflectionClass( $class );

        return $reflectionClass->newInstanceArgs( $args );
    }

    /**
     * Returns a Config object for the Server File input type
     *
     * @return object
     */
    public function getServerFileConfig()
    {
        $class = $this->parameters['serverfileconfig.class'];
        return new $class();
    }

    /**
     * Returns the value of the property $name.
     *
     * @param string $name
     * @ignore
     */
    public function __get( $name )
    {
        if ( $name == 'parameters' )
            return $this->parameters;

        if ( strpos( $name, ".instance" ) !== false )
            return self::$shared[$name];

        return $this->parameters[$name];
    }

    /**
     * Sets the property $name to $value.
     *
     * @param string $name
     * @param mixed $value
     * @ignore
     */
    public function __set( $name, $value )
    {
        if ( $name == 'parameters' )
        {
            $this->parameters = $value;
            return;
        }

        if ( strpos( $name, ".instance" ) !== false )
        {
            self::$shared[$name] = $value;
            return;
        }

        $this->parameters[$name] = $value;
    }
}

