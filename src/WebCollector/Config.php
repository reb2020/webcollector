<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;
use WebCollector\Collection as Collection;

class Config {
    
    private $File = null;
    private $Collections = [];
    
    public function __construct(String $File) {
        $this->File = $File;
        $this->validateFile();
        $this->load();
    }
    
    protected function validateFile(){
        if(!file_exists($this->File)){
            throw new CollectorException("Collection file was not found.");
        }
    }
    
    public function load(){
        foreach (json_decode(file_get_contents($this->File)) as $Data){
            $this->Collections[$Data->name] = new Collection($Data->name, $Data);
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