<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;

abstract class Resource {
    
    protected $RootDir = null;
    
    protected $CompileDir = null;
    
    protected $File = null;
    
    protected $Filters = [];
    
    public function __construct($RootDir, $CompileDir, $File, $Filters = []) {
        $this->RootDir = $RootDir;
        $this->CompileDir = $CompileDir;
        $this->File = $File;
        $this->initialize($Filters);
    }
    
    protected function initialize($Filters = []) {
        foreach ($Filters as $Filter){
            $ClassName = $Filter['class'];
            $this->Filters[] = new $ClassName($this->RootDir, $this->CompileDir, $Filter['params']);
        }
    }
    
    public function getContent(){
        return file_get_contents($this->RootDir . $this->File);
    }

    public function getFile(){
        return $this->File;
    }
    
    public function filter() {
        $Content = $this->getContent();
        foreach ($this->Filters as $Filter){
            $Filter->setContent($Content);
            $Content = $Filter->filter();
        }
        return $Content;
    }
    
}