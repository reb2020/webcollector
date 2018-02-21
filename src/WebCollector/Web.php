<?php

namespace WebCollector;

class Web {
    
    protected $File = "collections.lock";
    
    protected $Collections = [];
    
    static $Instance = null;
    
    public static function getInstance($Dir = null)
    {
        if (self::$Instance === null) self::$Instance = new Web($Dir);
        return self::$Instance;
    }
    
    public function __construct($Dir = null) {
        if($Dir !== null){
            $this->File = $Dir . $this->File;
        }
        
        if(file_exists($this->File)){
            foreach (json_decode(file_get_contents($this->File)) as $Data){
                $this->Collections[$Data->name] = $Data;
            }
        }
    }
    
    public function has($Name){
        return array_key_exists($Name, $this->Collections) ? true : false;
    }
    
    public function CSS($Name) {
        $Return = [];
        if($this->has($Name) && isset($this->Collections[$Name]['css'])){
            foreach ($this->Collections[$Name]['css'] as $File){
                $Return[] = '<link rel="stylesheet" type="text/css" href="' . $File . '" />';
            }
        }
        return implode("\n", $Return);
    }
    
    public function JS($Name) {
        $Return = [];
        if($this->has($Name) && isset($this->Collections[$Name]['js'])){
            foreach ($this->Collections[$Name]['js'] as $File){
                $Return[] = '<script type="text/javascript" src="' . $File . '"></script>';
            }
        }
        return implode("\n", $Return);
    }
    
}