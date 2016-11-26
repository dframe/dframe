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

class Router extends Core
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

    }
 
    public function setHttps($option = false){
        if(!in_array($option, array(true, false)))
            throw new \Exception("Incorect option", 403);

        $this->https = $option;
    }

    // string||array (folder,)controller/action 
    // Sprawdzanie czy to jest aktualnie wybrana zakładka
    public function isActive($url) {

        if(is_array($url)) {
            foreach($url as $oneurl) {
                if(strpos($oneurl, '/'))
                    list($task, $action) = explode('/', $oneurl);
                else
                    $task = $oneurl;
                    
                if(!empty($action)) {
                    if($task == $this->aRouting['NAME_CONTROLLER'] AND $action == $this->aRouting['NAME_MODEL'])
                        return true;
                } else {
                    if($task == $this->aRouting['NAME_CONTROLLER'])
                        return true;
                }
            }
            return false;

        }else{
            if(strpos($url, '/'))
                list($task, $action) = explode('/', $url);
            else
                $task = $url;
                
            if(!empty($action))
                return ($task == $this->aRouting['NAME_CONTROLLER']) AND ($action == $this->aRouting['NAME_MODEL']);

            return ($task == $this->aRouting['NAME_CONTROLLER']);
        }

        return false;
    }

    public function publicWeb($sUrl = null, $path = null){
        if(is_null($path))
            $path = $this->aRouting['publicWeb'];

        $prefix = ($this->https == true ? 'https://' : 'http://');

        $sExpressionUrl = $sUrl;
        $sUrl = $prefix.HTTP_HOST.$this->sURI.$path;
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
        
        if(MOD_REWRITE){
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

        }else{
            if(empty($sTask)){
                $sExpressionUrl = '';

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
        $prefix = ($this->https == true ? 'https://' : 'http://');

        $HTTP_HOST = HTTP_HOST;
        if(!empty($this->subdomain))
            $HTTP_HOST = $this->subdomain.'.'.HTTP_HOST;

            $sUrl = $prefix.$HTTP_HOST.$this->sURI;

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
        $routerConfig = Config::load('router');
        
        if(MOD_REWRITE){

            $sRequest = preg_replace('!'.$this->sURI.'(.*)$!i',  '$1', $_SERVER['REQUEST_URI']);
            if(substr($sRequest, -1)!='/')
                $sRequest .= '/';
            $sGets = $this->parseUrl($sRequest);

            parse_str($sGets, $aGets);

            $_GET['task'] = !empty($aGets['task'])?$aGets['task']:$routerConfig->get('NAME_CONTROLLER');
            unset($aGets['NAME_CONTROLLER']);

            $_GET['action'] = !empty($aGets['action'])?$aGets['action']:$routerConfig->get('NAME_MODEL');;
            unset($aGets['NAME_MODEL']);

            $_GET = array_merge($_GET, $aGets);

        }else{

            $sRequest = preg_replace('!'.$this->sURI.'(.*)$!i',  '$1', $_SERVER['REQUEST_URI']);
            if(substr($sRequest, 0, 1)=='?')
                $sRequest = substr($sRequest, 1);
            
            $sGets = $sRequest;

            $sGets = str_replace("index.php?", "", $sGets);
            parse_str($sGets, $output);

            $_GET['NAME_CONTROLLER'] = !empty($output['NAME_CONTROLLER'])?$output['NAME_CONTROLLER']:$routerConfig->get('NAME_CONTROLLER');;    
            $_GET['NAME_MODEL'] = !empty($output['NAME_MODEL'])?$output['NAME_MODEL']:$routerConfig->get('NAME_MODEL');;
            
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
                            $sVars .= '&'.preg_replace_callback('!\[(.+?)\]!i', '[$1_'.$i.']', $v[1]);
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
}