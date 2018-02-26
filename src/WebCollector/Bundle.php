<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;
use WebCollector\Resources\Css as Css;
use WebCollector\Resources\Js as Js;
use MatthiasMullie\Minify;

class Bundle {
    
    protected $type = null;
    
    protected $file = null;

    protected $version = null;

    protected $minify = false;
    
    protected $root_dir = null;

    protected $compiled_dir = null;

    protected $filters = [];

    protected $source = [];
    
    public function __construct($Type, $Data = []) {
        $this->type = $Type;
        $this->initialize($Data);
    }
    
    protected function validateFile($File){
        if(!file_exists($this->root_dir . $File)){
            throw new CollectorException("Collection file " . $File . " was not found.");
        }
    }
    
    protected function validateFilter($Data){
        $Params = [];
        if(is_object($Data)){
            if(!isset($Data->name)){
                throw new CollectorException("Filter name is required.");
            }
            if(isset($Data->class)){
                $ClassName = $Data->class;
                if(!class_exists($ClassName)){
                    throw new CollectorException("Collector could not find filter class " . $ClassName);
                } 
            } else if(!array_key_exists($Data->name, $this->filters)){
                $ClassName = '\\WebCollector\\Filters\\' . ucfirst(strtolower($Data->name));
                if(!class_exists($ClassName)){
                    throw new CollectorException("Collector could not find filter class " . $ClassName);
                }
            } else {
                $ClassName = $this->filters[$Data->name];
            }
            
            if(isset($Data->params)){
                $Params = $Data->params;
            }
        } else if(!array_key_exists($Data, $this->filters)){
            $ClassName = '\\WebCollector\\Filters\\' . ucfirst(strtolower($Data));
            if(!class_exists($ClassName)){
                throw new CollectorException("Collector could not find filter class " . $ClassName);
            }
        } else {
            $ClassName = $this->filters[$Data];
        }
        
        return [
            'class' => $ClassName,
            'params' => $Params
        ];
    }
    
    protected function scanDir($Dir, $Regex, $Depth = 1){
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($Dir), \RecursiveIteratorIterator::LEAVES_ONLY);
        $objects->setMaxDepth($Depth);
        return new \RegexIterator($objects, '/^(.*.css|.*.less|.*.scss)$/i', \RecursiveRegexIterator::GET_MATCH);
    }
    
    protected function addSource($File, $Filters) {
        $this->validateFile($File);
        if($this->type == 'css'){
            $this->source[] = new Css($this->root_dir, $this->compiled_dir, $File, $Filters);
        } else if($this->type == 'js'){
            $this->source[] = new Js($this->root_dir, $this->compiled_dir, $File, $Filters);
        }
    }
    
    protected function source($Data) {
        $Index = 0;
        foreach ($Data as $SourceData){
            if(!isset($SourceData->file) && !isset($SourceData->dir)){
                throw new CollectorException("Collection source must contenct file or dir information");
            }
            
            $SourceDataFilters = [];
            if(isset($SourceData->filters)){
                foreach ($SourceData->filters as $SourceDataFilter){
                    $SourceDataFilters[] = $this->validateFilter($SourceDataFilter);
                }
            }
            
            if(isset($SourceData->file)){
                $this->addSource($SourceData->file, $SourceDataFilters);
            } else if(isset($SourceData->dir)){
                if($this->type == 'css'){
                    $Regex = '/^(.*.css|.*.less|.*.scss)$/i';
                } else if($this->type == 'js'){
                    $Regex = '/^(.*.js)$/i';
                }
                foreach ($this->scanDir($this->root_dir . $SourceData->dir, (isset($SourceData->regex) ? $SourceData->regex : $Regex), 7) as $FileName => $FileObject){
                    $this->addSource(substr($FileName, strlen($this->root_dir), strlen($FileName)), $SourceDataFilters);
                }
            }
            
        }
    }
    
    protected function initialize($Data) {
        $Source = [];
        foreach ($Data AS $Name => $Value){
            if($Name == 'source'){
                $Source = $Value;
            } else {
                $this->{$Name} = $Value;
            }
        }
        
        $this->source($Source);
    }
    
    protected function template($Name){
        $Name = str_replace("{hash}", md5(rand(10000, 99999) . time()), $Name);
        $Name = str_replace("{d}", date('d', time()), $Name);
        $Name = str_replace("{m}", date('m', time()), $Name);
        $Name = str_replace("{y}", date('y', time()), $Name);
        $Name = str_replace("{Y}", date('Y', time()), $Name);
        $Name = str_replace("{H}", date('H', time()), $Name);
        $Name = str_replace("{i}", date('i', time()), $Name);
        $Name = str_replace("{s}", date('s', time()), $Name);
        return $Name;
    }
    
    public function collect() {
        $FileInfo = pathinfo($this->file);
        
        $DirName = $FileInfo['dirname'];
        if($DirName == '.'){
            $DirName = '';
        } else {
            $DirName .= DIRECTORY_SEPARATOR; 
        }
        
        $FileName = $this->template($FileInfo['basename']);
        $Version = null;
        if($this->version){
            $Version = $this->template($this->version);
        }
        
        $Path = $this->compiled_dir . $DirName;
        if(!is_dir($Path)){
            mkdir($Path, '0755', true);
        }
        
        $Content = '';
        foreach ($this->source as $Source){
            $Content .= $Source->filter();
        }
        
        if($this->minify){
            if($this->type == 'css'){
                $Minifier = new Minify\CSS();
            } else if($this->type == 'js'){
                $Minifier = new Minify\JS();
            }
            $Minifier->add($Content);
            $Minifier->minify($Path . $FileName);
        } else {
            file_put_contents($Path . $FileName, $Content);
        }
        
        return [
            "file" => $DirName . $FileName,
            "version" => ($Version ? '?v=' . $Version : '')
        ];
    }
    
}