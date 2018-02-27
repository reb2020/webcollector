<?php

namespace WebCollector\Resources;

use WebCollector\Resource as Resource;

class File extends Resource {
    
    protected $FileTo = null;
    
    public function __construct($RootDir, $CompileDir, $File, $FileTo, $Filters = []) {
        $this->FileTo = $FileTo;
        parent::__construct($RootDir, $CompileDir, $File, $Filters);
    }
    
    public function getFileTo(){
        return $this->FileTo;
    }
    
}