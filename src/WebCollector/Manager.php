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
        $this->Compiler = new Compiler($Dir, "collections.lock");
        $this->Config = new Config($Dir, "collections.json", $this->Compiler->getLastFiles());
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
            
            echo "Scan Last Files: " . $this->Dir . $Collection->compiled_dir . "\n";
            foreach ($Collection->old_files as $FileName){
                echo " -> " . $FileName . "\n";
            }
            
            echo "Compile CSS: \n";
            $Css = $this->Compiler->compileCss($Collection);
            foreach ($Css->files() as $FileName){
                echo " -> " . $FileName . " \n";
            }

            echo "Compile JS: \n";
            $Js = $this->Compiler->compileJs($Collection);
            foreach ($Js->files() as $FileName){
                echo " -> " . $FileName . " \n";
            }
            
            $Data = [
                        'css' => $Css->newFiles(), 
                        'js' => $Js->newFiles(),
                        'files' => $Collection->new_files
            ];
            
            echo "Generate Files: \n";
            foreach ($Collection->new_files as $FileName){
                echo " -> " . $FileName . " \n";
            }
            
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