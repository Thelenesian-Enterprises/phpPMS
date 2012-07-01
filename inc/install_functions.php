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
// along with phpPMS. If not, see <http://www.gnu.org/licenses/>.


/**
 *
 * @author nuxsmin
 * @version 0.91b
 * @link http://www.cygnux.org/phppms
 * 
 */

define('PMS_ROOT', '..');

// Función para imprimir los mensajes
function printMsg($msg,$status = 0){
    global $LANG;

    switch ($status){
        case 0:
            echo '<TR><TD>'.$msg.'</TD><TD CLASS="result"><span class="altTxtGreen">'.strtoupper($LANG['common'][6]).'</span></TD></TR>';
            break;
        case 1:
            echo '<TR><TD>'.$msg.'</TD><TD CLASS="result"><span class="altTxtRed">'.strtoupper($LANG['common'][5]).'</span></TD></TR>';
            break;
        case 2:
            echo '<TR><TD>'.$msg.'</TD><TD CLASS="result"><span class="altTxtOrange">'.strtoupper($LANG['common'][4]).'</span></TD></TR>';
            break;
    }
}

// Función para crear el archivo de configuración de la BBDD
function createDbFile() {
    global $LANG;
    
    $dbhost = $_POST["dbhost"];
    $dbuser = $_POST["dbuser"];
    $dbpass = $_POST["dbpass"];
    $dbname = $_POST["dbname"];
    
    $filePath = dirname(__FILE__)."/".PMS_ROOT."/inc";
    $fileName = $filePath."/db.class.php";
  
//    if ( checkDBFile() ) {
//        printMsg($LANG['install'][7], 2);
//        return TRUE;
//    }
    
    if ( ! $dbhost OR ! $dbuser OR ! $dbpass OR ! $dbname ){
        printMsg($LANG['install'][8], 1);
        return FALSE;
    }

    $DB_str = "<?php\n class DB extends Config {\n\tvar \$dbhost = '".$dbhost."';\n\tvar \$dbuser = '".$dbuser."';\n\tvar \$dbpassword = '".$dbpass."';\n\tvar \$dbname = '".$dbname."';\n }\n?>";

    if ( ! is_writable($filePath) ){
        printMsg($LANG['install'][9]." ('$filePath')", 1);
        return FALSE;
    }
    
    if ( $fp = fopen($fileName,'w') ) {
        $fw = fwrite($fp,$DB_str);
        fclose($fp);
        
        printMsg($LANG['install'][10]);
        return TRUE;
    } else {
        printMsg($LANG['install'][11], 1);
        return FALSE;
    }
}

// Función para comprobar si existe el archivo de conexión a la BBDD
function checkDBFile(){
    $filePath = dirname(__FILE__)."/".PMS_ROOT."/inc";
    $fileName = $filePath."/db.class.php";
    
    if ( file_exists($fileName) ) return TRUE;
    
    return FALSE;
}

// Función para comprobar la conexión a la BBDD
function checkDB($useDB = FALSE){
    global $LANG;
    
    include_once (PMS_ROOT."/inc/db.class.php");
    $objDB = new DB;

    $dbHost = $objDB->dbhost;
    $dbUser = $objDB->dbuser;
    $dbPass = $objDB->dbpassword;
    $dbName = $objDB->dbname;

    unset($objDB);

    if ( $useDB ){
        @$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    } else {
        @$mysqli = new mysqli($dbHost, $dbUser, $dbPass);
    }
    
    if ( $mysqli->connect_errno ){
        printMsg($LANG['install'][12].": <BR />".$mysqli->connect_errno." - ".$mysqli->connect_error, 1);
        unset($mysqli);
        return FALSE;
    } else{
        printMsg($LANG['install'][13]." $dbUser@$dbHost -> $dbName");
        unset($mysqli);
        return TRUE;
    }
}

// Función para actualizar la BBDD
function updateDB(){
    global $LANG;

    $fileName = PMS_ROOT."/install/upgrade_".PMS_ROOT.".sql";
    
    if ( ! file_exists($fileName) ){
        printMsg($LANG['install'][36], 2);
        return TRUE;
    }

    $dbHost = $_POST["dbhost"];
    $dbAdminUser = $_POST["dbadminuser"];
    $dbAdminPass = $_POST["dbadminpass"];
    $dbName = $_POST["dbname"];

    $handle = fopen($fileName, 'rb');

    if ( $handle ) {
        @$mysqli = new mysqli($dbHost,$dbAdminUser,$dbAdminPass);
                
        if ( $mysqli->connect_errno ){
            printMsg($LANG['install'][40]."<BR />".$mysqli->connect_error, 1);
            return FALSE;
        }

        if ( ! $mysqli->query("USE `$dbName`") ){
            printMsg($LANG['install'][41]." '$dbName'<BR />".$mysqli->error,1);
            return FALSE;
        }
        
        while ( ! feof($handle) ) {
            $buffer = stream_get_line($handle, 1000000, ";\n");
            if ( $buffer ){
                if ( ! $mysqli->query($buffer) ){
                    printMsg($LANG['install'][42]."<BR />".$mysqli->error, 1);
                    return FALSE;
                }
            }
        }
        
        printMsg($LANG['install'][43]);
        return TRUE;        
    } else {
        printMsg($LANG['install'][44], 1);
        return FALSE;
    }
}

// Función para crear la BBDD
function createDB(){
    global $LANG;
    
    $dbHost = $_POST["dbhost"];
    $dbAdminUser = $_POST["dbadminuser"];
    $dbAdminPass = $_POST["dbadminpass"];
    $dbUser = $_POST["dbuser"];
    $dbPass = $_POST["dbpass"];
    $dbName = $_POST["dbname"];
        
    @$mysqli = new mysqli($dbHost,$dbAdminUser,$dbAdminPass);
    
    if ( $mysqli->connect_errno ){
        printMsg($LANG['install'][12].": <BR />".$mysqli->connect_errno." - ".$mysqli->connect_error, 1);
        unset($mysqli);
        return FALSE;
    } else {
        // Si existe el archivo de conexión a la BBDD, lo utilizamos
        if ( checkDBFile() ){
            if ( checkDB(FALSE) ){
                include_once (PMS_ROOT."/inc/db.class.php");
                $objDB = new DB;

                $dbHost = $objDB->dbhost;
                $dbUser = $objDB->dbuser;
                $dbPass = $objDB->dbpassword;
                $dbName = $objDB->dbname;

                unset($objDB);

                //printMsg($LANG['install'][38], 2);
            }
        }
        
        // Comprobamos si el usuario para phpPMS existe
        $strQuery = "SELECT User FROM mysql.user WHERE User = '$dbUser' AND Host = '$dbHost'";
        $resQuery = $mysqli->query($strQuery);
        
        if ( ! $resQuery ){
            $strQuery = "CREATE USER '$dbUser'@'$dbHost' IDENTIFIED BY '$dbPass'";
            $resQuery = $mysqli->query($strQuery);

            if ( $resQuery ){
                printMsg($LANG['install'][14].' \''.$dbUser.'@'.$dbHost);
            } else {
                printMsg($LANG['install'][14].' \''.$dbUser.'@'.$dbHost, 1);
                return FALSE;
            }
        } else {
            // Comprobamos que los permisos sean correctos
            printMsg($LANG['install'][22], 2);
            
            $strQuery = "SELECT Select_priv,Insert_priv,Update_priv,Delete_priv,Lock_tables_priv FROM mysql.db WHERE User = '$dbUser' AND Host = '$dbHost'";
            $resQuery = $mysqli->query($strQuery);
            $i = 0;
            
            if ( $resQuery ){
                $resResult = $resQuery->fetch_assoc();
                
                foreach ( $resResult as $perm ){
                    if ( $perm == "N"){
                        $noPerm = 1;
                        break;                        
                    }
                }
                
                if ( $noPerm == 1 ){
                    printMsg($LANG['install'][37], 1);
                }
            }
        }

        if ( $mysqli->select_db($dbName) ){
            printMsg($LANG['install'][45], 1);
            return FALSE;                    
        }
        
        $fileName = PMS_ROOT."/install/phpPMS.sql";        

        if ( ! file_exists($fileName) ){
            printMsg($LANG['install'][46], 1);
            return FALSE;
        }
        
        $strQuery = "CREATE DATABASE `$dbName` DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci;";
        $resQuery = $mysqli->query($strQuery);
        
        if ( ! $resQuery ){
            printMsg($LANG['install'][35]." '$dbName'<BR />".$mysqli->error, 1);
            return FALSE;
        }
        
        if ( ! $mysqli->select_db($dbName) ){
            printMsg($LANG['install'][35]." '$dbName'<BR />".$mysqli->error, 1);
            return FALSE;
        }
        
        // Leemos el archivo SQL para crear las tablas de la BBDD
        $handle = fopen($fileName, 'rb');
        
        if ( $handle ) {
            while ( ! feof($handle) ) {
                $buffer = stream_get_line($handle, 1000000, ";\n");
                if ( $buffer ){
                    if ( ! $mysqli->query($buffer) ){
                        printMsg($LANG['install'][35]." '$dbName'<BR />".$mysqli->error, 1);
                        return FALSE;
                    }
                }
            }
        }

        $strQuery = "GRANT SELECT,INSERT,UPDATE,DELETE,LOCK TABLES ON $dbName.* TO '$dbUser'@'$dbHost'";
        $resQuery = $mysqli->query($strQuery);
        
        if ( ! $resQuery ){
            printMsg($LANG['install'][35]." '$dbName'<BR />".$mysqli->error, 1);
            return FALSE;
        }
        
        printMsg($LANG['install'][15]." '$dbName'");
        return TRUE;
    }
}

// Función para actualizar la versión
function updateVersion(){
    $objConfig = new Config;
    $objConfig->arrConfigValue["version"] = PMS_VERSION;
    $objConfig->writeConfig();
}

// Función para comprobar la versión de PHP
function checkPhpVersion(){
    global $LANG;
    
    preg_match("/(^\d\.\d)\..*/",PHP_VERSION, $version);

    if ( $version[1] >= 5.1 ){
        printMsg($LANG['install'][16]." '".$version[0]."'");
        return TRUE;
    } else {
        printMsg($LANG['install'][16]." '".$version[0]."'", 1);
        return FALSE;
    }    
}

// Función para comprobar los módulos de PHP necesarios
function checkModules(){
    global $LANG;
    
    $modsError = 0;
    $modsAvail = get_loaded_extensions();
    $modsNeed = array("mysql","ldap","mcrypt","curl","SimpleXML");

    echo '<TR><TD>'.$LANG['install'][17].':';

    foreach($modsNeed as $module){
        if ( in_array($module, $modsAvail) ){
            echo '<span class="altTxtGreen"> \''.$module.'\'</span>';
        } else {
            echo '<span class="altTxtRed"> \''.$module.'\'</span>';
            $modsError++;
        }
    }

    if ( $modsError > 0 ){
        echo '</TD><TD CLASS="result"><span class="altTxtRed">'.strtoupper($LANG['common'][5]).'</span></TD></TR>';
        
        printMsg($LANG['install'][18], 1);
        return FALSE;
    } else {
        echo '</TD><TD CLASS="result"><span class="altTxtGreen">'.strtoupper($LANG['common'][6]).'</span></TD></TR>';
        return TRUE;
    }
}

// Función para crear el formulario de datos de la BBDD
function mkDbForm(){
    global $LANG, $instLang;

    echo '<TABLE CLASS="tblConfig">';
    echo '<FORM METHOD="post" NAME="frmDbConfig" ID="frmConfig" ACTION="install.php" />';

    echo '<TR><TD CLASS="descCampo">'.$LANG['install'][29].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="dbhost" VALUE="localhost" /></TD>';
    echo '</TR>';
    
    echo '<TR><TD CLASS="descCampo">'.$LANG['install'][30].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="dbadminuser" VALUE="root" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['install'][31].'</TD>';
    echo '<TD><INPUT TYPE="password" NAME="dbadminpass" VALUE="" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['install'][32].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="dbname" VALUE="phppms" /></TD>';
    echo '</TR>';    
    
    echo '<TR><TD CLASS="descCampo">'.$LANG['install'][33].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="dbuser" VALUE="phppms" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['install'][34].'</TD>';
    echo '<TD><INPUT TYPE="password" NAME="dbpass" VALUE="" /></TD>';
    echo '</TR>';
   
    echo '</TABLE>';
    echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][27].'" />';
    echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][26].'" />';
    echo '<INPUT TYPE="hidden" NAME="instLang" VALUE="'.$instLang.'" />';
    echo '<INPUT TYPE="hidden" NAME="step" VALUE="3" />';
    echo '</FORM>';      
}

// Función para crear el formulario de configuración
function mkConfigForm(){
    global $LANG, $instLang;
    
    $arrLangAvailable = array('es_ES','en_US');
    
    echo '<TABLE CLASS="tblConfig">';
    echo '<FORM METHOD="post" NAME="frmConfig" ID="frmConfig" ACTION="install.php" />';

    //echo '<TR><TD COLSPAN="2" CLASS="rowHeader">'.$LANG['config'][1].'</TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][2].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="sitename" CLASS="txtLong" ID="sitename" VALUE="" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][3].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="siteshortname" VALUE="" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][36].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="siteroot" VALUE="/phppms" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][37].'</TD>';
    echo '<TD><SELECT NAME="sitelang" SIZE="1">';
    foreach ( $arrLangAvailable as $langOption ){
        echo '<OPTION>'.$langOption.'</OPTION>';
    }
    echo '</SELECT></TD></TR>';


    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][4].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="session_timeout" VALUE="300" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][5].'</TD>';
    echo '<TD><INPUT TYPE="checkbox" NAME="logenabled" CLASS="checkbox" checked="checked" /></TD>';
    echo '</TR>';        

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][6].'</TD>';
    echo '<TD><INPUT TYPE="checkbox" NAME="debug" CLASS="checkbox" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][7].'</TD>';
    echo '<TD><SELECT NAME="account_link" SIZE="1">';
    echo '<OPTION>TRUE</OPTION><OPTION>FALSE</OPTION>';
    echo '</SELECT></TD></TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][8].'</TD>';
    echo '<TD><SELECT NAME="account_count" SIZE="1">';

    $arrAccountCount = array(1,2,3,5,10,15,20,25,30,50,"all");

    foreach ($arrAccountCount as $num ){
        echo "<OPTION>$num</OPTION>";
    }
    echo '</SELECT></TD></TR>';

    //echo '<TR><TD COLSPAN="2" CLASS="rowHeader" >'.$LANG['config'][9].'</TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][10].'</TD>';
    echo '<TD><INPUT TYPE="checkbox" NAME="wikienabled" CLASS="checkbox" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][11].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="wikisearchurl" CLASS="txtLong" VALUE="" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][12].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="wikipageurl" CLASS="txtLong" VALUE="" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][13].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="wikifilter" VALUE="" /></TD>';
    echo '</TR>';

    //echo '<TR><TD COLSPAN="2" CLASS="rowHeader" >'.$LANG['config'][14].'</TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][15].'</TD>';
    echo '<TD><INPUT TYPE="checkbox" NAME="ldapenabled" CLASS="checkbox" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][16].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="ldapserver" VALUE="" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][17].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="ldapbase" CLASS="txtLong" VALUE="" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][18].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="ldapgroup" CLASS="txtLong" VALUE="" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][19].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="ldapuserattr" CLASS="txtLong" VALUE="" /></TD>';
    echo '</TR>';

    //echo '<TR><TD COLSPAN="2" CLASS="rowHeader" >'.$LANG['config'][20].'</TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][21].'</TD>';
    echo '<TD><INPUT TYPE="checkbox" NAME="mailenabled" CLASS="checkbox" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][22].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="mailserver" VALUE="" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][23].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="mailfrom" VALUE="" /></TD>';
    echo '</TR>'; 

    echo '</TABLE>';
    echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][28].'" />';
    echo '<INPUT TYPE="hidden" NAME="instLang" VALUE="'.$instLang.'" />';
    echo '<INPUT TYPE="hidden" NAME="step" VALUE="5" />';
    echo '</FORM>';    
}

// Función para mostrar el botón de volver
function printBack($step){
    global $LANG, $instLang;
    
    echo '<FORM METHOD="post" NAME="frmConfig" ID="frmConfig" ACTION="install.php" />';
    echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][47].'" />';
    echo '<INPUT TYPE="hidden" NAME="instLang" VALUE="'.$instLang.'" />';
    echo '<INPUT TYPE="hidden" NAME="step" VALUE="'.$step.'" /></FORM>';
}
?>