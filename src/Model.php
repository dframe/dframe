<?php
namespace Dframe;

/*
Copyright (C) 2015  Sławomir Kaleta

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

abstract class Model extends Core
{
    
    public function methodResult($type, $array = null){
        if(!is_null($array))
            return array_merge(array('return' => $type), $array);
 
        return array('return' => $type);
    }

    public function methodFail($errors = null){
        if($errors === null){
            return $this->methodResult(false);
        }
        if(!is_array($errors)){
            $errors = array($errors);
        }

    	return $this->methodResult(false, array('errors' => $errors));
    }
      
    public function init() {}
}