<?php
// Copyright (c) 2012 Rubén Domínguez
//  
// This file is part of phpPMS.
//
// phpPMS is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// phpPMS is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Foobar.  If not, see <http://www.gnu.org/licenses/>.


/**
 *
 * @author nuxsmin
 * @version 0.91b
 * @link http://www.cygnux.org/phppms
 * 
 */

function initError($str){
    $htmlOut = "<html><head><title>phpPMS :: ERROR</title></head><body>";
    $htmlOut .= "<div align='center' style='width:60%;margin:auto;padding:15px;border:1px solid red;background-color:#fee8e6;color:red;line-height:2em;font-family:Verdana,Helvetica,Arial;'>Ooops...<br />".$str."</div>";
    $htmlOut .= "</body></html>";
    
    header("Content-Type: text/html; charset=UTF-8");
    die($htmlOut);
}

if ( ! defined('PMS_ROOT') ) initError("No es posible acceder directamente a este archivo<br />You can't access directly to this file");

$fileName = dirname(__FILE__) . "/db.class.php";

function class_autoload($classname) {
    $class = strtolower($classname);
    $classfile = dirname(__FILE__) . "/$class.class.php";

    if (file_exists($classfile)) {
        include_once ($classfile);
    }
}

include_once (PMS_ROOT . "/inc/sesion.php");

spl_autoload_register("class_autoload");

$objConfig = new Config;

if ( ! $objConfig->getDBConfig() ) initError("No se ha podido cargar la configuración<br />Configuration can not be loaded");

define('PMS_VERSION', $objConfig->getConfigValue("version"));
define('PMS_ROOTURL', $objConfig->getConfigValue("siteroot"));
define('PMS_LANG', $objConfig->getConfigValue("sitelang"));

$langFile = PMS_ROOT . "/locales/".PMS_LANG.".php";

if ( ! file_exists($langFile) ) initError("Archivo de idioma no encontrado<br />Language file not found");

include_once ($langFile);

unset($objConfig);
?>