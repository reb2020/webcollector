<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;
use WebCollector\Collection as Collection;

final class Config {
    
    private $File = null;
    private $Dir = null;
    private $Collections = [];
    
    public function __construct(String $Dir, String $File) {
        $this->File = $File;
        $this->Dir = $Dir;
        $this->validateFile();
        $this->load();
    }
    
    protected function validateFile(){
        if(!file_exists($this->Dir . $this->File)){
            throw new CollectorException("Collection file was not found.");
        }
    }
    
    protected function load(){
        foreach (json_decode(file_get_contents($this->Dir . $this->File)) as $Data){
            if(!isset($Data->root_dir)){
                $Data->root_dir = $this->Dir;
            }
            $Data->compiled_dir = str_replace('~', $Data->root_dir, $Data->compiled_dir);
            $Data->compiled_dir = str_replace('//', '/', $Data->compiled_dir);
            $this->Collections[$Data->name] = new Collection($Data);
        }
    }
    
    public function has($Name){
        return array_key_exists($Name, $this->Collections) ? true : false;
    }
    
    public function getCollection($Name){
        if($this->has($Name)){
            return $this->Collections[$Name]; 
        }
        return null; 
    }
    
    public function getCollections(){
        return $this->Collections;
    }
    
}