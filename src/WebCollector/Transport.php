<?php

namespace WebCollector;

abstract class Transport {
    
    protected $Dir = null;
    protected $Files = [];
    protected $LastFiles = [];
    protected $Params = null;
    
    public function __construct($Params = null) {
        $this->Params = $Params;
    }
    
    public function initialize($Dir, $NewFiles = [], $LastFiles = []) {
        $this->Dir = $Dir;
        $this->Files = $NewFiles;
        $this->LastFiles = $LastFiles;
    }
    
    abstract public function send();

    abstract public function delete();
    
}