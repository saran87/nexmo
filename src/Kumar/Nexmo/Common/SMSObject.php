<?php


namespace Kumar\Nexmo\Common;


abstract class SMSObject  implements \ArrayAccess{


    /**
     * The response's attributes.
     *
     * @var array
     */
    protected $attributes = array();


    public function __construct(array $attributes = array())
    {
        $this->fill($attributes);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws MassAssignmentException
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value)
        {
            $this->setAttribute($key, $value);
        }

        return $this;
    }
    /**
     * Set a given attribute on the Response.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        $key = $this->sanitizeKey($key);
        $this->attributes[$key] = $value;
    }


    /**
     * Get an attribute from the Response.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {

        if (array_key_exists($key, $this->attributes))
        {
            return $this->attributes[$key];
        }
    }


    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return ((isset($this->attributes[$key])  && ! \is_null($this->getAttributeValue($key))));
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * @param $key
     *
     * Sanitize array key to avoid '-'
     *
     * @return string
     */
    private function sanitizeKey($key)
    {
        $key_arr = \explode("-", $key);

        $key = $key_arr[0];
        unset($key_arr[0]);

        foreach($key_arr as $key_part){
            $key .= \ucfirst($key_part);
        }

        return  $key;
    }
}