<?php
use Dframe\Session;
use Dframe\Messages;
use Dframe\Token;
use Dframe\Database\Database;

include_once 'config.php';
class Bootstrap
{
    
    public function __construct() {
        try {
            if(!empty(DB_HOST)){
                $dbConfig = array(
                    'host' => DB_HOST, 
                    'dbname' => DB_DATABASE, 
                    'username' => DB_USER, 
                    'password' => DB_PASS,
                );
                $this->db = new Database($dbConfig);
                $this->db->setErrorLog(false); // Debugowanie
            }
        }
        
        catch(DBException $e) {
            echo 'The connect can not create: ' . $e->getMessage();
            exit();
        }

        $this->session  = new Session(SALT);
        $this->msg = new Messages();
        $this->token  = new Token($this->session);

        return $this;
    }

}