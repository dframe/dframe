<?php
namespace Dframe\View;
use \Dframe\Config;

/**
 * This class includes methods for models.
 *
 * @abstract
 */

class smartyView implements \Dframe\View\interfaceView
{
    public function __construct(){
        $smartyConfig = Config::load('View/smarty');

        $smarty = new \Smarty;
        $smarty->debugging = $smartyConfig->get('debugging', false);;
        $smarty->setTemplateDir($smartyConfig->get('setTemplateDir'))
                ->setCompileDir($smartyConfig->get('setCompileDir'));
        
        $this->smarty = $smarty;
    }


    public function assign($name, $value) {
        try {
            if($this->smarty->getTemplateVars($name) !== null)
                throw new \Exception('You can\'t assign "'.$name . '" in Smarty');
            else
                return $this->smarty->assign($name, $value);

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
        
        $path= $smartyConfig->get('setTemplateDir').'/'.$folder.$name.$smartyConfig->get('fileExtension', '.html.php');

        try {
            if(is_file($path))
                return $this->smarty->fetch($path); // Ładowanie widoku
            else
                throw new \Exception('Can not open template '.$name.' in: '.$path);

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
     * Przekazuje kod do szablonu Smarty
     *
     * @param string $name Nazwa pliku
     * @param string $path Ścieżka do szablonu
     *
     * @return void
     */
    public function renderInclude($name, $path=null) {

    	$smartyConfig = Config::load('smarty');
		
		$pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];
		
        $path= $smartyConfig->get('setTemplateDir').'/'.$folder.$name.$smartyConfig->get('fileExtension', '.html.php');
        try {
            if(is_file($path))
                $this->smarty->display($path); // Ładowanie widoku
            else
                throw new \Exception('Can not open template '.$name.' in: '.$path);

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

}