<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;

abstract class Filter {
    
    protected $RootDir = null;

    protected $CompileDir = null;

    protected $Params = null;

    protected $Content = null;

    
    public function __construct($RootDir, $CompileDir, $Params = null) {
        $this->RootDir = $RootDir;
        $this->CompileDir = $CompileDir;
        $this->Params = $Params;
        $this->initialize();
    }
    
    public function setContent($Content){
        $this->Content = $Content;
    }
    
    
    private function _toArray(&$array, $object){
        if(is_object($object)){
            $data = $object;
            foreach ($data AS $name => $value){
                if(!is_object($value)){
                    $array[$name] = $value;
                } else {
                    $this->_toArray($array[$name], $value);
                }
            }
        }
    }
    
    protected function paramsToArray(){
        $Return = [];
        if(isset($this->Params) && is_array($this->Params)){
            $this->_toArray($Return, $this->Params);
        }
        return $Return;
    }
    
    abstract protected function initialize();
    
    abstract public function filter();
    
}