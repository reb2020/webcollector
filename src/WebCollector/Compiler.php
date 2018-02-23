<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;
use WebCollector\Collection as Collection;
use WebCollector\Compilations\Css as Css;
use WebCollector\Compilations\Js as Js;

final class Compiler {
    
    private $File = null;
    private $Dir = null;
    private $Collections = [];
    private $LastFiles = [];
    
    public function __construct(String $Dir, String $File) {
        $this->File = $File;
        $this->Dir = $Dir;
        $this->loadLastFiles();
    }
    
    public function add($Name, Array $Data = []) {
        $this->Collections[$Name] = $Data;
    }
    
    protected function loadLastFiles(){
        $Load = [];
        if(file_exists($this->Dir . $this->File)){
            foreach (json_decode(file_get_contents($this->Dir . $this->File)) as $Data){
                if(isset($Data->files)){
                    $Load[$Data->name] = $Data->files;
                }
            }
        }
        $this->LastFiles = $Load;
    }
    
    public function compileCss($Collection) {
        $Css = new Css($Collection);
        $Css->execute();
        return $Css;
    }

    public function compileJs($Collection) {
        $Js = new Js($Collection);
        $Js->execute();
        return $Js;
    }
    
    public function getLastFiles(){
        return $this->LastFiles;
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