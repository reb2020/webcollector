<?php

namespace WebCollector\Filters;

use WebCollector\Exception as CollectorException;
use WebCollector\Filter as Filter;
use Babel\Transpiler\Core as BabelTranspiler;

class Transpiler extends Filter {
    
    protected $Transpiler = null;
    
    protected function initialize() {
        $this->Transpiler = new BabelTranspiler($this->paramsToArray());
    }
    
    public function filter() {
        try {
            return $this->Transpiler->execute($this->Content);
        } catch(\Exception $e) {
            throw new CollectorException("Filter Transpiler: " . $e->getMessage());
        }
        
        return "";
    }
    
}