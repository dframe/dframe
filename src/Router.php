<?php
namespace Dframe;
//http://download.hernas.pl/

class Router extends Core
{

    public $aRouting;
    private $aRoutingParse;
    private $sURI;
    private $parsingArray;

	public function __construct($bootstrap){

		$aURI = explode('/', $_SERVER['SCRIPT_NAME']);
		
		array_pop($aURI);
		$this->sURI = implode('/', $aURI).'/';

		$this->aRouting = $this->loadConfig('router')->get();
		$this->aRoutingParse = $this->loadConfig('router')->get();

	}
    
    // string||array (folder,)controller/action 
    public function isActive($url) {
        if(is_array($url)) {
            foreach($url as $oneurl) {
                if(strpos($oneurl, '/'))
                    list($task, $action) = explode('/', $oneurl);
                else
                    $task = $oneurl;
                    
                if(!empty($action)) {
                    if($task == $this->aRouting['task'] && $action == $this->aRouting['action'])
                        return true;
                } else {
                    if($task == $this->aRouting['task'])
                        return true;
                }
            }
            return false;
        } else {
            if(strpos($url, '/'))
                list($task, $action) = explode('/', $url);
            else
                $task = $url;
                
            if(!empty($action))
                return ($task == $this->aRouting['task']) && ($action == $this->aRouting['action']);

            return ($task == $this->aRouting['task']);
        }
    }
    
	public function makeUrl($sUrl = null)
	{
		$aParamsHook = explode('#', $sUrl);
		$aParams = explode('?', $aParamsHook[0]);
		$aParams_ = explode('/', $aParams[0]);
		$sAction = $aParams_[0];
		if (isset($aParams_[1])) 
			$sModel = $aParams_[1];
		else
			$sModel = null;

		if (isset($aParams[1])) 
			parse_str($aParams[1], $aParams);
		else 
			$aParams = array();
		
		if(MOD_REWRITE){

            $sExpressionUrl = $sAction.'/'.$sModel;
            if(!empty($aParams)) {
                $sExpressionUrl .= '?';
                foreach($aParams AS $k => $v)
                {
                    $test[] = $k.'='.$v;
                }
                $sExpressionUrl .= implode('&', $test);
            }
		} else {
			$sExpressionUrl = 'index.php?task='.$sAction.'&action='.$sModel;
			foreach($aParams AS $k => $v){
				$sExpressionUrl .= '&'.$k.'='.$v;
			}

		}
		$sUrl = 'http://' . $_SERVER['HTTP_HOST'] . $this->sURI;
		$sUrl .= $sExpressionUrl;
		
		return $sUrl;
	}
	private function parseParams($sRouting, $aParams){
		$sReturn = null;
		foreach($aParams AS $key => $value)
		{
			$sReturn .= str_replace(array('[name]', '[value]'), array($key, $value), $sRouting);
		}
		return $sReturn;
	}

	public function parseGets(){
		if(MOD_REWRITE){

			$sRequest = preg_replace('!'.$this->sURI.'(.*)$!i',  '$1', $_SERVER['REQUEST_URI']);
			if(substr($sRequest, -1)!='/'){
				$sRequest .= '/';
			}

			$sGets = $this->parseUrl($sRequest);
			parse_str($sGets, $aGets);
			$_GET['task'] = !empty($aGets['task'])?$aGets['task']:$this->loadConfig('router')->get('NAME_CONTROLLER');;	
			unset($aGets['task']);
			$_GET['action'] = !empty($aGets['action'])?$aGets['action']:$this->loadConfig('router')->get('NAME_MODEL');;
			unset($aGets['action']);
			$_GET = array_merge($_GET, $aGets);

		}else{

			$sRequest = preg_replace('!'.$this->sURI.'(.*)$!i',  '$1', $_SERVER['REQUEST_URI']);
			if(substr($sRequest, 0, 1)=='?'){
				$sRequest = substr($sRequest, 1);
			}

			$sGets = $sRequest;

            $sGets = str_replace("index.php?", "", $sGets);
            parse_str($sGets, $output);

			$_GET['task'] = !empty($output['task'])?$output['task']:$this->loadConfig('router')->get('NAME_CONTROLLER');;	
			$_GET['action'] = !empty($output['action'])?$output['action']:$this->loadConfig('router')->get('NAME_MODEL');;
			
		}
	}

	private function parseUrl($sRequest){   
		$sVars = null;
		foreach($this->aRoutingParse AS $k => $v){
			
			preg_match_all('!\[(.+?)\]!ie', $v[0], $aExpression_);
			$sExpression = preg_replace('!\[(.+?)\]!ie', '$this->transformParam(\'$1\', \''.$k.'\')', $v[0]);
			if(preg_match_all('!'.$sExpression.'!i', $sRequest, $aExpression__))
			{
				foreach($aExpression__ AS $k_ => $v_)
				{
					foreach($v_ AS $kkk => $vvv)
					{
						if(!isset($aExpression_[1][$k_-1]))
						{
							$aExpression_[1][$k_-1] = null;
						}
						if($kkk>0)
						{	
							$aExpression[] = array($aExpression_[1][$k_-1].'_'.$kkk, $vvv);
						} else {
							$aExpression[] = array($aExpression_[1][$k_-1], $vvv);
						}
					}
				}
				unset($aExpression[0]);
				$iCount = count($aExpression__[0]);
				if($iCount>1)
				{
					for($i=0;$i<$iCount;$i++)
					{
						if($i>0)
						{
							$sVars .= '&'.preg_replace('!\[(.+?)\]!i', '[$1_'.$i.']', $v[1]);
						} else {
							$sVars = '&'.$v[1];						
						}
					}
				} else {				
					$sVars = '&'.$v[1];
				}
				foreach($aExpression AS $k => $v_)
				{
					if(!isset($v['_'.$v_[0]]))
					{
						$v['_'.$v_[0]] = null;
					}
					if(!is_array($v['_'.$v_[0]]))
					{
						
						$sVars = str_replace('['.$v_[0].']', $v_[1], $sVars);
						
					} else {
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
		if(isset($this->aRouting[$k][$sParam]) && !is_array($this->aRouting[$k][$sParam])){
			return $this->aRouting[$k][$sParam];
		}else 
		    return '(.+?)';
		
	}
}
?>