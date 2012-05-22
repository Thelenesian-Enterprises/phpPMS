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
 *
 * @author nuxsmin
 * @version 0.9b
 * @link http://www.cygnux.org/phppms
 * 
 */

define('PMS_ROOT', '..');

include_once (PMS_ROOT."/inc/constants.inc");
include_once (PMS_ROOT."/inc/config.class.php");
include_once (PMS_ROOT."/inc/common.class.php");

// Función para crear el archivo de configuración de la BBDD
function createDbFile() {
    global $CFG_DB;
  
    $filePath = dirname(__FILE__)."/".PMS_ROOT."/inc";
    $fileName = $filePath."/db.class.php";
    
    if( file_exists($fileName) ) return TRUE;
    
    if ( ! $CFG_DB["hostname"] OR ! $CFG_DB["username"] OR ! $CFG_DB["userpass"] OR ! $CFG_DB["dbname"] ){
        echo "<br />&gt; ERROR: faltan parámetros para la conexión a la BBDD &lt;";
        return FALSE;
    }

    $DB_str = "<?php\n class DB extends Config {
            \n var \$dbhost = '".$CFG_DB["hostname"]."';
            \n var \$dbuser = '".$CFG_DB["username"]."';
            \n var \$dbpassword = '".$CFG_DB["userpass"]."';
            \n var \$dbname = '".$CFG_DB["dbname"]."';
            \n } \n?>";

    if ( ! is_writable($filePath) ){
        echo "<br />&gt; ERROR: los permisos del directorio '$filePath' son incorrectos &lt;";
        return FALSE;
    }
    
    if ( $fp = fopen($fileName,'w') ) {
        $fw = fwrite($fp,$DB_str);
        fclose($fp);
        
        echo "<br />&gt; Archivo de conexión a la BBDD creado correctamente &lt;";
        return TRUE;
    }  else {
        echo "<br />&gt; ERROR: No es posible crear el archivo de conexión a la BBDD &lt;";
        return FALSE;
    }
}

// Función para crear la configuración inicial
function mkInitialConfig(){
    $objConfig = new Config();

    if ( $objConfig->getConfigValue("install") == 1 ){
        echo "<br />&gt; AVISO: Entorno ya instalado &lt;";
        return;
    }
            
    if ( ! $objConfig->getConfigValue("masterPwd") ){
        $objCrypt = new Crypt();
        $hashMPass = $objCrypt->mkHashPassword("0000");
        $objConfig->arrConfigValue["masterPwd"] = $hashMPass;
        
        if ( $objConfig->writeConfig(TRUE) ){
            echo "<br />&gt; Clave maestra inicial establecida a '0000'. Es recomendable cambiarla &lt;";
        } else {
            echo "<br />&gt; ERROR: no se ha podido guardar la clave maestra &lt;";
            return FALSE;
        }
        unset($objCrypt);
    } else {
        echo "<br />&gt; AVISO: Clave maestra ya establecida &lt;";
    }
    
    $strQuery = "INSERT INTO users (vacUName,vacULogin,intUGroupFid,intUProfile,blnIsAdmin,vacUPassword,blnFromLdap) 
                VALUES('PMS Admin','admin',1,0,1,MD5('admin'),0);";
    $resQuery = $objConfig->dbh->query($strQuery);
    
    if ( $resQuery ){
        echo "<br />&gt; Usuario 'admin' con clave 'admin' creado correctamnete &lt;";
    } else {
        echo "<br />&gt; ERROR: no se ha podido crear el usuario 'admin' &lt;";
        return FALSE;
    }
    
    unset($objConfig->arrConfigValue);
    
    $objConfig->arrConfigValue["md5_pass"] = "TRUE";
    $objConfig->arrConfigValue["password_show"] = "TRUE";
    $objConfig->arrConfigValue["account_link"] = "TRUE";
    $objConfig->arrConfigValue["account_count"] = 5;
    $objConfig->arrConfigValue["install"] = 1;
    $objConfig->writeConfig(TRUE);
    
    unset($objConfig);
}

if ( ! Config::getFileConfig(dirname(__FILE__)."/".PMS_ROOT) ) return;

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
    <HEAD>
        <TITLE>'.$CFG_PMS["siteshortname"].' - '.$CFG_PMS["sitename"].'</TITLE>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <LINK REL="icon" TYPE="image/png" HREF="'.PMS_ROOT.'/imgs/logo.png">
        <LINK REL="stylesheet" HREF="'.PMS_ROOT.'/css/styles.css" TYPE="text/css">
    </HEAD>
    <BODY>
        <DIV ID="install">
            &gt; Configurando entorno...  &lt;';

if ( createDbFile() ) {
    include_once (PMS_ROOT."/inc/db.class.php");
    include_once (PMS_ROOT."/inc/crypt.class.php");
    
    mkInitialConfig();
}

echo '<br />&gt; Configuración del entorno finalizada &lt;
    <br /><br /><a href="'.PMS_ROOT.'/login.php">Pulse aquí para acceder</a>
    </DIV></BODY></HTML>';
?>