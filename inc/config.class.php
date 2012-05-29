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

class Config {

    var $dbh;
    var $arrConfigValue;
    var $dbhost;
    var $dbuser;
    var $dbpassword;
    var $dbname;    

    function __construct() {
        //$this->dbh = $this->connectDb();
    }
    
    public function connectDb(){
        $this->getChildVars();
        
        @$mysqli = new mysqli($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
        
        if ( $mysqli->connect_errno ){
            die("No es posible conectar con la BBDD: (".$mysqli->connect_errno . ") ".$mysqli->connect_error);
            return FALSE;
        }
        
        return $mysqli;
    }
    
    private function getChildVars(){
        $objDB = new DB;
        $this->dbhost = $objDB->dbhost;
        $this->dbuser = $objDB->dbuser;
        $this->dbpassword = $objDB->dbpassword;
        $this->dbname = $objDB->dbname;
        unset ($objDB);
    }
    
    private function checkDBCon(){
        if ( ! is_object($this->dbh) ) {
            if ( ! $this->dbh = $this->connectDb () ) return FALSE;
        }
        
        return TRUE;
    }


        // Método para obtener en valor de un parámetro
    public function getConfigValue ($strConfigParam) {
        if ( ! $this->checkDBCon() ) return FALSE;
        
        $strQuery = "SELECT vacValue FROM config WHERE vacParameter = '$strConfigParam'";
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resResult = $resQuery->fetch_assoc();
        $strValue = $resResult["vacValue"];

        return $strValue;
    }

    // Método para guardar los valores de configuración en un array
    public function getConfig () {
        if ( ! $this->checkDBCon() ) return FALSE;

        $strQuery = "SELECT vacParameter, vacValue FROM config";
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        while ($row = $resQuery->fetch_assoc()) {
            $strKey = $row["vacParameter"];
            $strValue = $row["vacValue"];
            $this->arrConfigValue[$strKey] = $strValue;
        }
    }

    // Método para escribir la configuración
    public function writeConfig ($mkInsert = FALSE) {
        if ( ! $this->checkDBCon() ) return FALSE;
        
        $arrKeys = array_keys($this->arrConfigValue);

        foreach ($arrKeys as $strKey) {
            $strKey = $this->dbh->real_escape_string($strKey);
            $strValue = $this->dbh->real_escape_string($this->arrConfigValue[$strKey]);
            
            if ( $mkInsert ){
                $strQuery = "INSERT INTO config VALUES ('$strKey','$strValue') ON DUPLICATE KEY UPDATE vacValue = '$strValue' ";
            } else {
                $strQuery = "UPDATE config SET vacValue = '$strValue' WHERE vacParameter = '$strKey'";
            }
            $resQuery = $this->dbh->query($strQuery);

            if ( ! $resQuery ) {
                $strQueryEsc = $this->dbh->real_escape_string($strQuery);
                Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
                return FALSE;
            }
        }
        
        return TRUE;
    }   
    
    // Método para cargar la configuración desde un archivo
    static function getFileConfig($filePath = ""){
        global $CFG_PMS, $CFG_DB;

        if ( isset ($CFG_PMS) ) return TRUE;

        if( ! $filePath ) $filePath = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_DIRNAME)."/install";
        
        $fileName = $filePath."/config.ini";

        if ( file_exists($fileName) ){
            if ( $config = parse_ini_file($fileName,TRUE) ){
                $CFG_PMS = $config["global"];
                $CFG_DB = $config["database"];
            } else{
                return FALSE;
            }
        } else {
            header("Content-Type: text/html; charset=UTF-8");
            die("El archivo de configuración no existe (".$fileName.")");
        }

        return TRUE;
    }

    // Método para cargar la configuración desde la BBDD
    public function getDBConfig(){
        global $CFG_PMS;

        if ( isset ($CFG_PMS) ) return TRUE;
        
        if ( $this->getConfigValue("siteshortname") == "" ){
            if ( ! $this->writeFileConfig2DB() ) return FALSE;
        }

        if ( ! $this->checkDBCon() ) return FALSE;
        
        $strQuery = "SELECT vacParameter, vacValue FROM config";
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        while ( $row = $resQuery->fetch_assoc() ){
            $cfgParam = $row["vacParameter"];
            $cfgValue = $row["vacValue"];
            
            if ( strstr($cfgValue, "||") ) $cfgValue = explode ("||",$cfgValue);

            $CFG_PMS["$cfgParam"] = $cfgValue;
        }
        
        return TRUE;
    }     
    
    // Método para escribir la configuración desde el archivo de configuración a la BBDD
    public function writeFileConfig2DB($filePath = ""){
        if( ! $filePath ) $filePath = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_DIRNAME)."/install";
        
        $fileName = $filePath."/config.ini";

        if ( file_exists($fileName) ){
            if ( $config = parse_ini_file($fileName,TRUE) ){
                foreach ( $config as $cfg_type => $cfg_param ){
                    if ( $cfg_type == "database" ) continue;
                    
                    foreach ( $cfg_param as $cfg_param_name => $cfg_param_value ){
                        if ( is_array($cfg_param_value) ) $cfg_param_value = implode("||", $cfg_param_value);                        
                        $this->arrConfigValue["$cfg_param_name"] = $cfg_param_value;
                    }
                }

                if ( $this->writeConfig(TRUE) ){
                    if ( is_writable($filePath) ) {
                        unlink($fileName);
                    } else{
                        Common::wrLogInfo(__FUNCTION__, "No es posible borrar el archivo de configuración '$fileName', por seguridad, bórrelo");
                    }
                    return TRUE;
                }
            } else{
                return FALSE;
            }
        } else {
            if ( $this->getConfigValue("install") == 1 ){
                die("El archivo de configuración no existe (".$fileName.")");
            }
            return FALSE;
        }
    }   
    
    // Método para crear la configuración inicial
    public function mkInitialConfig($filePath = "",$upgrade = 0){
        if ( $this->getConfigValue("install") == 1 && $upgrade == 0){
            echo '<TR><TD>Entorno ya instalado ::<a href="install.php?upgrade=1">ACTUALIZAR</a>::</TD><TD CLASS="result"><span class="altTxtOrange">AVISO</span></TD></TR>';
            return FALSE;
        }

        if ( $upgrade == 0 ){
            if ( ! $this->getConfigValue("masterPwd") ){
                $objCrypt = new Crypt();
                $hashMPass = $objCrypt->mkHashPassword("0000");
                $this->arrConfigValue["masterPwd"] = $hashMPass;

                if ( $this->writeConfig(TRUE) ){
                    echo "<TR><TD>Clave maestra inicial establecida a '0000'. Es recomendable cambiarla</TD><TD CLASS='result'><span class='altTxtGreen'>OK</TD></TR>";
                } else {
                    echo "<TR><TD>No se ha podido guardar la clave maestra</TD><TD CLASS='result'><span class='altTxtRed'>ERROR</span></TD></TR>";
                    return FALSE;
                }
                unset($objCrypt);
            } else {
                echo "<TR><TD>Clave maestra ya establecida</TD><TD CLASS='result'><span class='altTxtOrange'>AVISO</span></TD></TR>";
            }

            $strQuery = "SELECT vacULogin FROM users WHERE vacULogin = 'admin'";
            $resQuery = $this->dbh->query($strQuery);

            if ( ! $resQuery ){
                $strQuery = "INSERT INTO users (vacUName,vacULogin,intUGroupFid,intUProfile,blnIsAdmin,vacUPassword,blnFromLdap) 
                            VALUES('PMS Admin','admin',1,0,1,MD5('admin'),0);";
                $resQuery = $this->dbh->query($strQuery);

                if ( $resQuery ){
                    echo "<TR><TD>Usuario 'admin' con clave 'admin' creado correctamnete</TD><TD CLASS='result'><span class='altTxtGreen'>OK</TD></TR>";
                } else {
                    echo "<TR><TD>No se ha podido crear el usuario 'admin'</TD><TD CLASS='result'><span class='altTxtRed'>ERROR</span></TD></TR>";
                    return FALSE;
                }
            } else {
                echo "<TR><TD>El usuario 'admin', ya existe</TD><TD CLASS='result'><span class='altTxtOrange'>AVISO</TD></TR>";
            }

            unset($this->arrConfigValue);

            $this->arrConfigValue["md5_pass"] = "TRUE";
            $this->arrConfigValue["password_show"] = "TRUE";
            $this->arrConfigValue["account_link"] = "TRUE";
            $this->arrConfigValue["account_count"] = 5;
        }
        
        $this->arrConfigValue["install"] = 1;
        $this->arrConfigValue["version"] = "0.94b";
        
        if ( $this->writeFileConfig2DB($filePath) ) return TRUE;
    }    
    
    // Método para realizar los backups
    public function doDbBackup($bakDirPMS){
        global $CFG_PMS;
        
        $siteName = $CFG_PMS["siteshortname"];
        $bakDstDir = $bakDirPMS.'/backup';
        $bakFilePMS = $bakDirPMS.'/backup/'.$siteName.'.tgz';
        $bakFileDB = $bakDirPMS.'/backup/'.$siteName.'_db.sql';

        if ( ! is_dir($bakDstDir) ){
            if ( ! @mkdir($bakDstDir, 0550) ){
                $arrOut[] = '<span class="altTxtRed">No es posible crear el directorio de backups ('.$bakDstDir.')</span>';
                Common::wrLogInfo("Backup BBDD",$strError.";IP:".$_SERVER['REMOTE_ADDR']);
            }
        }

        if ( ! is_writable($bakDstDir) ){
            $arrOut[] = '<span class="altTxtRed">Compruebe los permisos del directorio de backups</span>';
        }

        if ( ! is_array($arrOut) ){
            Common::wrLogInfo("Backup BBDD","IP:".$_SERVER['REMOTE_ADDR']);
            Common::sendEmail("Realizado backup");
            
            $this->getChildVars();
            
            // Backup de la BBDD
            $command = 'mysqldump -h '.$this->dbhost.' -u '.$this->dbuser.' -p'.$this->dbpassword.' -r "'.$bakFileDB.'" '.$this->dbname.' 2>&1'; 
            $arrOut[] = system($command);
            //$bzip = system('bzip2 "'.$backupFile.'"');
            $command = 'tar czf '.$bakFilePMS.' '.$bakDirPMS.' --exclude "'.$bakDstDir.'"';
            $arrOut[] = system($command);
        }
        
        return $arrOut;
    }
    
    public function getConfigTable(){
        $this->getConfig();
        
        echo '<TABLE CLASS="data tblConfig">';
        echo '<FORM METHOD="post" NAME="frmConfig" ID="frmConfig" />';      

        echo '<TR><TD COLSPAN="2" CLASS="rowHeader">Sitio</TD></TR>';
        echo '<TR><TD CLASS="descCampo">Nombre del sitio</TD>';
        echo '<TD><INPUT TYPE="text" NAME="sitename" CLASS="txtLong" ID="sitename" VALUE="'.$this->arrConfigValue["sitename"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">Siglas del sitio</TD>';
        echo '<TD><INPUT TYPE="text" NAME="siteshortname" VALUE="'.$this->arrConfigValue["siteshortname"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">Timeout de sesión (s)</TD>';
        echo '<TD><INPUT TYPE="text" NAME="session_timeout" VALUE="'.$this->arrConfigValue["session_timeout"].'" /></TD>';
        echo '</TR>';
        
        $chkLog = ( $this->arrConfigValue["logenabled"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">Habilitar log de eventos</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="logenabled" CLASS="checkbox" '.$chkLog.' /></TD>';
        echo '</TR>';        

        $chkDebug = ( $this->arrConfigValue["debug"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">Habilitar depuración</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="debug" CLASS="checkbox" '.$chkDebug.' /></TD>';
        echo '</TR>';
               
        echo '<TR><TD CLASS="descCampo">Nombre de cuenta como enlace</TD>';
        echo '<TD><SELECT NAME="account_link" SIZE="1">';
        if ( $this->arrConfigValue["account_link"] == "TRUE" ){
            echo '<OPTION SELECTED>TRUE</OPTION><OPTION>FALSE</OPTION>';
        } else {
            echo '<OPTION>TRUE</OPTION><OPTION SELECTED>FALSE</OPTION>';
        }
        echo '</SELECT></TD></TR>';
        
        echo '<TR><TD CLASS="descCampo">Resultados por página</TD>';
        echo '<TD><SELECT NAME="account_count" SIZE="1">';
        
        $arrAccountCount = array(1,2,3,5,10,15,20,25,30,50,"all");
        
        foreach ($arrAccountCount as $num ){
            if ( $this->arrConfigValue["account_count"] == $num){
                echo "<OPTION SELECTED>$num</OPTION>";
            } else {
                echo "<OPTION>$num</OPTION>";
            }
        }
        echo '</SELECT></TD></TR>';
        
        echo '<TR><TD COLSPAN="2" CLASS="rowHeader" >Wiki</TD></TR>';
        $chkWiki = ( $this->arrConfigValue["wikienabled"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">Habilitar enlaces Wiki</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="wikienabled" CLASS="checkbox" '.$chkWiki.' /></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">URL de búsqueda Wiki</TD>';
        echo '<TD><INPUT TYPE="text" NAME="wikisearchurl" CLASS="txtLong" VALUE="'.$this->arrConfigValue["wikisearchurl"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">URL de página en Wiki</TD>';
        echo '<TD><INPUT TYPE="text" NAME="wikipageurl" CLASS="txtLong" VALUE="'.$this->arrConfigValue["wikipageurl"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">Prefijo para nombre de cuenta</TD>';
        echo '<TD><INPUT TYPE="text" NAME="wikifilter" VALUE="'.$this->arrConfigValue["wikifilter"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD COLSPAN="2" CLASS="rowHeader" >LDAP</TD></TR>';
        $chkLdap = ( $this->arrConfigValue["ldapenabled"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">Habilitar LDAP</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="ldapenabled" CLASS="checkbox" '.$chkLdap.' /></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">Servidor</TD>';
        echo '<TD><INPUT TYPE="text" NAME="ldapserver" VALUE="'.$this->arrConfigValue["ldapserver"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">Base de búsqueda</TD>';
        echo '<TD><INPUT TYPE="text" NAME="ldapbase" CLASS="txtLong" VALUE="'.$this->arrConfigValue["ldapbase"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">Grupo</TD>';
        echo '<TD><INPUT TYPE="text" NAME="ldapgroup" CLASS="txtLong" VALUE="'.$this->arrConfigValue["ldapgroup"].'" /></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">Atributos de usuario</TD>';
        echo '<TD><INPUT TYPE="text" NAME="ldapuserattr" CLASS="txtLong" VALUE="'.$this->arrConfigValue["ldapuserattr"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD COLSPAN="2" CLASS="rowHeader" >Correo</TD></TR>';
        $chkMail = ( $this->arrConfigValue["mailenabled"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">Habilitar notificaciones de correo</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="mailenabled" CLASS="checkbox" '.$chkMail.' /></TD>';
        echo '</TR>';
      
        echo '<TR><TD CLASS="descCampo">Servidor</TD>';
        echo '<TD><INPUT TYPE="text" NAME="mailserver" SIZE="20" VALUE="'.$this->arrConfigValue["mailserver"].'" /></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">Dirección de correo de envío</TD>';
        echo '<TD><INPUT TYPE="text" NAME="mailfrom" SIZE="20" VALUE="'.$this->arrConfigValue["mailfrom"].'" /></TD>';
        echo '</TR>'; 
        
        echo '<INPUT TYPE="hidden" NAME="action" VALUE="config" />';
        echo '</FORM></TABLE>';
    }
    
    // Método para comprobar si hay actualizaciones
    static function checkUpdates(){
        $ch = curl_init("http://sourceforge.net/api/file/index/project-id/775555/mtime/desc/limit/20/rss");
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        
        if ( ! $data = curl_exec($ch) ) return FALSE;
        
        curl_close($ch);
        
	$xmlUpd = simplexml_load_string($data);

	if ( $xmlUpd->channel->title ){

            $url = $xmlUpd->channel->item[0]->link;
            $title = $xmlUpd->channel->item[0]->title;
            $desc = $xmlUpd->channel->item[0]->description;
            
            preg_match("/phpPMS_(\d\.\d{1,}[a-z])\.tar.gz$/", $title, $pubVer);
            
            if ( $pubVer[1] == PMS_VERSION ){
                if ( $_SESSION["ulogin"] ){
                    $_SESSION["pms_upd"] = TRUE;
                }
                
                return TRUE;
            } else {
               if ( $_SESSION["ulogin"] ){
                    $_SESSION["pms_upd"] = array($pubVer[1], (string)$url);
                }
                
                $resCheck = array($pubVer[1], (string)$url);
                return $resCheck;
            }
        }
    }
}
?>