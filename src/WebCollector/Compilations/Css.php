<?php

namespace WebCollector\Compilations;

use WebCollector\Exception as CollectorException;

use MatthiasMullie\Minify;

class Css {
    
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
        foreach ($this->Collection->css->getData() as $Data){
            $this->validateFile($this->Collection->dir . $Data->file);
            $FileInfo = pathinfo($Data->file);
            
            $this->Files[] = $Data->file;
            
            if ($FileInfo["extension"] == "css") {
                $NewFileName = md5(time() . rand(10000, 99999)) . '.css';
                copy($this->Collection->dir . $Data->file, $this->Collection->dir . $this->Collection->compiled_dir . $NewFileName);
                
                if($Data->minify){
                    $this->Minify[] = $this->Collection->dir . $this->Collection->compiled_dir . $NewFileName;
                    $this->Collection->tmp_files[] = $NewFileName;
                } else {
                    $this->Return[] = $this->Collection->base_url . $NewFileName;
                    $this->Collection->new_files[] = $NewFileName;
                }
            } else if ($FileInfo["extension"] == "less") {
                $Less = new \lessc();
                if($Data->import_dir){
                    $Less->addImportDir($this->Collection->dir . $Data->import_dir);
                }
                $NewFileName = md5(time() . rand(10000, 99999)) . '.css';
                
                if($Data->minify){
                    $this->Minify[] = $this->Collection->dir . $this->Collection->compiled_dir . $NewFileName;
                    $this->Collection->tmp_files[] = $NewFileName;
                } else {
                    $this->Return[] = $this->Collection->base_url . $NewFileName;
                    $this->Collection->new_files[] = $NewFileName;
                }
                
                $Less->compileFile($this->Collection->dir . $Data->file, $this->Collection->dir . $this->Collection->compiled_dir . $NewFileName);
            } else if ($FileInfo["extension"] == "scss") {
                $Scss = new \Leafo\ScssPhp\Compiler();
                if($Data->import_dir){
                    $Scss->setImportPaths($this->Collection->dir . $Data->import_dir);
                }
                $NewFileName = md5(time() . rand(10000, 99999)) . '.css';
                
                if($Data->minify){
                    $this->Minify[] = $this->Collection->dir . $this->Collection->compiled_dir . $NewFileName;
                    $this->Collection->tmp_files[] = $NewFileName;
                } else {
                    $this->Return[] = $this->Collection->base_url . $NewFileName;
                    $this->Collection->new_files[] = $NewFileName;
                }
                
                $Compiled = $Scss->compile(file_get_contents($this->Collection->dir . $Data->file), $this->Collection->dir . $Data->file);
                
                file_put_contents($this->Collection->dir . $this->Collection->compiled_dir . $NewFileName, $Compiled);
            }
        }
    }
    
    protected function minify() {
        if(count($this->Minify)){
            $Minifier = new Minify\CSS();
            
            foreach ($this->Minify as $File) {
                $Minifier->add($File);
            }
            
            $NewFileName = md5(time() . rand(10000, 99999)) . '.css';
            
            $Minifier->minify($this->Collection->dir . $this->Collection->compiled_dir . $NewFileName);
            
            $this->Return[] = $this->Collection->base_url . $NewFileName;
            $this->Collection->new_files[] = $NewFileName;
        }
    }
    
}