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
    
    abstract protected function initialize();
    
    abstract public function filter();
    
}