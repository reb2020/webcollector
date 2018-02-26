<?php

namespace WebCollector\Filters;

use WebCollector\Exception as CollectorException;
use WebCollector\Filter as Filter;

class Scss extends Filter {
    
    protected $Scss = null;
    
    protected function initialize() {
        $this->Scss = new \Leafo\ScssPhp\Compiler();
        if(isset($this->Params->import_dir)) {
            $this->Scss->setImportPaths($this->RootDir . $this->Params->import_dir);
        }
    }
    
    public function filter() {
        try {
            return $this->Scss->compile($this->Content);
        } catch (\Exception $e) {
            throw new CollectorException("Filter Scss: " . $e->getMessage());
        }
        
        return "";
    }
    
}