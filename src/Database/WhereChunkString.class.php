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
        return array($this->string, $this->bindWhere);
    }
}