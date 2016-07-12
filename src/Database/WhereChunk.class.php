<?php
/*
 * Klasa wspomagająca warunki wyszukiwania
 * new WhereChunk('kolumna', 'test', 'LIKE');
 * https://github.com/Appsco/component-share/blob/9b29a7579c9bdcf9832b94b05ecebc74d771adf9/src/BWC/Share/Data/Select.php
 *
 */

class WhereChunk
{
    public $key;
    public $value;
    public $operator;

    function __construct($key, $value, $operator = null) {
        $this->key = $key;
        $this->value = $value;
        $this->operator = $operator;
    }

    function build() {
        $params = array();
        if ($this->value !== null) {
            $op = !is_null($this->operator) ? $this->operator : '=';

            $paramName = str_replace('.', '_', $this->key);
            if($op == 'BETWEEN'){
                $sql = "{$this->key} $op ? AND ?";

                $between = explode('AND' , $this->value);

                $params[":dateFrom"] = trim($between[0]);
                $params[":dateTo"] = trim($between[1]);
            }else{
                $sql = "{$this->key} $op ?";                                    // $sql = "{$this->key} $op {$paramName}";
                $params[":{$paramName}"] = $this->value;
            }

        } else {
            $sql = $sql = "{$this->key} IS NULL ";
        }
        
        return array($sql, $params);
    }
}