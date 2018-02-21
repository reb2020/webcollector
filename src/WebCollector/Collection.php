<?php

namespace WebCollector;

class Collection {
    
    protected $_data = [];
    
    public function __construct($Name = null, $Data = []) {
        if($Name) {
            $this->name = $Name;
        }
        $this->setData($Data);
    }
    
    public function __get($Name){
        return $this->get($Name);
    }
    
    public function __set($Name, $Value){
        return $this->set($Name, $Value);
    }
    
    public function __isset($Name) {
        return $this->has($Name);
    }
    
    public function get($Name, $defaultValue = null) {
        return $this->has($Name) ? $this->_data[$Name] : $defaultValue;
    }
    
    public function set($Name, $Value) {
        $this->_data[$Name] = $Value;
    }
    
    public function has($Name) {
        return array_key_exists($Name, $this->_data) ? true : false;
    }
    
    public function setData($Data) {
        foreach ($Data AS $Name => $Value){
            if(is_array($Value) || is_object($Value)){
                $this->{$Name} = new Collection(null, $Value);
            } else {
                $this->{$Name} = $Value;
            }
        }
    }
    
    public function getData() {
        return $this->_data;
    }
    
}