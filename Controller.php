<?php
namespace controllers;

use interfaces\IController;
use \Illuminate\Database\Eloquent\Model;

class Controller implements IController
{
    /**
     * Current model object
     *
     * @var Model
     */
    protected static $model = null;

    /**
     * Current controller object
     *
     * @var IController
     */
    protected static $controller = null;

    /**
     * Attributes array, must be the same as its model counterpart
     * 
     * @var array
     */
    protected $attributes = array();

    /**
     * @var array
     */
    protected $validation = array();

    /**
     * Custom variables which are not part of the model counterpart
     *
     * @var array
     */
    protected $variables = array();

    /**
     * Array with `attributes` and `variables` merged together.
     * This data can be used publicly 
     *
     * @var array
     */
    public $properties = array();

    /**
     * Constructor
     *
     * @param   array   $attributes
     */
    public final function __construct( array $attributes = null )
    {
        if( ! $attributes ) return;
        if( ! static::$model ) throw new \ErrorException('Define a `model` in your controller using `protected static $model`');

        // Fill controler on boot
        $this->fill( $attributes );
        $this->properties = array_merge( $this->attributes, $this->variables );
    }

    /**
     * Fill controller attributes, will also fill variables if you have set an callable inside the it
     *
     * @param   array       $attributes
     * 
     * @return  IController
     * 
     * @internal IController
     */
    protected final function fill( array $attributes ) : IController
    {
        array_walk( $attributes, fn($val, $key) => $this->attributes[$key] = $attributes[$key] );
        array_walk( $this->variables, fn($val, $key) => is_callable( $val ) ? $this->variables[$key] = call_user_func( array( $this, $val[1] ) ) : null );

        // Validate attributes
        $this->validate();
        
        return $this;
    }

    /**
     * Fill controller attributes
     *
     * @param   array       $attributes
     * 
     * @return  IController
     */
    public final function create( array $attributes ) : IController
    {
        array_walk( $attributes, fn($val, $key) => $this->attributes[$key] = $attributes[$key] );

        // Validate attributes
        $this->validate();

        return $this;
    }

    /**
     * Validate attributes using the regex given in $validation array
     *
     * @return  IController
     * 
     * @throws  \ErrorException
     */
    public final function validate() : IController
    {       
        $validationFailedArray = array_filter( $this->validation, fn( $key ) => ! preg_match( $this->validation[$key], $this->attributes[$key] ), ARRAY_FILTER_USE_KEY);

        if( $validationFailedArray ) throw new \ErrorException("\r\n\r\n" . str_replace('=', ' must be type of ', urldecode( http_build_query( $validationFailedArray, '', "\r\n" ) ) ) . "\r\n\r\n");

        return $this;
    }

    /**
     * Get attribute value
     *
     * @param   string      $key
     * 
     * @return  mixed|null
     */
    public final function get( string $key )
    {
        return isset( $this->attributes[$key] ) ? $this->attributes[$key] : null;
    }

    /**
     * Set attribute value
     *
     * @param   string $key
     * @param   mixed $value
     * 
     * @return  IController
     */
    public final function set( string $key, $value ) : IController
    {
        $this->attributes[$key] = $value;
        $this->properties[$key] = $value;

        // Validate new values
        $this->validate();

        return $this;
    }

    /**
     * Saves current attribute state to model
     *
     * @return IController
     */
    public final function save() : IController
    {
        return new static::$controller( static::$model::updateOrCreate( $this->attributes )->toArray() );
    }
    
    /**
     * Convert object to array
     *
     * @return array
     */
    public final function toArray() : array
    {
        return json_decode( json_encode( $this ), true );
    }

    /**
     * Convert object to json
     *
     * @return string
     */
    public final function toJson() : string
    {
        return json_encode( $this );
    }
}