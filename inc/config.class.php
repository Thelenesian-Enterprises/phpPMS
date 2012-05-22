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
 * Clase para la gestión de los valores de configuración
 *
 * @author nuxsmin
 * @version 0.9b
 * @link http://www.cygnux.org/phppms
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
        
        //$mysqli = new mysqli($CFG_DB["hostname"], $CFG_DB["username"], $CFG_DB["userpass"], $CFG_DB["dbname"]);
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
    

    // Función para obtener en valor de un parámetro
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

    // Función para guardar los valores de configuración en un array
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

    // Función para escribir la configuración
    public function writeConfig ($mkInsert = FALSE) {
        if (! is_object($this->dbh)) $this->dbh = $this->connectDb ();
        
        $arrKeys = array_keys($this->arrConfigValue);

        foreach ($arrKeys as $strKey) {
            $strValue = $this->arrConfigValue[$strKey];
            if ( $mkInsert ){
                $strQuery = "INSERT INTO config (vacParameter, vacValue) VALUES ('$strKey','$strValue')";
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
    
    // Función para cargar la configuración desde un archivo
    static function getFileConfig($filePath = ""){
        global $CFG_PMS, $CFG_DB, $CFG_SMTP, $CFG_LDAP;

        if ( isset ($CFG_PMS) ) return TRUE;

        if( ! $filePath ) $filePath = pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_DIRNAME);
        
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