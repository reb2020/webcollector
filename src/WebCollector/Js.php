<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;

class Js {
    
    protected $_data = [];
    private $Dir = null;
    
    public function __construct(String $Dir, $Data = []) {
        $this->_data = $Data;
        $this->Dir = $Dir;
        $this->prepared();
    }
    
    public function getData() {
        return $this->_data;
    }
    
    protected function scanDir($Dir, $Depth = 1){
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($Dir), \RecursiveIteratorIterator::LEAVES_ONLY);
        $objects->setMaxDepth($Depth);
        return new \RegexIterator($objects, '/^(.*.js)$/i', \RecursiveRegexIterator::GET_MATCH);
    }
    
    protected function prepared() {
        $NewJS = [];
        $Index = 0;
        foreach ($this->getData() as $Data){
            $FileInfo = pathinfo($Data->file);
            if($FileInfo["filename"] == "*"){
                $JsScanDir = $this->Dir . $FileInfo["dirname"] . DIRECTORY_SEPARATOR;
                if(!is_dir($JsScanDir)){
                    throw new CollectorException("Collector not found import dir " . $JsScanDir);
                } else {
                    foreach ($this->scanDir($JsScanDir, (!isset($FileInfo["extension"]) ? 7 : 1)) as $FileName){
                        if(
                            (isset($FileInfo["extension"]) && $FileInfo["extension"] != '*' && strpos($FileName, "." . $FileInfo["extension"])) ||
                            (!isset($FileInfo["extension"]))
                            ){
                                $NewJS[$Index] = new \stdClass();
                                $NewJS[$Index]->file = substr($FileName, strlen($this->Dir), strlen($FileName));
                                $NewJS[$Index]->minify = $Data->minify;
                                $Index++;
                        }
                    }
                }
            } else {
                $NewJS[$Index] = new \stdClass();
                $NewJS[$Index]->file = $Data->file;
                $NewJS[$Index]->minify = $Data->minify;
                $Index++;
            }
        }
        
        $this->_data = $NewJS;
    }
    
}