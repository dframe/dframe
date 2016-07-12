<?php
use Dframe\Loader;

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

include_once '../vendor/autoload.php';
include_once '../app/Bootstrap.php';
$bootstrap = new Bootstrap();

$loader = new Loader($bootstrap);
$controller = $loader->CreateController(); # Loading Controller class


if(method_exists($controller, 'start'))
    $controller->start();

if(method_exists($controller, 'init'))
    $controller->init();

if(method_exists($controller, $_GET['action']))
    $controller->$_GET['action']();
else 
    if(method_exists($controller, 'page'))
	    $controller->page();

if(method_exists($controller, 'end'))
    $controller->end();