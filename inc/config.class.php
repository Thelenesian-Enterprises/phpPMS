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
        
        $mysqli = new mysqli($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
        if ( $mysqli->connect_errno ){
            echo "No es posible conectar con la BBDD: (".$mysqli->connect_errno . ") ".$mysqli->connect_error;
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
    

    // Método para obtener en valor de un parámetro
    public function getConfigValue ($strConfigParam) {
        if (! is_object($this->dbh)) $this->dbh = $this->connectDb ();
        
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
        if (! is_object($this->dbh)) $this->dbh = $this->connectDb ();

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
        if (! is_object($this->dbh)) $this->dbh = $this->connectDb ();
        
        $arrKeys = array_keys($this->arrConfigValue);

        foreach ($arrKeys as $strKey) {
            $strValue = $this->arrConfigValue[$strKey];
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
        global $CFG_PMS, $CFG_DB, $CFG_SMTP, $CFG_LDAP;

        if ( isset ($CFG_PMS) ) return TRUE;

        if( ! $filePath ) $filePath = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_DIRNAME)."/install";
        
        $fileName = $filePath."/config.ini";

        if ( file_exists($fileName) ){
            if ( $config = parse_ini_file($fileName,TRUE) ){
                $CFG_PMS = $config["global"];
                $CFG_DB = $config["database"];
                $CFG_SMTP = $config["smtp"];
                $CFG_LDAP = $config["ldap"];
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

        if (! is_object($this->dbh)) $this->dbh = $this->connectDb ();
        
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
                //header("Content-Type: text/html; charset=UTF-8");
                die("El archivo de configuración no existe (".$fileName.")");
            }
            return FALSE;
        }
    }   
    
    // Método para crear la configuración inicial
    public function mkInitialConfig($filePath = "",$upgrade = 0){
        if ( $this->getConfigValue("install") == 1 && $upgrade == 0){
            echo "<br />&gt; <span class='altTxtOrange'>AVISO: Entorno ya instalado</span> ::<a href='install.php?upgrade=1'>ACTUALIZAR</a>:: &lt;";
            return FALSE;
        }

        if ( $upgrade == 0 ){
            if ( ! $this->getConfigValue("masterPwd") ){
                $objCrypt = new Crypt();
                $hashMPass = $objCrypt->mkHashPassword("0000");
                $this->arrConfigValue["masterPwd"] = $hashMPass;

                if ( $this->writeConfig(TRUE) ){
                    echo "<br />&gt; Clave maestra inicial establecida a '0000'. Es recomendable cambiarla &lt;";
                } else {
                    echo "<br />&gt; <span class='altTxtRed'>ERROR: no se ha podido guardar la clave maestra</span> &lt;";
                    return FALSE;
                }
                unset($objCrypt);
            } else {
                echo "<br />&gt; <span class='altTxtOrange'>AVISO: Clave maestra ya establecida</span> &lt;";
            }

            $strQuery = "SELECT vacULogin FROM users WHERE vacULogin = 'admin'";
            $resQuery = $this->dbh->query($strQuery);

            if ( ! $resQuery ){
                $strQuery = "INSERT INTO users (vacUName,vacULogin,intUGroupFid,intUProfile,blnIsAdmin,vacUPassword,blnFromLdap) 
                            VALUES('PMS Admin','admin',1,0,1,MD5('admin'),0);";
                $resQuery = $this->dbh->query($strQuery);

                if ( $resQuery ){
                    echo "<br />&gt; Usuario 'admin' con clave 'admin' creado correctamnete &lt;";
                } else {
                    echo "<br />&gt; <span class='altTxtRed'>ERROR: no se ha podido crear el usuario 'admin'</span> &lt;";
                    return FALSE;
                }
            } else {
                echo "<br />&gt; <span class='altTxtOrange'>AVISO: el usuario 'admin', ya existe </span>&lt;";
            }

            unset($this->arrConfigValue);

            $this->arrConfigValue["md5_pass"] = "TRUE";
            $this->arrConfigValue["password_show"] = "TRUE";
            $this->arrConfigValue["account_link"] = "TRUE";
            $this->arrConfigValue["account_count"] = 5;
        }
        
        $this->arrConfigValue["install"] = 1;
        $this->arrConfigValue["version"] = "0.91b";
        
        if ( $this->writeFileConfig2DB($filePath) ) return TRUE;
    }    
    
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
}
?>