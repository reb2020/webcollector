<?php

namespace WebCollector;

abstract class Transport {
    
    protected $Dir = null;
    protected $NewFiles = [];
    protected $OldFiles = [];
    protected $Params = null;
    
    public function __construct($Params = null) {
        $this->Params = $Params;
    }
    
    public function initialize($Dir, $NewFiles = [], $OldFiles = []) {
        $this->Dir = $Dir;
        $this->NewFiles = $NewFiles;
        $this->OldFiles = $OldFiles;
    }
    
    abstract public function send();

    abstract public function delete();
    
}