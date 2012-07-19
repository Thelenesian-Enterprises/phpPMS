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
 * @version 0.951b
 * @link http://www.cygnux.org/phppms
 * 
 */

define('PMS_ROOT', '..');
define('PMS_VERSION', '0.953b');

include_once (PMS_ROOT."/inc/crypt.class.php");
include_once (PMS_ROOT."/inc/config.class.php");
include_once (PMS_ROOT."/inc/common.class.php");
include_once (PMS_ROOT."/inc/install_functions.php");

if ( isset($_POST["step"]) ) $step = $_POST["step"];
if ( isset($_POST["submit"]) ) $submit = $_POST["submit"];
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

if ( ! $instLang ){
    echo '<FORM METHOD="post" NAME="frmConfig" ID="frmConfig" ACTION="install.php" />';
    echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][49].'" />';
    echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][50].'" />';
    echo '<INPUT TYPE="hidden" NAME="instLang" VALUE="1" /></FORM>';
} elseif( ! $step ){
    echo '<TABLE ID="tblInstall">';
    if ( checkPhpVersion() && checkModules() ){
        $filePath = dirname(__FILE__)."/".PMS_ROOT."/inc";
        $fileName = $filePath."/db.class.php";
        
        if ( ! preg_match("/^\/phppms\//", $_SERVER["REQUEST_URI"]) ){
            printMsg($LANG['install'][19], 2);
        }
        
        if ( ! is_writable($filePath) ){
            printMsg($LANG['install'][9]." ('$filePath')", 2);
        }
    
        if (checkDBFile() ){
            printMsg($LANG['install'][7], 2);
        }
        
        printMsg($LANG['install'][20],2);
        
        echo '</TABLE>';
        echo '<FORM METHOD="post" NAME="frmConfig" ID="frmConfig" ACTION="install.php" />';
        echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][26].'" />';
        echo '<INPUT TYPE="hidden" NAME="instLang" VALUE="'.$instLang.'" />';
        echo '<INPUT TYPE="hidden" NAME="step" VALUE="2" /></FORM>';
    }
}

if ( $step == 2 ) mkDbForm();       

if ( $step == 3 ){
    echo '<TABLE ID="tblInstall">';
    
    if ( $submit == $LANG["install"][27] ){
        if ( ! checkDBFile() AND ( ! $_POST["dbhost"] OR ! $_POST["dbuser"] OR ! $_POST["dbpass"] OR ! $_POST["dbname"] ) ){
            printMsg($LANG["install"][51],1);
        } else {
            if ( ! checkDBFile() ) {
                if ( checkDB(TRUE, FALSE) ) {
                    createDbFile();
                }
            }
            
            if ( checkDBFile() AND checkDB() AND updateDB() ){
                updateVersion();
                printMsg($LANG["install"][24]);
                echo '</TABLE>';
                echo '<DIV ID="access"><A CLASS="round" HREF="'.PMS_ROOT.'/login.php">'.$LANG['install'][23].'</A></DIV>';
                $isOk = TRUE;
            }
        }
    } elseif ( $submit == $LANG["install"][26] ){
        if ( createDbFile() ){
            if ( createDB() ){
                echo '</TABLE>';
                echo '<FORM METHOD="post" NAME="frmConfig" ID="frmConfig" ACTION="install.php" />';
                echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG["install"][26].'" />';
                echo '<INPUT TYPE="hidden" NAME="instLang" VALUE="'.$instLang.'" />';
                echo '<INPUT TYPE="hidden" NAME="step" CLASS="button round" VALUE="4" /></FORM>';
                $isOk = TRUE;
            }
        }
    }
    
    if ( ! $isOk ){
        echo '</TABLE>';
        printBack(2);
    }
}

if ( $step == 4 ) mkConfigForm();

if ( $step == 5 ){
    echo '<TABLE ID="tblInstall">';

    if ( checkDB() ) {
        
        if ( file_exists("config.ini") || file_exists(PMS_ROOT."/config.ini") ){
            printMsg($LANG['install'][22], 2);
        }
        
        $objConfig = new Config;
        
        if ( $objConfig->mkInitialConfig($_POST) ){
            printMsg($LANG['install'][21]);
            echo '</TABLE>';
            echo '<DIV ID="access"><A CLASS="round" HREF="'.PMS_ROOT.'/login.php">'.$LANG['install'][23].'</A></DIV>';
        } else {
            printMsg($LANG['install'][39], 1);
            echo '</TABLE>';
            printBack(4);
        }
    }
}

echo '</DIV></BODY></HTML>';
?>