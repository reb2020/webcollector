<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;
use WebCollector\Data as Data;
use WebCollector\Resources\File as File;

class Copy extends Data {
    
    protected $root_dir = null;
    
    protected $compiled_dir = null;
    
    protected $filters = [];
    
    protected $source = [];
    
    public function __construct($Data = []) {
        $this->initialize($Data);
    }
    
    protected function scanDir($Dir, $Regex, $Depth = 1){
        $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($Dir), \RecursiveIteratorIterator::LEAVES_ONLY);
        $objects->setMaxDepth($Depth);
        return new \RegexIterator($objects, $Regex, \RecursiveRegexIterator::GET_MATCH);
    }
    
    protected function addSource($File, $FileTo, $Filters) {
        $this->validateFile($File);
        $this->source[] = new File($this->root_dir, $this->compiled_dir, $File, $FileTo, $Filters);
    }
    
    protected function source($Data) {
        $Index = 0;
        foreach ($Data as $SourceData){
            if(!isset($SourceData->from) && !isset($SourceData->to)){
                throw new CollectorException("Collection source must contenct from and to information");
            }
            
            $SourceDataFilters = [];
            if(isset($SourceData->filters)){
                foreach ($SourceData->filters as $SourceDataFilter){
                    $SourceDataFilters[] = $this->validateFilter($SourceDataFilter);
                }
            }
            
            if(!is_dir($this->root_dir . $SourceData->from) && file_exists($this->root_dir . $SourceData->from)){
                $this->addSource($SourceData->from, $SourceData->to, $SourceDataFilters);
            } else if (is_dir($this->root_dir . $SourceData->from)) {
                foreach ($this->scanDir($this->root_dir . $SourceData->from, (isset($SourceData->regex) ? $SourceData->regex : '/^(.*.*)$/i'), 7) as $FileName => $FileObject){
                    $FileInfo = pathinfo($FileName);
                    if(in_array($FileInfo['basename'], ['.', '..'])){
                        continue;
                    }
                    $File = substr($FileName, strlen($this->root_dir . $SourceData->from), strlen($FileName));
                    $FileTo = $SourceData->to . $File;
                    $this->addSource($SourceData->from . $File, $FileTo, $SourceDataFilters);
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
    
    public function execute() {
        $Return = [];
        foreach ($this->source as $Source){
            $FileInfo = pathinfo($Source->getFile());
            $FileInfoTo = pathinfo($Source->getFileTo());
            $Return[] = $Source->getFileTo();
            
            $FileNameTo = $FileInfoTo['basename'];
            
            $DirName = $FileInfo['dirname'];
            if($DirName == '.'){
                $DirName = '';
            } else {
                $DirName .= DIRECTORY_SEPARATOR;
            }
            
            $DirNameTo = $FileInfoTo['dirname'];
            if($DirNameTo == '.'){
                $DirNameTo = '';
            } else {
                $DirNameTo .= DIRECTORY_SEPARATOR;
            }
            
            $Path = $this->compiled_dir . $DirNameTo;
            if(!is_dir($Path)){
                mkdir($Path, '0755', true);
            }
            
            $Content = $Source->filter();
            file_put_contents($Path . $FileNameTo, $Content);
        }
        return $Return;
    }
    
}