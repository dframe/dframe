<?php
namespace View;

abstract class View extends \Dframe\View{

    public function __construct($baseClass){
        parent::__construct($baseClass);
        $this->assign('token', $this->baseClass->token);
    }
}
?>