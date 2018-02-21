<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;
use WebCollector\Collection as Collection;

class Compiler {
    
    private $File = null;
    private $Collections = [];
    
    public function __construct(String $File) {
        $this->File = $File;
    }
    
    public function add($Name, Array $Data = []) {
        $this->Collections[$Name] = $Data;
    }
    
    public function save() {
        $CollectionData = [];
        $Index = 0;
        foreach ($this->Collections as $Name => $Collection) {
            $CollectionData[$Index]['name'] = $Name;
            $CollectionData[$Index]['css'] = $Collection['css'];
            $CollectionData[$Index]['js'] = $Collection['js'];
            $Index++;
        }
        
        file_put_contents($this->File, json_encode($CollectionData, JSON_PRETTY_PRINT));
    }
    
}