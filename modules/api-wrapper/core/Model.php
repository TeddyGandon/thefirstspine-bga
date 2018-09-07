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
        $this->originalAttributes = is_null($this->originalAttributes) ? array() : $this->originalAttributes;
        $this->currentAttributes = json_decode(json_encode($attributes), true);
        $this->currentAttributes = is_null($this->currentAttributes) ? array() : $this->currentAttributes;
    }

    public function attributes()
    {
        return $this->currentAttributes;
    }

    public function __set($name, $value)
    {
        $this->currentAttributes[$name] = $value;
    }

    public function __get($name)
    {
        return isset($this->currentAttributes[$name]) ? $this->currentAttributes[$name] : null;
    }

}
