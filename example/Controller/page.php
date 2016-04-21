<?php
namespace Controller;
use Dframe\Config;

class pageController extends \Controller\Controller 
{

    public function page(){
    	$smartyConfig = Config::load('smarty');
    	$view = $this->loadView('index');

    	$patchController =  $smartyConfig->get('setTemplateDir', './View/templates').'/page/'.$_GET['action'].$smartyConfig->get('fileExtension', '.html.php');
        if(file_exists($patchController)){
        	$view->render('page/'.$_GET['action']);
        }else{
        	$this->redirect('page/index');
        }
    }

    
    public function index() {
        $view = $this->loadView('index');

        //$this->customLoad;
        $view->assign('contents', 'Example assign');
        $view->render('index');

    }
}
?>