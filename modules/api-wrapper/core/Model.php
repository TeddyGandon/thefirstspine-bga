<?php

namespace thefirstspine\apiwrapper\core;

class Model {

    protected $originalAttributes = array();
    protected $currentAttributes = array();

    public function __construct($params = null)
    {
    }

    protected function setOriginalAttributes($attributes)
    {
        $this->originalAttributes = json_decode(json_encode($attributes), true);
        $this->currentAttributes = json_decode(json_encode($attributes), true);
    }

    public function attributes()
    {
        return $this->currentAttributes;
    }

    public function __set($name, $value)
    {
        if (!array_key_exists($name, $this->originalAttributes) && !empty($this->originalAttributes))
        {
            trigger_error("Property $name doesn't exists and cannot be set.", E_USER_ERROR);
            return;
        }

        $this->currentAttributes[$name] = $value;
    }

    public function __get($name)
    {
        if (!array_key_exists($name, $this->originalAttributes) && !empty($this->originalAttributes))
        {
            trigger_error("Property $name doesn't exists and cannot be set.", E_USER_ERROR);
            return null;
        }

        return $this->currentAttributes[$name];
    }

}
