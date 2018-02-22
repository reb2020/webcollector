<?php

namespace WebCollector;

use WebCollector\Css as Css;
use WebCollector\Js as Js;

use WebCollector\Exception as CollectorException;

class Collection {
    
    public $name = null;
    
    public $base_url = null;

    public $dir = null;

    public $compiled_dir = null;

    public $transport = null;

    public $css = null;

    public $js = null;
    
    public $tmp_files = [];

    public $old_files = [];

    public $new_files = [];
    
    
    public function __construct($Data = []) {
        $this->setData($Data);
    }
    
    protected function initTransport($Data){
        $ClassName = $Data->class;
        if(!class_exists($ClassName)){
            throw new CollectorException("Collector could not find transport class " . $ClassName);
        } 
        
        $Parameters = null;
        if(isset($Data->parameters)){
            $Parameters = $Data->parameters;
        }
        
        return new $ClassName($Parameters);
    }
   
    public function setData($Data) {
        $Css = [];
        $Js = [];
        foreach ($Data AS $Name => $Value){
            if($Name == 'css'){
                $Css = $Value;
            } else if($Name == 'js'){
                $Js = $Value;
            } else if($Name == 'transport'){
                $this->{$Name} = $this->initTransport($Value);
            } else {
                $this->{$Name} = $Value;
            }
        }
        
        if(!$this->compiled_dir || !is_dir($this->dir . $this->compiled_dir)){
            throw new CollectorException("Collector not found compiled dir " . $this->compiled_dir);
        }
        
        $this->css = new Css($this->dir, $Css);
        $this->js = new Js($this->dir, $Js);
    }
    
    public function getData() {
        return $this->_data;
    }
    
    public function send() {
        if($this->transport) {
            $this->transport->initialize($this->dir, $this->new_files, $this->old_files);
            $this->transport->send();
        }
    }
    
    public function clear() {
        foreach ($this->tmp_files as $File){
            if(file_exists($this->dir . $this->compiled_dir . $File)){
                unlink($this->dir . $this->compiled_dir . $File);
            }
        }
        $this->tmp_files = [];
        
        if($this->transport) {
            $this->transport->initialize($this->dir, $this->new_files, $this->old_files);
            $this->transport->delete();
        }
        
        foreach ($this->old_files as $File){
            if(file_exists($this->dir . $this->compiled_dir . $File)){
                unlink($this->dir . $this->compiled_dir . $File);
            }
        }
        $this->old_files = [];
    }
    
}