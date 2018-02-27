<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;
use WebCollector\Data as Data;
use WebCollector\Resources\Css as Css;
use WebCollector\Resources\Js as Js;
use MatthiasMullie\Minify;

class Bundle extends Data {
    
    protected $type = null;
    
    protected $file = null;

    protected $version = null;

    protected $minify = false;
    
    public function __construct($Type, $Data = []) {
        $this->type = $Type;
        $this->initialize($Data);
    }
    
    protected function scanDir($Dir, $Regex, $Depth = 1){
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($Dir), \RecursiveIteratorIterator::LEAVES_ONLY);
        $objects->setMaxDepth($Depth);
        return new \RegexIterator($objects, $Regex, \RecursiveRegexIterator::GET_MATCH);
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
                    $FileInfo = pathinfo($FileName);
                    if(in_array($FileInfo['basename'], ['.', '..'])){
                        continue;
                    }
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