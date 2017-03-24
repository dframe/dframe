<?php
namespace Dframe;
use Dframe\Config;

/**
 * Copyright (C) 2016  Sławomir Kaleta
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

class Router
{

    public $aRouting;
    private $aRoutingParse;
    private $sURI;
    private $parsingArray;
    private $subdomain = false;

    public function __construct(){

        if(!defined('HTTP_HOST') AND isset($_SERVER['HTTP_HOST']))
            define('HTTP_HOST', $_SERVER['HTTP_HOST']);
        elseif(!defined('HTTP_HOST'))
            define('HTTP_HOST', '');

        $aURI = explode('/', $_SERVER['SCRIPT_NAME']);
        
        array_pop($aURI);
        $this->sURI = implode('/', $aURI).'/';
        $this->sURI = str_replace('/web/', '/', $this->sURI);

        $routerConfig = Config::load('router');
        $this->setHttps($routerConfig->get('https', false));

        $this->aRouting = $routerConfig->get();
        $this->aRoutingParse = $routerConfig->get();

        // Check forced Https
        if($this->https == true){
            $this->requestPrefix = 'https://';

            // If forced than redirect
            if(isset($_SERVER['REQUEST_SCHEME']) AND ((!empty($_SERVER['REQUEST_SCHEME']) AND $_SERVER['REQUEST_SCHEME'] == 'http'))){
                header('Location: '.$this->requestPrefix.HTTP_HOST.'/'.$_SERVER['REQUEST_URI']);
                return;
            }
            
        }else{
            $this->requestPrefix = 'http://';
            if(isset($_SERVER['REQUEST_SCHEME']) AND ((!empty($_SERVER['REQUEST_SCHEME']) AND $_SERVER['REQUEST_SCHEME'] == 'https') OR (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') OR (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443'))) {
                $this->requestPrefix = 'https://';
            }
        }

    }
 
    private function setHttps($option = false){
        if(!in_array($option, array(true, false)))
            throw new \Exception("Incorect option", 403);

        $this->https = $option;
    }

    // string||array (folder,)controller/action 
    // Sprawdzanie czy to jest aktualnie wybrana zakładka
    public function isActive($url) {

        if(empty($url) OR $url == false)
            return false; 

        if(!is_array($url))
            $url = array($url);

        foreach($url as $oneurl) {
    
            $sRequest = preg_replace('!'.$this->sURI.'(.*)$!i',  '$1', $_SERVER['REQUEST_URI']);
    
            if(strpos($oneurl, '/'))
                list($task, $action) = explode('/', $oneurl);
            else
                $task = $oneurl;
    
            if(MOD_REWRITE){
            
                if(substr($sRequest, -1)!='/')
                    $sRequest .= '/';
    
                $sGets = $this->parseUrl($sRequest);
    
            }else{
    
                if(substr($sRequest, 0, 1)=='?')
                    $sRequest = substr($sRequest, 1);
                
                $sGets = $sRequest;
                $sGets = str_replace("index.php?", "", $sGets);
                
            }
    
            parse_str($sGets, $aGets);
    
            $aTask = !empty($aGets['task'])?$aGets['task']:$this->aRouting['NAME_CONTROLLER'];
            $gAction = !empty($aGets['action'])?$aGets['action']:$this->aRouting['NAME_MODEL'];
    
    
            if(!empty($action))
                return ($task == $aTask) AND ($action == $gAction);
    
            return ($task == $aTask);
        }

        return false;

    }

    public function publicWeb($sUrl = null, $path = null){
        if(is_null($path))
            $path = $this->aRouting['publicWeb'];

        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix.HTTP_HOST.'/'.$path;
        $sUrl .= $sExpressionUrl;
        
        return $sUrl;
    }

    public function makeUrl($sUrl = null){

        $aParamsHook = explode('#', $sUrl);
        $aParams = explode('?', $aParamsHook[0]);
        $aParams_ = explode('/', $aParams[0]);
        $sTask = $aParams_[0];

        $sAction = null;
        if(isset($aParams_[1]) AND !empty($aParams_[1])) 
            $sAction = $aParams_[1];

        if(isset($aParams[1])) 
            parse_str($aParams[1], $aParams);
        else 
            $aParams = array();
        
        $findKey = explode('?', $sUrl);
        if(isset($findKey[0]))
            $findKey = $findKey[0];


        if(MOD_REWRITE){

            if(isset($this->aRouting[$findKey])){

                $sExpressionUrl = $this->aRouting[$findKey][0];
                foreach($aParams AS $key => $value) {
                    $sExpressionUrl = str_replace('['.$key.']', $value, $sExpressionUrl, $count);
                    if ($count > 0) {
                        unset($aParams[$key]);
                    }

                }

                if(isset($aParams))
                    $sExpressionUrl = str_replace('[params]', $this->parseParams($this->aRouting['default']['_params'][0], $aParams), $sExpressionUrl);
            

            }else{

				$sExpressionUrl = $this->aRouting['default'][0];

				$sExpressionUrl = str_replace('[task]', $sTask, $sExpressionUrl);
				$sExpressionUrl = str_replace('[action]', $sAction, $sExpressionUrl);
				if(isset($aParams))
				{
					$sExpressionUrl = str_replace('[params]', $this->parseParams($this->aRouting['default']['_params'][0], $aParams), $sExpressionUrl);
				}

				/*

                $sExpressionUrl = $sTask;
                if(!empty($sAction))
                    $sExpressionUrl = $sTask.'/'.$sAction;
    
                if(!empty($aParams)) {
                    $sExpressionUrl .= '?';
                    foreach($aParams AS $k => $v){
                        $test[] = $k.'='.$v;
                    }
                    $sExpressionUrl .= implode('&', $test);
                }

                */
            }

        }else{

            if(empty($sTask)){
                $sExpressionUrl = '';

            }else{

                if(isset($this->aRouting[$findKey])){
    
                    $sExpressionUrl0 = $this->aRouting[$findKey][1];
                    foreach($aParams AS $key => $value) {
                        $sExpressionUrl0 = str_replace('['.$key.']', $value, $sExpressionUrl0);
                    }

                    $sExpressionUrl = 'index.php?'.$sExpressionUrl0;
          
                }else{

                    $sExpressionUrl = 'index.php?task='.$sTask;
                    if(!empty($sAction))
                        $sExpressionUrl = 'index.php?task='.$sTask.'&action='.$sAction;
        
                    if(!empty($aParams)){
                        foreach($aParams AS $k => $v){
                            $sExpressionUrl .= '&'.$k.'='.$v;
                        }
                    }
                }
            }

        }

        $HTTP_HOST = HTTP_HOST;
        if(!empty($this->subdomain))
            $HTTP_HOST = $this->subdomain.'.'.HTTP_HOST;

            $sUrl = $this->requestPrefix.$HTTP_HOST.'/';

        $sUrl .= $sExpressionUrl;

        return $sUrl;
    }

    private function parseParams($sRouting, $aParams){
        $sReturn = null;
        foreach($aParams AS $key => $value){
            $sReturn .= str_replace(array('[name]', '[value]'), array($key, $value), $sRouting);
        }
        return $sReturn;
    }

    public function parseGets(){


        
        $sRequest = preg_replace('!'.$this->sURI.'(.*)$!i',  '$1', $_SERVER['REQUEST_URI']);
        
        if(MOD_REWRITE){

            if(substr($sRequest, -1)!='/')
                $sRequest .= '/';
            $sGets = $this->parseUrl($sRequest);
            //$sGets = str_replace('?', '&', $sGets);

            parse_str($sGets, $aGets);

            $_GET['task'] = !empty($aGets['task'])?$aGets['task']:$this->aRouting['NAME_CONTROLLER'];
            unset($aGets['task']);

            $_GET['action'] = !empty($aGets['action'])?$aGets['action']:$this->aRouting['NAME_MODEL'];
            unset($aGets['action']);

            $_GET = array_merge($_GET, $aGets);

        }else{

            $_GET['task'] = !empty($_GET['task'])?$_GET['task']:$this->aRouting['NAME_CONTROLLER'];
            $_GET['action'] = !empty($_GET['action'])?$_GET['action']:$this->aRouting['NAME_MODEL'];
            
        }

    }

    private function parseUrl($sRequest){   


        $sVars = null;
        foreach($this->aRoutingParse AS $k => $v){
            
            if(!is_array($v))
                continue;

            preg_match_all('!\[(.+?)\]!i', $v[0], $aExpression_);
            $sExpression = preg_replace_callback('!\[(.+?)\]!i', function($m) use ($k){ 
                return $this->transformParam($m[1], $k);
            }, $v[0]);


            if(preg_match_all('!'.$sExpression.'!i', $sRequest, $aExpression__)){

                foreach($aExpression__ AS $k_ => $v_){
                    foreach($v_ AS $kkk => $vvv){

                        if(!isset($aExpression_[1][$k_-1]))
                            $aExpression_[1][$k_-1] = null;
                        
                        if($kkk>0)
                            $aExpression[] = array($aExpression_[1][$k_-1].'_'.$kkk, $vvv);
                        else
                            $aExpression[] = array($aExpression_[1][$k_-1], $vvv);
                    
                    }
                }

                unset($aExpression[0]);
                $iCount = count($aExpression__[0]);
                if($iCount>1){
                    for($i=0;$i<$iCount;$i++){
                        if($i>0)
                            $sVars .= '&'.preg_replace('!\[(.+?)\]!i', '[$1_'.$i.']', $v[1]);
                        else
                            $sVars = '&'.$v[1];                        
                    }

                }else                
                    $sVars = '&'.$v[1];
            
                foreach($aExpression AS $k => $v_){
                    if(!isset($v['_'.$v_[0]]))
                        $v['_'.$v_[0]] = null;
                    
                    if(!is_array($v['_'.$v_[0]]))
                        $sVars = str_replace('['.$v_[0].']', $v_[1], $sVars);
                        
                    else {
                        $this->aRoutingParse = array($v['_'.$v_[0]]);
                        $sVars = $sVars.$this->parseUrl($v_[1]);

                    }
                }                
                break;
            }
        }    

        return $sVars;
    }

    private function transformParam($sParam, $k){
        if(isset($this->aRouting[$k][$sParam]) AND !is_array($this->aRouting[$k][$sParam]))
            return $this->aRouting[$k][$sParam];
        else 
            return '(.+?)';

    }

    /**
     * Przekierowanie adresu 
     *
     * @param string CONTROLLER/MODEL?parametry
     *
     * @return void
     */

    public function redirect($url = '') {
        header("Location: ".$this->makeUrl($url));
        exit();
    }

    public function subdomain($subdomain){
        $this->subdomain = $subdomain;
        return $this;
        
    }

    /**
     * Metoda kopiujaca i udostepniajaca pliki w katalogu assets 
     *
     * @param string|NULL
     * @param string|NULL
     *
     * @return void
     */
    public function assets($sUrl = null, $path = null){


        if(is_null($path)){
            $path = 'assets';
            if(isset($this->aRouting['assetsPath']) AND !empty($this->aRouting['assetsPath'])){
                $path = $this->aRouting['assetsPath'];

                if(!is_dir(appDir.$path)){
                    if(!mkdir(appDir.$path))
                        throw new BaseException('Unable to create'.appDir.$path);
                }
            }
        }

        //Podstawowe sciezki
        $srcPath = appDir.'../app/View/assets/'.$sUrl;
        $dstPath = appDir.$path.'/'.$sUrl;
        //Kopiowanie pliku jezeli nie istnieje
        if(!file_exists($dstPath)){
            if(!file_exists($srcPath))
                return '';

            //Rekonstruujemy sciezki
            $relDir = explode('/', $sUrl);
            array_pop($relDir);
            $subDir = "";
            foreach ($relDir as $dir) {
                $subDir .= "/".$dir;
                $fileDst = appDir.$path.$subDir;

                if(!is_dir($fileDst)){
                    if(!mkdir($fileDst)){
                        throw new BaseException('Unable to create new directory');
                    }
                }
            }


            if(!is_writable(appDir.$path))
                throw new BaseException('Unable to get an app/view/'.$path);

            if(!copy($srcPath, $dstPath))
                throw new BaseException('Unable to copy an asset');
        }

        //Zwrocenie linku do kopii
        $sExpressionUrl = $sUrl;
        $sUrl = $this->requestPrefix.HTTP_HOST.'/'.$path.'/';
        $sUrl .= $sExpressionUrl;
        
        return $sUrl;
    }

}
