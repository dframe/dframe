<?php
namespace Controller;
use Dframe\Controller;
use Dframe\Config;

class pageController extends Controller 
{
    /* 
     * Dynamiczny loader stron wykrywa akcje jako plik i stara sie go za ładować
     */
    public function page(){
        $smartyConfig = Config::load('smarty');
        $view = $this->loadView('index');

        $patchController = $smartyConfig->get('setTemplateDir', appDir.'../app/View/templates').'/page/'.htmlspecialchars($_GET['action']).$smartyConfig->get('fileExtension', '.html.php');
        
        if(file_exists($patchController))
            $view->render('page/'.htmlspecialchars($_GET['action']));
        else
            $this->router->redirect('page/index');
        
    }

    public function index() {
        $view = $this->loadView('index');

        $view->assign('contents', 'Example assign');
        $view->render('index');

    }
}