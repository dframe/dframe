<?php
namespace Dframe;
use \Dframe\Config;

/**
 * This class includes methods for models.
 *
 * @abstract
 */

abstract class View extends Core
{
    public $session;
    public $msg;
    public $timeAgo;

    public function __construct($baseClass){
        parent::__construct($baseClass);
        $smartyConfig = Config::load('smarty');

        $this->baseClass = $baseClass;
        //include_once "Core/View/smarty/libs/Autoloader.php";
        //\Smarty_Autoloader::register(); 
        $smarty = new \Smarty;
        $smarty->debugging = $smartyConfig->get('debugging', false);;
        $smarty->setTemplateDir($smartyConfig->get('setTemplateDir', './View/templates'))
                     ->setCompileDir($smartyConfig->get('setCompileDir', './View/templates_c'));
        
        $this->baseClass->smarty = $smarty;
        $this->assign('router', $this->router);
    }

    /**
     * Przekazuje kod do szablonu Smarty
     *
     * @param string $name Nazwa pliku
     * @param string $path Ścieżka do szablonu
     *
     * @return void
     */
    public function render($name, $path=null) {
    	$smartyConfig = Config::load('smarty');
		
		$pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];
		
        $path= $smartyConfig->get('setTemplateDir', './View/templates').'/'.$folder.$name.$smartyConfig->get('fileExtension', '.html.php');
        try {
            if(is_file($path)) {
                $this->baseClass->smarty->display($path); // Ładowanie widoku
            } else {
                throw new \Exception('Can not open template '.$name.' in: '.$path);
            }
        }
        catch(Exception $e) {
            echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br />
                Trace: '.$e->getTraceAsString();
            exit();
        }
    }
 
     /**
     * Wyświetla dane JSON.
     * @param array $data Dane do wyświetlenia
     */
    public function renderJSON($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
 
    /**
     * Wyświetla dane JSONP.
     * @param array $data Dane do wyświetlenia
     */
    public function renderJSONP($data) {
        header('Content-Type: application/json');
        $callback = null;
        if(isset($_GET['callback'])) 
        	$callback = $_GET['callback'];
        
        echo $callback . '(' . json_encode($data) . ')';
        exit();
    }


    public function assign($name, $value) {
        try {
            if ($this->baseClass->smarty->getTemplateVars($name) !== null) {
                throw new \Exception('You can\'t assign "'.$name . '" in Smarty');
            } else {
                return $this->baseClass->smarty->assign($name, $value);
            }
        }
        catch(Exception $e) {
            echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br />
                Trace: '.$e->getTraceAsString();
            exit();
        }
    }

    public function fetch($name, $path=null) {
    	$smartyConfig = Config::load('smarty');

		$pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];
        
        $path= $smartyConfig->get('setTemplateDir', './View/templates').'/'.$folder.$name.$smartyConfig->get('fileExtension', '.html.php');

        try {
            if(is_file($path)) {
                return $this->baseClass->smarty->fetch($path); // Ładowanie widoku
            } else {
                throw new \Exception('Can not open template '.$name.' in: '.$path);
            }
        }
        catch(Exception $e) {
            echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br />
                Trace: '.$e->getTraceAsString();
            exit();
        }
    }
}
?>