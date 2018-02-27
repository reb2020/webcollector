<?php

namespace WebCollector;

use WebCollector\Copy as Copy;
use WebCollector\Bundle as Bundle;
use WebCollector\Exception as CollectorException;

class Collection {
    
    public $name = null;
    
    public $base_url = null;

    public $root_dir = null;

    public $compiled_dir = null;

    public $transport = null;
    
    public $filters = [];

    public $css = [];

    public $js = [];

    public $copy = null;


    public $last_files = [];

    public $files = [];
    
    
    public function __construct($Data = []) {
        $this->initialize($Data);
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

    protected function initFilters($Data){
        foreach ($Data as $Filter){
            if(!isset($Filter->name)){
                throw new CollectorException("Filter name is required.");
            }
            $ClassName = $Filter->class;
            if(!class_exists($ClassName)){
                throw new CollectorException("Collector could not find filter class " . $ClassName);
            } 
            $this->filters[$Filter->name] = $ClassName;
        }
    }
    
    protected function initCss($Css, $DataBundle) {
        foreach ($Css as $CssBundle){
            
            $DataBundlePut = [];
            foreach ($CssBundle as $Name => $Value){
                $DataBundlePut[$Name] = $Value;
            }
            
            $this->css[] = new Bundle('css', array_merge($DataBundlePut, $DataBundle));
        }
    }

    protected function initJs($Js, $DataBundle) {
        foreach ($Js as $JsBundle){
            
            $DataBundlePut = [];
            foreach ($JsBundle as $Name => $Value){
                $DataBundlePut[$Name] = $Value;
            }
            
            $this->js[] = new Bundle('js', array_merge($DataBundlePut, $DataBundle));
        }
    }

    protected function initCopy($Copy) {
        $this->copy = new Copy(
            [
                'root_dir' => $this->root_dir,
                'compiled_dir' => $this->compiled_dir,
                'filters' => $this->filters,
                'source' => $Copy
            ]
        );
    }
   
    protected function initialize($Data) {
        $Css = [];
        $Js = [];
        $Copy = [];
        foreach ($Data AS $Name => $Value){
            if($Name == 'css'){
                $Css = $Value;
            } else if($Name == 'js'){
                $Js = $Value;
            } else if($Name == 'copy'){
                $Copy = $Value;
            } else if($Name == 'filters'){
                $this->{$Name} = $this->initFilters($Value);
            } else if($Name == 'transport'){
                $this->{$Name} = $this->initTransport($Value);
            } else {
                $this->{$Name} = $Value;
            }
        }
        
        if(!$this->root_dir || !is_dir($this->root_dir)){
            throw new CollectorException("Collector not found root dir " . $this->root_dir);
        }
        
        if(!$this->compiled_dir || !is_dir($this->compiled_dir)){
            throw new CollectorException("Collector not found compiled dir " . $this->compiled_dir);
        }
        
        $DataBundle = [
            'root_dir' => $this->root_dir,
            'compiled_dir' => $this->compiled_dir,
            'filters' => $this->filters
        ];
        
        $this->initCss($Css, $DataBundle);
        $this->initJs($Js, $DataBundle);
        $this->initCopy($Copy);
    }
    
    public function collectCss() {
        $Files = [];
        foreach ($this->css as $Bundle) {
            $Collect = $Bundle->collect();
            $Files[] = $this->base_url . $Collect["file"] . $Collect["version"];
            $this->files[] = $Collect["file"];
        }
        return $Files;
    }
    
    public function collectJs() {
        $Files = [];
        foreach ($this->js as $Bundle) {
            $Collect = $Bundle->collect();
            $Files[] = $this->base_url . $Collect["file"] . $Collect["version"];
            $this->files[] = $Collect["file"];
        }
        return $Files;
    }
    
    public function copy() {
        $Files = [];
        foreach ($this->copy->execute() as $FileName) {
            $this->files[] = $FileName;
            $Files[] = $FileName;
        }
        return $Files;
    }
    
    
    public function send() {
        if($this->transport) {
            $this->transport->initialize($this->compiled_dir, $this->files, $this->last_files);
            $this->transport->send();
        }
    }
    
    public function clear() {
        
        if($this->transport) {
            $this->transport->initialize($this->compiled_dir, $this->files, $this->last_files);
            $this->transport->delete();
        }
        
        foreach ($this->last_files as $File){
            if(file_exists($this->compiled_dir . $File) && !in_array($File, $this->files)){
                unlink($this->compiled_dir . $File);
            }
        }
        
        $this->last_files = [];
    }
    
}