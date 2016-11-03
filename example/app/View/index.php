<?php
namespace View;

class indexView extends \View\View
{
    public function init(){
        $this->assign('router', $this->router);

        /* Domyślne alerty */
        if($this->baseClass->msg->hasMessages('error'))
            $this->assign('msgError', $this->baseClass->msg->display('error'));
        
        if($this->baseClass->msg->hasMessages('success')) 
            $this->assign('msgSuccess', $this->baseClass->msg->display('success'));
        
        if($this->baseClass->msg->hasMessages('warning')) 
            $this->assign('msgWarning', $this->baseClass->msg->display('warning'));
        
        if($this->baseClass->msg->hasMessages('info')) 
            $this->assign('msgInfo', $this->baseClass->msg->display('info'));

    }

}