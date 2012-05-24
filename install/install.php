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

if ( file_exists("config.ini")){
    Config::getFileConfig(dirname(__FILE__));
} elseif ( file_exists(dirname(__FILE__)."/".PMS_ROOT."/config.ini") ){
    Config::getFileConfig(dirname(__FILE__));
} else {
    header("Content-Type: text/html; charset=UTF-8");
    die("El archivo de configuración no existe.");
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
    <HEAD>
        <TITLE>'.$CFG_PMS["siteshortname"].' - '.$CFG_PMS["sitename"].'</TITLE>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <LINK REL="icon" TYPE="image/png" HREF="'.PMS_ROOT.'/imgs/logo.png">
        <LINK REL="stylesheet" HREF="'.PMS_ROOT.'/css/styles.css" TYPE="text/css">
    </HEAD>
    <BODY>
        <DIV ID="install" ALIGN="center">
            <H2>Instalación phpPMS</H2>
            &gt; Configurando entorno...  &lt;';

if ( createDbFile() ) {
    include_once (PMS_ROOT."/inc/db.class.php");
    
    $objConfig = new Config;
    
    if ( $objConfig->mkInitialConfig(dirname(__FILE__),$_GET["upgrade"]) ){
        echo '<br />&gt; <span class="altTxtGreen">Configuración del entorno finalizada</span> &lt;';
    } else {
        echo '<br />&gt; <span class="altTxtRed">Configuración del entorno finalizada con errores</span> &lt;';
    }
    
    if ( file_exists("config.ini") || file_exists(PMS_ROOT."/config.ini") ){
        echo '<br />&gt; Por seguridad, elimine el archivo config.ini &lt;';
    }
    
    if ( file_exists("upgrade.sql") ){
        echo '<br />&gt; Para actualizar la BBDD, ejecute \'mysql -u root -p < install/upgrade.sql\' desde la consola &lt;';
    }
    
    echo '<br /><br /><a href="'.PMS_ROOT.'/login.php">Pulse aquí para acceder</a>
        </DIV></BODY></HTML>';
}
?>