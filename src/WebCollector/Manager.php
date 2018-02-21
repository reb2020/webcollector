<?php

namespace WebCollector;

use WebCollector\Compiler;
use WebCollector\Config;
use WebCollector\Exception as CollectorException;

use MatthiasMullie\Minify;

class Manager {
    
    private $Config = null;
    private $Compiler = null;
    private $Collections = [];
    private $Dir = null;
    
    public function __construct($Dir, $Collection = null) {
        $this->Compiler = new Compiler("collections.lock");
        $this->Config = new Config("collections.json");
        $this->Dir = $Dir;
        
        if($Collection !== null){
            if(!$this->Config->has($Collection)){
                throw new CollectorException("Collector could not find collection called " . $Collection);
            }
            $this->Collections[] = $this->Config->getCollection($Collection);
        } else {
            $this->Collections = $this->Config->getCollections();
        }
        
        if(!count($this->Collections)){
            throw new CollectorException("Collector could not find any Collections.");
        }
        
        $this->init();
    }
    
    protected function init() {
        foreach ($this->Collections as $Collection){
            echo "Start \n";
            echo "Collection Name: " . $Collection->name . " \n";
            
            if(!is_dir($this->Dir . $Collection->import_dir)){
                throw new CollectorException("Collector not found import dir " . $Collection->import_dir);
            }
            
            echo "Scan Last Files: " . $this->Dir . $Collection->compiled_dir . "\n";
            $LastFiles = [];
            foreach (scandir($this->Dir . $Collection->compiled_dir) as $FileName){
                if(strpos($FileName, ".css") || strpos($FileName, ".js")){
                    $LastFiles[] = $FileName;
                    echo " -> " . $FileName . "\n";
                }
            }
            $Collection->last_files = $LastFiles;
            
            $Data = [
                        'css' => $this->css($Collection), 
                        'js' => $this->js($Collection)
            ];
            $this->Compiler->add($Collection->name, $Data);
        }
        $this->Compiler->save();
        $this->clear();
    }
    
    protected function validateFile($File) {
        if(!file_exists($File)){
            throw new CollectorException("Collection file '" . $File . "' was not found.");
        }
    }
    
    protected function css($Collection) {
        $Return = [];
        $Minify = [];
        
        echo "Compile CSS: \n";
        
        foreach ($Collection->css->getData() as $Data){
            $this->validateFile($this->Dir . $Data->file);
            $FileInfo = pathinfo($Data->file);
            
            echo " -> " . $Data->file . " \n";
            
            if($FileInfo["filename"] == "*"){
                
            } else if ($FileInfo["extension"] == "css") {
                $NewFileName = md5(time() . rand(10000, 99999)) . '.css';
                copy($this->Dir . $Data->file, $this->Dir . $Collection->compiled_dir . $NewFileName);
                
                if($Data->minify){
                    $Minify[] = $this->Dir . $Collection->compiled_dir . $NewFileName;
                } else {
                    $Return[] = $Collection->base_url . $NewFileName;
                }
            } else if ($FileInfo["extension"] == "less") {
                $Less = new \lessc();
                if($Data->import_dir){
                    $Less->addImportDir($this->Dir . $Data->import_dir);
                }
                $NewFileName = md5(time() . rand(10000, 99999)) . '.css';
                
                if($Data->minify){
                    $Minify[] = $this->Dir . $Collection->compiled_dir . $NewFileName;
                } else {
                    $Return[] = $Collection->base_url . $NewFileName;
                }
                
                $Less->compileFile($this->Dir . $Data->file, $this->Dir . $Collection->compiled_dir . $NewFileName);
            } else if ($FileInfo["extension"] == "scss") {
                $Scss = new \Leafo\ScssPhp\Compiler();
                if($Data->import_dir){
                    $Scss->setImportPaths($this->Dir . $Data->import_dir);
                }
                $NewFileName = md5(time() . rand(10000, 99999)) . '.css';
                
                if($Data->minify){
                    $Minify[] = $this->Dir . $Collection->compiled_dir . $NewFileName;
                } else {
                    $Return[] = $Collection->base_url . $NewFileName;
                }
                
                $Compiled = $Scss->compile(file_get_contents($this->Dir . $Data->file), $this->Dir . $Data->file);
               
                file_put_contents($this->Dir . $Collection->compiled_dir . $NewFileName, $Compiled);
            }
        }
        
        if(count($Minify)){
            $Return[] = $this->minifyCss($Collection, $Minify);
        }
        
        return $Return;
    }
    
    protected function minifyCss($Collection, $Files) {
        $Minifier = new Minify\CSS();
        
        foreach ($Files as $File) {
            $Minifier->add($File);
        }
        
        $NewFileName = md5(time() . rand(10000, 99999)) . '.css';
        
        $Minifier->minify($this->Dir . $Collection->compiled_dir . $NewFileName);
        
        return $Collection->base_url . $NewFileName;
    }

    protected function js($Collection) {
        $Return = [];
        $Minify = [];
        
        echo "Compile JS: \n";
        
        foreach ($Collection->js->getData() as $Data){
            $this->validateFile($this->Dir . $Data->file);
            $FileInfo = pathinfo($Data->file);
            
            echo " -> " . $Data->file . " \n";
            
            if($FileInfo["filename"] == "*"){
                
            } else if ($FileInfo["extension"] == "js") {
                $NewFileName = md5(time() . rand(10000, 99999)) . '.js';
                copy($this->Dir . $Data->file, $this->Dir . $Collection->compiled_dir . $NewFileName);
                
                if($Data->minify){
                    $Minify[] = $this->Dir . $Collection->compiled_dir . $NewFileName;
                } else {
                    $Return[] = $Collection->base_url . $NewFileName;
                }
            }
        }
        
        if(count($Minify)){
            $Return[] = $this->minifyJs($Collection, $Minify);
        }
        
        return $Return;
    }
    
    protected function minifyJs($Collection, $Files) {
        $Minifier = new Minify\JS();
        
        foreach ($Files as $File) {
            $Minifier->add($File);
        }
        
        $NewFileName = md5(time() . rand(10000, 99999)) . '.js';
        
        $Minifier->minify($this->Dir . $Collection->compiled_dir . $NewFileName);
        
        return $Collection->base_url . $NewFileName;
    }
    
    protected function clear() {
        foreach ($this->Collections as $Collection){
            echo "Delete Old Files: " . $this->Dir . $Collection->compiled_dir . "\n";
            foreach ($Collection->last_files as $File){
                echo " -> " . $File . " \n";
                if(file_exists($this->Dir . $Collection->compiled_dir . $File)){
                    unlink($this->Dir . $Collection->compiled_dir . $File);
                }
            }
        }
    }
    
    
}