<?php
namespace Dframe\View;
use \Dframe\Config;

/**
 * This class includes methods for models.
 *
 * @abstract
 */

class twigView implements \Dframe\View\interfaceView
{
	public $assigns = array();

    public function __construct(){
        $twigConfig = Config::load('twig');

        $loader = new \Twig_Loader_Filesystem($twigConfig->get('setTemplateDir'));
        $twig = new \Twig_Environment($loader, array(
        	'cache' => $twigConfig->get('setCompileDir'),
        ));

        $this->twig = $twig;
    }


    public function assign($name, $value) {
        try {
            if (isset($this->assigns[$name])) {
                throw new \Exception('You can\'t assign "'.$name . '" in Twig');
            } else {
                return $this->assigns[$name] = $value;
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
    	throw new \Exception('This module dont have fetch');
    } 

    /**
     * Przekazuje kod do szablonu Twig
     *
     * @param string $name Nazwa pliku
     * @param string $path Ścieżka do szablonu
     *
     * @return void
     */
    public function renderInclude($name, $path=null) {

    	$twigConfig = Config::load('twig');

        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];

        $path= $twigConfig->get('setTemplateDir').'/'.$folder.$name.$twigConfig->get('fileExtension', '.twig');
        try {
            if(is_file($path)) {
                $this->twig->render($name, $this->assign);
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

}