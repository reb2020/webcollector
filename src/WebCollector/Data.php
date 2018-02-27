<?php

namespace WebCollector;

use WebCollector\Exception as CollectorException;

class Data {
    
    protected $root_dir = null;
    
    protected $compiled_dir = null;
    
    protected $filters = [];
    
    protected $source = [];
    
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
    
}