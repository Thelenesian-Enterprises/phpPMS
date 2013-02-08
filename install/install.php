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

define('PMS_ROOT', '..');
define('PMS_VERSION', '0.971b');

include_once (PMS_ROOT."/inc/crypt.class.php");
include_once (PMS_ROOT."/inc/config.class.php");
include_once (PMS_ROOT."/inc/common.class.php");
include_once (PMS_ROOT."/inc/install_functions.php");

$step = ( isset($_POST["step"]) ) ? $_POST["step"] : 1;
$submit = ( isset($_POST["submit"]) ) ? $_POST["submit"] : "";
$instLang = "";

if ( isset($_POST["instLang"]) ){
    if ( $_POST["instLang"] == 1 ){
        $instLang = $_POST["submit"];
    } else {
        $instLang = $_POST["instLang"];
    }
    
    switch ($instLang){
        case "Español":
            include_once (PMS_ROOT."/locales/es_ES.php");
            break;
        case "English":
            include_once (PMS_ROOT."/locales/en_US.php");
            break;
    }
} else {
    include_once (PMS_ROOT."/locales/es_ES.php");
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
    <HEAD>
        <TITLE>'.$LANG['install'][25].'</TITLE>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <LINK REL="icon" TYPE="image/png" HREF="'.PMS_ROOT.'/imgs/logo.png">
        <LINK REL="stylesheet" HREF="'.PMS_ROOT.'/css/styles.css" TYPE="text/css">
    </HEAD>
    <BODY>
        <DIV ID="install" ALIGN="center">
        <DIV ID="logo" CLASS="round"><IMG SRC="../imgs/logo.png" />'.$LANG['install'][25].' '.PMS_VERSION.'</DIV>';

if ( $instLang == "" ){
    echo '<FORM METHOD="post" NAME="frmConfig" ID="frmConfig" ACTION="install.php" />';
    echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][49].'" />';
    echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][50].'" />';
    echo '<INPUT TYPE="hidden" NAME="instLang" VALUE="1" />';
    echo '</FORM>';
} else {
    installProcess($step,$instLang,$submit);
} 

echo '</DIV></BODY></HTML>';
?>