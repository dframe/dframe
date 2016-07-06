<?php
namespace Dframe\View;
use \Dframe\Config;

/**
 * This class includes methods for models.
 *
 * @abstract
 */

class smarty
{
    public $session;
    public $msg;
    public $timeAgo;

    public function __construct(){
        $smartyConfig = Config::load('smarty');

        $smarty = new \Smarty;
        $smarty->debugging = $smartyConfig->get('debugging', false);;
        $smarty->setTemplateDir($smartyConfig->get('setTemplateDir', './View/templates'))
                     ->setCompileDir($smartyConfig->get('setCompileDir', './View/templates_c'));
        
        $this->smarty = $smarty;
    }

    /**
     * Przekazuje kod do szablonu Smarty
     *
     * @param string $name Nazwa pliku
     * @param string $path Ścieżka do szablonu
     *
     * @return void
     */
    public function renderHTML($name, $path=null) {
    	$smartyConfig = Config::load('smarty');
		
		$pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];
		
        $path= $smartyConfig->get('setTemplateDir', './View/templates').'/'.$folder.$name.$smartyConfig->get('fileExtension', '.html.php');
        try {
            if(is_file($path)) {
                $this->smarty->display($path); // Ładowanie widoku
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
 
    public function assign($name, $value) {
        try {
            if ($this->smarty->getTemplateVars($name) !== null) {
                throw new \Exception('You can\'t assign "'.$name . '" in Smarty');
            } else {
                return $this->smarty->assign($name, $value);
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
                return $this->smarty->fetch($path); // Ładowanie widoku
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