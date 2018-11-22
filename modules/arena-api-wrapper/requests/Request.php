<?php

namespace arenaApiWrapper\requests;

/**
 * Class Request
 * Represents a base request in the Arena API
 * @package arenaApiWrapper\requests
 */
abstract class Request
{

    /**
     * The request attributes
     * @var array
     */
    private $attributes = array();

    /**
     * The request that will be called by the request.
     * @return string
     */
    protected static function getMethod()
    {
        return '';
    }

    /**
     * The attributes to set in the request.
     * @return array
     */
    protected static function getAttributes()
    {
        return [];
    }

    /**
     * Convert this request to an array, ready to be sent to the API.
     * @return array
     */
    public function toArray()
    {
        $parameters = array();

        foreach ($this->getAttributes() as $attribute)
        {
            if (is_scalar($this->{$attribute}))
            {
                $parameters[$attribute] = $this->{$attribute};
            }
            else
            {
                $parameters[$attribute] = json_decode(
                    json_encode($this->{$attribute}),
                    true
                );
            }
        }

        return array(
            'method' => static::getMethod(),
            'parameters' => $parameters
        );
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (in_array($name, static::getAttributes()))
        {
            return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if (in_array($name, static::getAttributes()))
        {
            $this->attributes[$name] = $value;
        }
    }

}