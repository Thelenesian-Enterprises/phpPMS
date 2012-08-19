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
// along with phpPMS.  If not, see <http://www.gnu.org/licenses/>.


/**
 *
 * @author nuxsmin
 * @link http://www.cygnux.org/phppms
 * 
 */

Header("content-type: application/x-javascript");

define('PMS_ROOT', '..');

$jsLang = ( $_GET["lang"] ) ? $_GET["lang"] : "es_ES";
$pmsRoot = $_GET["root"];
$arrJsLang = "";

include_once (PMS_ROOT."/locales/".$jsLang.".php");;

foreach ( $LANG["js"] as $langIndex => $langDesc ){
    $arrJsLang .= "'".$langDesc."',";
}

$arrJsLang = trim($arrJsLang,",");

echo "// i18n language $jsLang array from PHP\n";
echo "var LANG = [".$arrJsLang."]; \n\n";

//echo ( $pmsRoot ) ? "var pms_root = '/".$pmsRoot."';\n" : "var pms_root = '/phppms';\n";
echo "var pms_root = '".$pmsRoot."';\n";

include_once 'functions.js';
?>