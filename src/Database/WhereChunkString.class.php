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
        $params = $this->flatter($params);

        return array($this->string, $params);
    }

    // Bug fix Autor Krzysztof Franek
    function flatter($array){ 
        $result = array();
        foreach($array as $item){
            if(is_array($item)){
                $result = array_merge($result, $this->flatter($item));
            }
            else
                $result[] = $item;
        }
        return $result;
    }
}