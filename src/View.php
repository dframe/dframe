<?php
namespace Dframe;
use Dframe\BaseException;

/**
 * Copyright (C) 2016  
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


abstract class View extends Core implements \Dframe\View\interfaceView
{
    public function __construct($baseClass){
        parent::__construct($baseClass);
        
        if(!isset($this->view))
           throw new BaseException('Please Define view engine in app/View.php', 500);
    }

    public function assign($name, $value) {
        return $this->view->assign($name, $value);
    }

    public function render($data, $type = null){

        if(empty($type) OR $type == 'html')
            $this->view->renderInclude($data);
        elseif($type == 'jsonp')
            $this->view->renderJSONP($data);
        else
            $this->view->renderJSON($data);
               
    } 

    /**
     * Przekazuje kod do szablonu
     *
     * @param string $name Nazwa pliku
     * @param string $path Ścieżka do szablonu
     *
     * @return void
     */

    public function fetch($name, $path=null) {
        return $this->view->fetch($name, $path=null);

    }

    /**
     * Include pliku
     */
    public function renderInclude($name){
        return $this->view->renderInclude($name);
    }
     
    /**
     * Wyświetla dane JSON.
     * @param array $data Dane do wyświetlenia
     */
    public function renderJSON($data) {
        return $this->view->renderJSON($data);

    }
 
    /**
     * Wyświetla dane JSONP.
     * @param array $data Dane do wyświetlenia
     */
    public function renderJSONP($data) {
        return $this->view->renderJSONP($data);
    }
}