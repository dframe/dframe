<?php
/*
 * Klasa wspomagająca warunki wyszukiwania
 * new WhereStringChunk('`kolumna` LIKE ?', array('test'));
 *
 *
 */

class WhereStringChunk
{
    public $string;
    public $bindWhere;

    function __construct($string, $bindWhere = null) {
        $this->string = $string;
        $this->bindWhere = $bindWhere;
    }
    
    function build() {
    	$paramName = str_replace('.', '_', $this->string);
    	$column = explode(' ' , $paramName);

        
        $params[":{$column[0]}"] = $this->bindWhere;

        return array($this->string, $params);
    }
}