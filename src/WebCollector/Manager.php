<?php

namespace WebCollector;

use WebCollector\Compiler;
use WebCollector\Config;
use WebCollector\Exception as CollectorException;
use WebCollector\Collection as Collection;

class Manager {
    
    private $Config = null;
    private $Compiler = null;
    private $Collections = [];
    private $Dir = null;
    
    public function __construct($Dir, $Collection = null) {
        $this->Config = new Config($Dir, "collections.json");
        $this->Compiler = new Compiler($Dir, "collections.lock");
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
            
            $Collection->last_files = $this->Compiler->getLastFiles($Collection->name);
            
            echo "Scan Last Files: " . $Collection->root_dir . $Collection->compiled_dir . "\n";
            foreach ($Collection->last_files as $FileName){
                echo " -> " . $FileName . "\n";
            }
            
            echo "Compile CSS: \n";
            $Css = $Collection->collectCss();
            foreach ($Css as $FileName){
                echo " -> " . $FileName . " \n";
            }

            echo "Compile JS: \n";
            $Js = $Collection->collectJs();
            foreach ($Js as $FileName){
                echo " -> " . $FileName . " \n";
            }

            echo "Copy: \n";
            foreach ($Collection->copy() as $FileName){
                echo " -> " . $FileName . " \n";
            }
            
            $Data = [
                'css' => $Css, 
                'js' => $Js,
                'files' => $Collection->files
            ];
            
            $Collection->send();
            $this->Compiler->add($Collection->name, $Data);
        }
        
        $this->Compiler->save();
        $this->clear();
    }
    
    protected function clear() {
        foreach ($this->Collections as $Collection){
            $Collection->clear();
        }
    }
    
    
}