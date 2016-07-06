<?php
namespace View;

abstract class View extends \Dframe\View{

	public function init(){
		$this->baseClass->smarty = new \Dframe\View\Smarty();
		$this->assign('token', $this->baseClass->token);
	}

}