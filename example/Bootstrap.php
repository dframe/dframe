<?php
/*
Copyright (C) 2015 Sławomir Kaleta

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

class Bootstrap
{
    
    public function __construct() {
        try {
            include_once 'config.php';
            $DB_USER = DB_USER;
            if(!empty($DB_USER)){
                $dbConfig = array(
                    'host' => DB_HOST, 
                    'dbname' => DB_DATABASE, 
                    'username' => DB_USER, 
                    'password' => DB_PASS,
                );
                $this->db = new \Dframe\Core\Database\Database($dbConfig);
            }
        }
        catch(DBException $e) {
            echo 'The connect can not create: ' . $e->getMessage();
        }

        
        $this->session  = new \Dframe\Session(SALT);
        $this->msg = new \Dframe\Messages();
        $this->token  = new \Dframe\Token($this->session);

        return $this;
    }


}
?>