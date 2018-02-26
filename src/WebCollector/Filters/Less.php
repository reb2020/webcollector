<?php

namespace WebCollector\Filters;

use WebCollector\Exception as CollectorException;
use WebCollector\Filter as Filter;

class Less extends Filter {
    
    protected $Less = null;
    
    protected function initialize() {
        $this->Less = new \lessc();
        if(isset($this->Params->import_dir)) {
            $this->Less->addImportDir($this->RootDir . $this->Params->import_dir);
        }
    }
    
    public function filter() {
        try {
            return $this->Less->compile($this->Content);
        } catch (\Exception $e) {
            throw new CollectorException("Filter Less: " . $e->getMessage());
        }
        
        return "";
    }
    
}