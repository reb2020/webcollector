<?php

namespace WebCollector\Compilations;

use WebCollector\Exception as CollectorException;

use MatthiasMullie\Minify;

class Js {
    
    protected $Collection;
    protected $Files = [];
    protected $Minify = [];
    protected $Return = [];
    
    public function __construct($Collection) {
        $this->Collection = $Collection;
    }
    
    public function execute(){
        $this->compile();
        $this->minify();
    }
    
    public function files() {
        return $this->Files;
    }
    
    public function newFiles() {
        return $this->Return;
    }
    
    protected function validateFile($File) {
        if(!file_exists($File)){
            throw new CollectorException("Collection file '" . $File . "' was not found.");
        }
    }
    
    protected function compile() {
        foreach ($this->Collection->js->getData() as $Data){
            $this->validateFile($this->Collection->dir . $Data->file);
            $FileInfo = pathinfo($Data->file);
            
            echo " -> " . $Data->file . " \n";
            
            if ($FileInfo["extension"] == "js") {
                $NewFileName = md5(time() . rand(10000, 99999)) . '.js';
                copy($this->Collection->dir . $Data->file, $this->Collection->dir . $this->Collection->compiled_dir . $NewFileName);
                
                if($Data->minify){
                    $this->Minify[] = $this->Collection->dir . $this->Collection->compiled_dir . $NewFileName;
                    $this->Collection->tmp_files[] = $NewFileName;
                } else {
                    $this->Return[] = $this->Collection->base_url . $NewFileName;
                    $this->Collection->new_files[] = $NewFileName;
                }
            }
        }
    }
    
    protected function minify() {
        if(count($this->Minify)){
            $Minifier = new Minify\JS();
            
            foreach ($Files as $File) {
                $Minifier->add($File);
            }
            
            $NewFileName = md5(time() . rand(10000, 99999)) . '.js';
            
            $Minifier->minify($this->Collection->dir . $this->Collection->compiled_dir . $NewFileName);
            
            $this->Return[] = $this->Collection->base_url . $NewFileName;
            $this->Collection->new_files[] = $NewFileName;
        }
    }
    
}