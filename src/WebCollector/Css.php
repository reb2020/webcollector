<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;

class Css {

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
    
    protected function prepared() {
        $NewCss = [];
        $Index = 0;
        foreach ($this->getData() as $Data){
            $FileInfo = pathinfo($Data->file);
            if($FileInfo["filename"] == "*"){
                $CssScanDir = $this->Dir . $FileInfo["dirname"] . DIRECTORY_SEPARATOR;
                if(!is_dir($CssScanDir)){
                    throw new CollectorException("Collector not found import dir " . $CssScanDir);
                } else {
                    foreach (scandir($CssScanDir) as $FileName){
                        if(
                            (isset($FileInfo["extension"]) && strpos($FileName, "." . $FileInfo["extension"])) ||
                            (!isset($FileInfo["extension"]) && (strpos($FileName, ".css") || strpos($FileName, ".less") || strpos($FileName, ".scss")))
                            ){
                                $NewCss[$Index] = new \stdClass();
                                $NewCss[$Index]->file = $FileInfo["dirname"] . DIRECTORY_SEPARATOR . $FileName;
                                if(isset($Data->import_dir)){
                                    $NewCss[$Index]->import_dir = $Data->import_dir;
                                }
                                $NewCss[$Index]->minify = $Data->minify;
                                $Index++;
                        }
                    }
                }
            } else {
                $NewCss[$Index] = new \stdClass();
                $NewCss[$Index]->file = $Data->file;
                if(isset($Data->import_dir)){
                    $NewCss[$Index]->import_dir = $Data->import_dir;
                }
                $NewCss[$Index]->minify = $Data->minify;
                $Index++;
            }
        }
        
        $this->_data = $NewCss;
    }
    
}