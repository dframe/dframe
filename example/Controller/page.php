<?php
namespace Controller;

class pageController extends \Dframe\Controller 
{

    public function page(){
    	$smartyConfig = \Dframe\Config::load('smarty');
    	$view = $this->loadView('index');

    	$patchController =  $smartyConfig->get('setTemplateDir', './View/templates').'/page/'.$_GET['action'].$smartyConfig->get('fileExtension', '.html.php');
        if(file_exists($patchController)){
        	$view->render('page/'.$_GET['action']);
        }else{
        	$this->redirect('page/index');
        }
    }

    
    public function index() {
    	$exampleConfig = $this->loadConfig('example')->get('test', ':)'); 
        $view = $this->loadView('index');

        $this->customLoad;
        $view->assign('contents', 'Example assign');
        $view->render('index');

    }
}
?>
