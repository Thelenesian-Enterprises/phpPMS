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
 * @link http://www.cygnux.org/phppms
 * 
 */

// Función para imprimir los mensajes
function printMsg($msg,$status = 0){
    global $LANG;

    switch ($status){
        case 0:
            echo '<TR><TD>'.$msg.'</TD><TD CLASS="result"><span class="altTxtOk">'.strtoupper($LANG['common'][6]).'</span></TD></TR>';
            break;
        case 1:
            echo '<TR><TD>'.$msg.'</TD><TD CLASS="result"><span class="altTxtError">'.strtoupper($LANG['common'][5]).'</span></TD></TR>';
            break;
        case 2:
            echo '<TR><TD>'.$msg.'</TD><TD CLASS="result"><span class="altTxtWarn">'.strtoupper($LANG['common'][4]).'</span></TD></TR>';
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
    
    $filePath = dirname(__FILE__);
    $fileName = $filePath."/db.class.php";
  
//    if ( checkDBFile() ) {
//        printMsg($LANG['install'][7], 2);
//        return TRUE;
//    }
    
    if ( ! $dbhost || ! $dbuser || ! $dbpass || ! $dbname ){
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
    $filePath = dirname(__FILE__);
    $fileName = $filePath."/db.class.php";
    
    if ( file_exists($fileName) ) return TRUE;
    
    return FALSE;
}

// Función para comprobar la conexión a la BBDD
function checkDB($useDB = FALSE, $useFile = TRUE, $silent = FALSE){
    global $LANG;
    
    if ( $useFile ){
        include_once (PMS_ROOT."/inc/db.class.php");
        $objDB = new DB;

        $dbHost = $objDB->dbhost;
        $dbUser = $objDB->dbuser;
        $dbPass = $objDB->dbpassword;
        $dbName = $objDB->dbname;

        unset($objDB);
    } else {
        $dbHost = $_POST["dbhost"];
        $dbUser = $_POST["dbuser"];
        $dbPass = $_POST["dbpass"];
        $dbName = $_POST["dbname"];        
    }

    if ( $useDB ){
        @$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    } else {
        @$mysqli = new mysqli($dbHost, $dbUser, $dbPass);
    }
    
    if ( $mysqli->connect_errno ){
        
        if ( ! $silent ) {
            switch ($mysqli->connect_errno){
                case 1044:
                    printMsg($LANG['install'][53], 1);
                    break;
                case 1045:
                    printMsg($LANG['install'][52], 1);
                    break;
                default :
                    printMsg($LANG['install'][12].": <BR />".$mysqli->connect_errno." - ".$mysqli->connect_error, 1);
            }
        }
        
        unset($mysqli);
        return FALSE;
    } else {
        if ( $useDB == TRUE ){
            $numTables = 0;
            $resQuery = $mysqli->query("SHOW TABLES");
        
            while ( $resResult = $resQuery->fetch_assoc() ){
                $numTables++;
            }
        
            if ( $numTables == 0 ){
                return FALSE;
            }
        }
        
        if ( ! $silent ){
            printMsg($LANG['install'][13]." $dbUser@$dbHost -> $dbName");
        }
        
        unset($mysqli);
        return TRUE;
    }
}

// Función para actualizar la BBDD
function updateDB(){
    global $LANG;
    
//    $objConfig = new Config;
//    $version = $objConfig->getConfigValue("version");
//    unset($objConfig);
    
//    $intVersion = rtrim($version,"b");
    $path = PMS_ROOT."/install";
    $fileName = $path."/upgrade.sql";

//    $dir = dir($path);
//    while ( false !== ($file = $dir->read()) ) {
//        if ( preg_match("/^upgrade_/", $file) ){
//            $arrFiles[] = $file;
//        }
//    }
//    $dir->close();
//    
//    print_r($arrFiles);

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

        if ( ! $mysqli->select_db($mysqli->real_escape_string($dbName)) ){
            printMsg($LANG['install'][41]." '$dbName'<BR />".$mysqli->error,1);
            return FALSE;
        }
        
        $nError = 0;
        
        while ( ! feof($handle) ) {
            $buffer = stream_get_line($handle, 1000000, ";\n");
            if ( $buffer ){
                
                $mysqli->query($buffer);
                
                if ( $mysqli->errno > 0 ){
                    if ( $mysqli->errno == 1050 
                            || $mysqli->errno == 1054 
                            || $mysqli->errno == 1060 
                            || $mysqli->errno == 1061 
                            || $mysqli->errno == 1062 ){
                        continue;
                    } else {
                        $nError++;
                        printMsg($LANG['install'][42]."<BR />(".$mysqli->errno.") ".$mysqli->error, 2);
//                      return FALSE;
                    }
                }
            }
        }
        
        if ( $nError > 0 ){
            printMsg($LANG['install'][54],1);
            return FALSE;
        } else{
            printMsg($LANG['install'][43]);
            return TRUE;
        }
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
    $dbIsHosting = ( isset($_POST["hosting"]) ) ? TRUE : FALSE;
        
    @$mysqli = new mysqli($dbHost,$dbAdminUser,$dbAdminPass);
    
    if ( $mysqli->connect_errno ){
        printMsg($LANG['install'][12].": <BR />".$mysqli->connect_errno." - ".$mysqli->connect_error, 1);
        unset($mysqli);
        return FALSE;
    } else {
        $dbUserConnect = FALSE;
        
        if ( ! checkDBFile() ) return FALSE;
        
        // Comprobamos la conexión a la BBDD y que las tablas existen
        if ( ! checkDB(TRUE,TRUE,TRUE) ) $dbUserConnect = TRUE;
        
        if ( ! $dbIsHosting ) {
            // Comprobamos si el usuario para phpPMS existe
            $strQuery = "SELECT User FROM mysql.user 
                        WHERE User = '".$mysqli->real_escape_string($dbUser)."' AND Host = '".$mysqli->real_escape_string($dbHost)."'";
            $resQuery = $mysqli->query($strQuery);
        
        
            if ( $resQuery->num_rows == 0 ){
                if ( ! $dbUserConnect ){
                    $strQuery = "CREATE USER '$dbUser'@'$dbHost' IDENTIFIED BY '$dbPass'";
                    $resQuery = $mysqli->query($strQuery);

                    if ( $resQuery ){
                        printMsg($LANG['install'][14].' '.$dbUser.'@'.$dbHost);
                    } else {
                        printMsg($LANG['install'][14].' '.$dbUser.'@'.$dbHost, 1);
                        return FALSE;
                    }
                }
            } else {
                $noPerm = 0;

                // Comprobamos que los permisos sean correctos
                printMsg($LANG['install'][22], 2);

                $strQuery = "SELECT Select_priv,Insert_priv,Update_priv,Delete_priv,Lock_tables_priv FROM mysql.db 
                            WHERE User = '".$mysqli->real_escape_string($dbUser)."' AND Host = '".$mysqli->real_escape_string($dbHost)."'";
                $resQuery = $mysqli->query($strQuery);
                $i = 0;

                if ( $resQuery->num_rows > 0 ){
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
        } else {
            if ( $dbUserConnect == FALSE ) return FALSE;
        }
        
        if ( $mysqli->select_db($mysqli->real_escape_string($dbName)) ){
            $numTables = 0;
            $resQuery = $mysqli->query("SHOW TABLES");
        
            while ( $resResult = $resQuery->fetch_assoc() ){
                $numTables++;
            }
           
            if ( $numTables > 0 ){
                printMsg($LANG['install'][45], 1);
                return FALSE;
            }
        }
        
        $fileName = PMS_ROOT."/install/phpPMS.sql";        

        if ( ! file_exists($fileName) ){
            printMsg($LANG['install'][46], 1);
            return FALSE;
        }
        
        if ( ! isset($numTables) ){
            $strQuery = "CREATE DATABASE `".$mysqli->real_escape_string($dbName)."` DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci;";
            $resQuery = $mysqli->query($strQuery);
        
            if ( ! $resQuery ){
                printMsg($LANG['install'][35]." '$dbName'<BR />".$mysqli->error, 1);
                return FALSE;
            }
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

        if ( ! $dbUserConnect && ! $dbIsHosting ){
            $strQuery = "GRANT SELECT,INSERT,UPDATE,DELETE,LOCK TABLES ON ".$mysqli->real_escape_string($dbName).".* 
                        TO '".$mysqli->real_escape_string($dbUser)."'@'".$mysqli->real_escape_string($dbHost)."'";
            $resQuery = $mysqli->query($strQuery);

            if ( ! $resQuery ){
                printMsg($LANG['install'][35]." '$dbName'<BR />".$mysqli->error, 1);
                return FALSE;
            }
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
    $modsWarn = 0;
    $modsAvail = get_loaded_extensions();
    $modsNeed = array("mysql","ldaps","mcrypt","curl","SimpleXML");

    echo '<TR><TD>'.$LANG['install'][17].':';

    foreach($modsNeed as $module){
        if ( in_array($module, $modsAvail) ){
            echo '<span class="altTxtOk"> \''.$module.'\'</span>';
        } elseif ( $module == "ldaps" && ! in_array($module, $modsAvail) ) {
            echo '<span class="altTxtWarn"> \''.$module.'\'</span>';
            $modsWarn++;
        }else {
            echo '<span class="altTxtError"> \''.$module.'\'</span>';
            $modsError++;
        }
    }

    if ( $modsError > 0 ){
        echo '</TD><TD CLASS="result"><span class="altTxtError">'.strtoupper($LANG['common'][5]).'</span></TD></TR>';
        printMsg($LANG['install'][18], 1);
        return FALSE;
    } elseif ( $modsWarn > 0 ) {
        echo '</TD><TD CLASS="result"><span class="altTxtWarn">'.strtoupper($LANG['common'][4]).'</span></TD></TR>';
         printMsg($LANG['install'][58], 2);
        return TRUE;    
    }else {
        echo '</TD><TD CLASS="result"><span class="altTxtOk">'.strtoupper($LANG['common'][6]).'</span></TD></TR>';
        return TRUE;
    }
}

// Función para crear el formulario de datos de la BBDD
function mkDbForm(){
    global $LANG, $instLang;
    
    $isInstalled = 0;
    
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

    if ( checkDBFile() ){
        if ( checkDB(TRUE, TRUE, TRUE)){
            $objConfig = new Config;
            $isInstalled = $objConfig->getConfigValue("install");
            unset($objConfig);
        }
    }
    
    
    echo '<TR><TD CLASS="descCampo">'.$LANG['install'][32].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="dbname" VALUE="phppms" /></TD>';
    echo '</TR>';
    
    if ( $isInstalled == 0 ){
        echo '<TR><TD CLASS="descCampo">'.$LANG['install'][33].'</TD>';
        echo '<TD><INPUT TYPE="text" NAME="dbuser" VALUE="phppms" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">'.$LANG['install'][34].'</TD>';
        echo '<TD><INPUT TYPE="password" NAME="dbpass" VALUE="" /></TD>';
        echo '</TR>';
    }
    
    echo '<TR><TD CLASS="descCampo">'.$LANG['install'][59].'</TD>';
    echo '<TD><INPUT TYPE="checkbox" NAME="hosting" CLASS="checkbox" />';
    echo '<IMG SRC="'.PMS_ROOT.'/imgs/warning.png" ALT="'.$LANG['config'][35].'" CLASS="iconMini" TITLE="'.$LANG['install'][60].'" />';
    echo '</TD></TR>';
    
    echo '</TABLE>';
    
    if ( $isInstalled == 0 ) {
        echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][56].'" />';
    } else{
        echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][27].'" />';
    }
    
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
    echo '<TD><INPUT TYPE="text" NAME="sitename" CLASS="txtLong" ID="sitename" VALUE="Passwords Management System" /></TD>';
    echo '</TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][3].'</TD>';
    echo '<TD><INPUT TYPE="text" NAME="siteshortname" VALUE="PMS" /></TD>';
    echo '</TR>';

    preg_match("/^\/\w+/", $_SERVER["REQUEST_URI"],$siteRoot);
    
    

    if ( ! isset($siteRoot[0]) ){
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][36].'</TD>';
        echo '<TD><INPUT TYPE="text" NAME="siteroot" VALUE="" />';
        echo '<IMG SRC="'.PMS_ROOT.'/imgs/warning.png" ALT="'.$LANG['config'][35].'" CLASS="iconMini" TITLE="'.$LANG['install'][55].'" />';
        echo '</TD></TR>';
    } else{
        echo '<INPUT TYPE="hidden" NAME="siteroot" VALUE="'.$siteRoot[0].'" />';
    }
        
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

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][7].'</TD>';
    echo '<TD><SELECT NAME="account_link" SIZE="1">';
    echo '<OPTION>TRUE</OPTION><OPTION>FALSE</OPTION>';
    echo '</SELECT></TD></TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][8].'</TD>';
    echo '<TD><SELECT NAME="account_count" SIZE="1">';

    $arrAccountCount = array(1,2,3,5,10,15,20,25,30,50,"all");

    foreach ($arrAccountCount as $num ){
        if ( $num == 10 ){
            echo "<OPTION SELECTED>$num</OPTION>";
        } else {
            echo "<OPTION>$num</OPTION>";
        }
    }
    echo '</SELECT></TD></TR>';

    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][39].'</TD>';
    echo '<TD><INPUT TYPE="checkbox" NAME="filesenabled" CLASS="checkbox" checked /></TD>';
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

function installProcess($step,$instLang,$submit){
    global $LANG;
    
    if ( $step == 1){ // Comprobaciones iniciales
        echo '<TABLE ID="tblInstall">';
        if ( checkPhpVersion() && checkModules() ){
            $filePath = dirname(__FILE__)."/".PMS_ROOT."/inc";
            $fileName = $filePath."/db.class.php";

            if ( ! isset($_SERVER['HTTPS']) ){
                printMsg($LANG['install'][57], 2);
            }
            
            if ( ! preg_match("/^\/phppms\//", $_SERVER["REQUEST_URI"]) ){
                printMsg($LANG['install'][19], 2);
            }

            if ( ! is_writable($filePath) ){
                printMsg($LANG['install'][9]." ('$filePath')", 2);
            }

            if ( checkDBFile() ){          
                printMsg($LANG['install'][7],2);
            }

            printMsg($LANG['install'][20],2);

            echo '</TABLE>';
            echo '<FORM METHOD="post" NAME="frmConfig" ID="frmConfig" ACTION="install.php" />';
            echo '<INPUT TYPE="submit" NAME="submit" CLASS="button round" VALUE="'.$LANG['install'][26].'" />';
            echo '<INPUT TYPE="hidden" NAME="instLang" VALUE="'.$instLang.'" />';
            echo '<INPUT TYPE="hidden" NAME="step" VALUE="2" /></FORM>';
        }
    } elseif ( $step == 2){ // Introducir datos de conexión a la BBDD
        mkDbForm();
    } elseif ( $step == 3){ // Actualizar/Instalar
        $isOk = FALSE;

        echo '<TABLE ID="tblInstall">';

        if ( $submit == $LANG["install"][27] ){ // Actualizar 
            if ( ! checkDBFile() 
                    && ( ! $_POST["dbhost"] 
                    || ! $_POST["dbuser"] 
                    || ! $_POST["dbpass"] 
                    || ! $_POST["dbname"] ) ){
                printMsg($LANG["install"][51],1);
            } else {
                if ( ! checkDBFile() ) {
                    if ( checkDB(TRUE, FALSE) ) {
                        if ( ! createDbFile() ) {
                            $LANG['install'][47];
                            return;
                        }
                    }
                }

                if ( checkDB() && updateDB() ){
                    printMsg($LANG["install"][38],2);
                    updateVersion();
                    printMsg($LANG["install"][24]." (v".PMS_VERSION.")");
                    echo '</TABLE>';
                    echo '<DIV ID="access"><A CLASS="round" HREF="'.PMS_ROOT.'/login.php">'.$LANG['install'][23].'</A></DIV>';
                    $isOk = TRUE;
                }
            }
        } elseif ( $submit == $LANG["install"][56] ){ // Instalar
            if ( ! checkDBFile() ) {
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
            } else {
                printMsg($LANG['install'][0], 1);
            }
        }

        if ( ! $isOk ){
            echo '</TABLE>';
            printBack(2);
        }        
    } elseif ( $step == 4){ // Muestra opciones de configuración en caso de instalar
        mkConfigForm();
    } elseif ( $step == 5 ){ // Guardar opciones de configuración y fin
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
}
?>