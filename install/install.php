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

define('PMS_ROOT', '..');

include_once (PMS_ROOT."/inc/config.class.php");
include_once (PMS_ROOT."/inc/common.class.php");

// Función para crear el archivo de configuración de la BBDD
function createDbFile() {
    global $CFG_DB;
  
    $filePath = dirname(__FILE__)."/".PMS_ROOT."/inc";
    $fileName = $filePath."/db.class.php";
    
    if( file_exists($fileName) ) {
        echo '<TR><TD>El archivo de conexión a la BBDD, ya existe</TD><TD CLASS="result"><span class="altTxtOrange">AVISO</span></TD></TR>';
        return TRUE;
    }
    
    if ( ! $CFG_DB["hostname"] OR ! $CFG_DB["username"] OR ! $CFG_DB["userpass"] OR ! $CFG_DB["dbname"] ){
        echo '<TR><TD>Faltan parámetros para la conexión a la BBDD</TD><TD CLASS="result"><span class="altTxtRed">ERROR</span></TD></TR>';
        return FALSE;
    }

    $DB_str = "<?php\n class DB extends Config {
            \n var \$dbhost = '".$CFG_DB["hostname"]."';
            \n var \$dbuser = '".$CFG_DB["username"]."';
            \n var \$dbpassword = '".$CFG_DB["userpass"]."';
            \n var \$dbname = '".$CFG_DB["dbname"]."';
            \n } \n?>";

    if ( ! is_writable($filePath) ){
        echo '<TR><TD>Los permisos del directorio \''.$filePath.'\' son incorrectos</TD><TD CLASS="result"><span class="altTxtRed">ERROR</span></TD></TR>';
        return FALSE;
    }
    
    if ( $fp = fopen($fileName,'w') ) {
        $fw = fwrite($fp,$DB_str);
        fclose($fp);
        
        echo '<TR><TD>Creando archivo de conexión a la BBDD</TD><TD CLASS="result"><span class="altTxtGreen">OK</span></TD></TR>';
        return TRUE;
    }  else {
        echo '<TR><TD>No es posible crear el archivo de conexión a la BBDD</TD><TD CLASS="result"><span class="altTxtRed">ERROR</span></TD></TR>';
        return FALSE;
    }
}

function checkDB(){
    include_once (PMS_ROOT."/inc/db.class.php");
    
    $objDB = new DB;
    
    @$mysqli = new mysqli($objDB->dbhost, $objDB->dbuser, $objDB->dbpassword, $objDB->dbname);
    
    if ( $mysqli->connect_errno ){
        echo '<TR><TD>No es posible conectar con la BBDD: <br />'.$mysqli->connect_errno.' - '.$mysqli->connect_error.'</TD><TD CLASS="result"><span class="altTxtRed">ERROR</span></TD></TR>';
        unset($mysqli);
        unset($objDB);
        return FALSE;
    } else{
        echo '<TR><TD>Conexión con la BBDD '.$objDB->dbuser.'@'.$objDB->dbhost.' -> '.$objDB->dbname.'</TD><TD CLASS="result"><span class="altTxtGreen">OK</span></TD></TR>';
        unset($mysqli);
        unset($objDB);        
        return TRUE;
    }
    
}

function checkFileConfig(){
    if ( file_exists("config.ini")){
        if ( Config::getFileConfig(dirname(__FILE__)) ) {
            echo '<TR><TD>Archivo de configuración procesado</TD><TD CLASS="result"><span class="altTxtGreen">OK</span></TD></TR>';
        } else {
            echo '<TR><TD>Error al procesar el archivo de configuración \'config.ini\'</TD><TD CLASS="result"><span class="altTxtRed">ERROR</span></TD></TR>';
            return FALSE;
        }
    } elseif ( file_exists(dirname(__FILE__)."/".PMS_ROOT."/config.ini") ){
        if ( Config::getFileConfig(dirname(__FILE__)."/".PMS_ROOT."/config.ini") ){
            echo '<TR><TD>Archivo de configuración procesado</TD><TD CLASS="result"><span class="altTxtGreen">OK</span></TD></TR>';
        } else {
            echo '<TR><TD>Error al procesar el archivo de configuración \'config.ini\'</TD><TD CLASS="result"><span class="altTxtRed">ERROR</span></TD></TR>';
            return FALSE;
        }
    } else {
        echo '<TR><TD>El archivo de configuración \'config.ini\', no existe</TD><TD CLASS="result"><span class="altTxtRed">ERROR</span></TD></TR>';
        return FALSE;
    }
    
    return TRUE;
}

function checkVersion(){
    preg_match("/(^\d\.\d)\..*/",PHP_VERSION, $version);

    if ( $version[1] >= 5.1 ){
        echo '<TR><TD>Versión PHP  \''.$version[0].'\'</TD><TD CLASS="result"><span class="altTxtGreen">OK</span></TD></TR>';
        return TRUE;
    } else {
        echo '<TR><TD>Versión PHP  \''.$version[0].'\', requerida >= 5.1</TD><TD CLASS="result"><span class="altTxtRed">ERROR</span></TD></TR>';
        return FALSE;
    }    
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
    <HEAD>
        <TITLE>Instalación phpPMS</TITLE>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <LINK REL="icon" TYPE="image/png" HREF="'.PMS_ROOT.'/imgs/logo.png">
        <LINK REL="stylesheet" HREF="'.PMS_ROOT.'/css/styles.css" TYPE="text/css">
    </HEAD>
    <BODY>
        <DIV ID="install" ALIGN="center">
        <DIV ID="logo" CLASS="round"><IMG SRC="../imgs/logo.png" />Instalación phpPMS</DIV>
        <TABLE ID="tblInstall">';

if ( checkVersion() AND checkFileConfig() AND createDbFile() ) {
    
    if ( checkDB() ) { 
    
        $modsError = 0;
        $modsAvail = get_loaded_extensions();
        $modsNeed = array("mysql","ldap","mcrypt","curl","SimpleXML");

        foreach($modsNeed as $module){
            if ( in_array($module, $modsAvail) ){
                echo '<TR><TD>Módulo \''.$module.'\'</TD><TD CLASS="result"><span class="altTxtGreen">OK</span></TD></TR>';
            } else {
                echo '<TR><TD>Módulo \''.$module.'\'</TD><TD CLASS="result"><span class="altTxtRed">ERROR</span></TD></TR>';
                $modsError++;
            }
        }            

        if ( $modsError > 0 ){
            echo '<TR><TD>Módulos requeridos no disponibles. Abortado</TD><TD CLASS="result"><span class="altTxtRed">ERROR</span></TD></TR>';
        } else {        
            $objConfig = new Config;

            if ( ! preg_match("/^\/phppms\//", $_SERVER["REQUEST_URI"],$matches) ){
                echo '<TR><TD><span class="altTxtBlue">No está utilizando la URL por defecto "/phppms". Debe de modificar la variable "phppms_root" del archivo "javascript/funxtions.js"</span></TD><TD CLASS="result"><span class="altTxtOrange">AVISO</span></TD></TR>';

            }

            if ( file_exists("upgrade.sql") ){
                echo '<TR><TD><span class="altTxtBlue">Si está actualizando, es necesario ejecutar antes: <br /> \'mysql -u root -p < install/upgrade.sql\' desde la consola </span></TD><TD CLASS="result"><span class="altTxtOrange">AVISO</span></TD></TR>';
            }

            if ( $objConfig->mkInitialConfig(dirname(__FILE__),$_GET["upgrade"]) ){
                echo '<TR><TD>Configuración del entorno finalizada</TD><TD CLASS="result"><span class="altTxtGreen">OK</span></TD></TR>';
            }

            if ( file_exists("config.ini") || file_exists(PMS_ROOT."/config.ini") ){
                echo '<TR><TD>Por seguridad, guarde y elimine el archivo config.ini</TD><TD CLASS="result"><span class="altTxtOrange">AVISO</span></TD></TR>';
            }

            echo '<TR><TD COLSPAN="2" STYLE="text-align: center;font-weight: bold;font-size: 12px;"><A HREF="'.PMS_ROOT.'/login.php">Pulse aquí para acceder</A></TD></TR>';
        }
    }
    
    echo '</DIV></BODY></HTML>';
}
?>