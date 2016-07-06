<?php
namespace Dframe;
use \Dframe\Config;

/**
 * Copyright (C) 2015  
 * @author Sławomir Kaleta
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


abstract class View extends Core
{
    public $session;
    public $msg;
    public $timeAgo;


    public function __construct($baseClass){
        parent::__construct($baseClass);
        $this->baseClass = $baseClass;
        $this->router =  $this->router;

    }

    public function render($data, $type = null){

        if(empty($type) OR $type == 'html')
            $this->renderHTML($data);
        
        elseif($type == 'jsonp')
            $this->renderJSONP($data);

        else
            $this->renderJSON($data);
               
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
		
        $path = $smartyConfig->get('setTemplateDir', appDir.'../app/View/templates').'/'.$folder.$name.$smartyConfig->get('fileExtension', '.html.php');
        try {

            if(is_file($path))
                $this->baseClass->smarty->display($path); // Ładowanie widoku
            else 
                throw new \Exception('Can not open template '.$name.' in: '.$path);
            
        }catch(Exception $e) {
            echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br />
                Trace: '.$e->getTraceAsString();
            exit();
        }
    }


    public function renderInclude($name){

        $pathFile = pathFile($name);
        $folder = $pathFile[0];
        $name = $pathFile[1];
        
        $path = appDir.'../app/View/templates/'.$folder.$name.'.php';

        try {
            if(is_file($path)) {
                 include($path);                    
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


    /**
     * It sets data.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function set($name, $value) {
        $this->$name=$value;
    }
    /**
     * It sets data.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    public function __set($name, $value) {
        $this->$name=$value;
    }
    /**
     * It gets data.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get($name) {
        return $this->$name;
    }
    /**
     * It gets data.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name) {
        if( isset($this->$name) )
            return $this->$name;
        return null;
    }



}