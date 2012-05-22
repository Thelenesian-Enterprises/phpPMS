<?php
//Copyright (c) 2012 Rubén Domínguez
//  
//This file is part of phpPMS.
//
//phpPMS is free software: you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation, either version 3 of the License, or
//(at your option) any later version.
//
//phpPMS is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with Foobar.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Includes and class autoloader
 *
 * @author nuxsmin
 * @version 0.9b
 * @link http://www.cygnux.org/phppms
 */

if ( ! defined('PMS_ROOT') ) {
   die("Sorry. You can't access directly to this file");
}

$fileName = dirname(__FILE__)."/db.class.php";
    
function class_autoload ($classname) {
    $class = strtolower($classname);
    $classfile = dirname(__FILE__)."/$class.class.php";
    
    if (file_exists($classfile)) {
        include_once ($classfile);
    }
}

include_once (PMS_ROOT."/inc/sesion.php");

// PHP 5 >= 5.1.2
spl_autoload_register("class_autoload");

Config::getFileConfig();
?>
