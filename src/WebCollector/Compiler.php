<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;
use WebCollector\Collection as Collection;

class Compiler {
    
    private $File = null;
    private $Dir = null;
    private $Collections = [];
    
    public function __construct(String $Dir, String $File) {
        $this->File = $File;
        $this->Dir = $Dir;
    }
    
    public function add($Name, Array $Data = []) {
        $this->Collections[$Name] = $Data;
    }
    
    public function load(){
        $Load = [];
        if(file_exists($this->Dir . $this->File)){
            foreach (json_decode(file_get_contents($this->Dir . $this->File)) as $Data){
                if(isset($Data->files)){
                    $Load[$Data->name] = $Data->files;
                }
            }
        }
        return $Load;
    }
    
    public function save() {
        $CollectionData = [];
        $Index = 0;
        foreach ($this->Collections as $Name => $Collection) {
            $CollectionData[$Index]['name'] = $Name;
            $CollectionData[$Index]['css'] = $Collection['css'];
            $CollectionData[$Index]['js'] = $Collection['js'];
            $CollectionData[$Index]['files'] = $Collection['files'];
            $Index++;
        }
        
        file_put_contents($this->Dir . $this->File, json_encode($CollectionData, JSON_PRETTY_PRINT));
    }
    
}