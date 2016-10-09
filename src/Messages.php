<?php
namespace Dframe;
use Dframe\BaseException;
use Dframe\Session;
use Dframe\Router;

/**
 * Session-Based Flash Messages
 * 
 * Original @author Mike Everhart
 * @author Sławomir Kaleta
 *
 */

class Messages 
{

    public $msgId;
    public $msgTypes = array('help', 'info', 'warning', 'success', 'error');

    
    public function __construct(Session $session) {
        $this->session = $session;

        // Generate a unique ID for this user and session
        $this->msgId = md5(uniqid());

        $keyExists = $this->session->keyExists('flash_messages');
        if($keyExists == false)
            $this->session->set('flash_messages', array());
    
    }
    
    /**
     * Add a message to the queue
     * 
     * @param  string   $type           The type of message to add
     * @param  string   $message        The message
     * @param  string   $redirect    (optional) If set, the user will be redirected to this URL
     * @return  bool 
     * 
     */

    public function add($type, $message, $redirect=null) {

        if(!isset($type) OR !isset($message[0])) 
            return false;

        // Replace any shorthand codes with their full version
        if( strlen(trim($type)) == 1 )
            $type = str_replace(array('h', 'i', 'w', 'e', 's'), array('help', 'info', 'warning', 'error', 'success'), $type);
        
        try {
            if(!in_array($type, $this->msgTypes))  // Make sure it's a valid message type
                throw new BaseException('"' . strip_tags($type) . '" is not a valid message type!' , 501);

        } catch(BaseException $e) {
            if(ini_get('display_errors') == "on"){
                echo $e->getMessage().'<br />
                File: '.$e->getFile().'<br />
                Code line: '.$e->getLine().'<br /> 
                Trace: '.$e->getTraceAsString();
                exit();
            }

            $routerConfig = Config::load('router');
            header("HTTP/1.0 501 Not Implemented");
            echo $e->getMessage();
            exit();
        }

        $get = $this->session->get('flash_messages');
        $get[$type][] = $message;
        $this->session->set('flash_messages', $get);

        if(!is_null($redirect)) {
            $router = new Router();
            $router->redirect($redirect);
            exit();
        }
        
        return true;
    }
    
    //-----------------------------------------------------------------------------------------------
    // display()
    // print queued messages to the screen
    //-----------------------------------------------------------------------------------------------
    /**
     * Display the queued messages
     * 
     * @param  string   $type     Which messages to display
     * @param  bool     $print    True  = print the messages on the screen
     * @return mixed              
     * 
     */

    public function display($type='all', $print=false) {
        $messages = '';
        $data = '';
        
        if($type == 'g' OR $type == 'growl'){
            $this->displayGrowlMessages();
            return true;
        }
        
        // Print a certain type of message?
        if(in_array($type, $this->msgTypes)){

            $flashMessages = $this->session->get('flash_messages');
            foreach($flashMessages[$type] as $msg ){
                $messages .= $msg;
            }

            $data .= $messages;
            
            // Clear the viewed messages
            $this->clear($type);
        
        // Print ALL queued messages
        }elseif( $type == 'all' ){
            $flashMessages = $this->session->get('flash_messages');
            foreach($flashMessages as $type => $msgArray ){
                $messages = '';
                foreach( $msgArray as $msg ) {
                    $messages .= $msg;  
                }
                $data .= $messages;
            }
            
            // Clear ALL of the messages
            $this->clear();
        
        // Invalid Message Type?
        }else 
            return false;
        
        // Print everything to the screen or return the data
        if($print)
            echo $data; 
        else
            return $data;
    }
    
    
    /**
     * Check to  see if there are any queued error messages
     * 
     * @return bool  true  = There ARE error messages
     *               false = There are NOT any error messages
     * 
     */

    public function hasErrors() {
        $flashMessages = $this->session->get('flash_messages');
        return empty($flashMessages['error']) ? false : true;   
    }
    
    /**
     * Check to see if there are any ($type) messages queued
     * 
     * @param  string   $type     The type of messages to check for
     * @return bool               
     * 
     */
    public function hasMessages($type=null) {
        if(!is_null($type)){
            $flashMessages = $this->session->get('flash_messages');
            if(!empty($flashMessages[$type])) 
                return $flashMessages[$type];   

        }else {
            $flashMessages = $this->session->get('flash_messages');
            foreach($this->msgTypes as $type){
                if(!empty($flashMessages[$type])) 
                    return true;    
            }
        }

        return false;
    }
    
    /**
     * Clear messages from the session data
     * 
     * @param  string   $type     The type of messages to clear
     * @return bool 
     * 
     */
    public function clear($type='all') { 
        if($type == 'all')
            $this->session->remove('flash_messages');
        else{
            $flashMessages = $this->session->get('flash_messages');
            unset($flashMessages[$type]);
            $this->session->set('flash_messages', $flashMessages);
        }
        
        return true;
    }
    
    public function __toString() { 
        return $this->hasMessages();    
    }

    public function __destruct() {
        $this->clear();
    }

}