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
        global $LANG;
        
        $this->getChildVars();
        
        @$mysqli = new mysqli($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
        
        if ( $mysqli->connect_errno ){
            if ( is_array($LANG) ){
                $this->throwError($LANG['msg'][75].": (".$mysqli->connect_errno . ") ".$mysqli->connect_error);
                //die( $LANG['msg'][75].": (".$mysqli->connect_errno . ") ".$mysqli->connect_error );
            } else {
                $txtError = "No es posible conectar con la BD: (".$mysqli->connect_errno . ") ".$mysqli->connect_error;
                $txtError .= "<BR />Unable to connect to DB: (".$mysqli->connect_errno . ") ".$mysqli->connect_error;
                $txtError .= "<BR /><A HREF='install/install.php'> Reinstale la aplicación</A>";
                $txtError .= "<BR /><A HREF='install/install.php'>Reinstall the application</A>";
                $this->throwError($txtError);
                //die( "Unable to connect to DB: (".$mysqli->connect_errno . ") ".$mysqli->connect_error );

            }
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

    private function throwError($str){
        $htmlOut = "<html><head><title>phpPMS :: ERROR</title></head><body>";
        $htmlOut .= "<div align='center' style='width:60%;margin:auto;padding:15px;border:1px solid red;background-color:#fee8e6;color:red;line-height:2em;font-family:Verdana,Helvetica,Arial;'>Ooops...<br />".$str."</div>";
        $htmlOut .= "</body></html>";

        header("Content-Type: text/html; charset=UTF-8");
        die($htmlOut);
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
            die($LANG['msg'][77]." (".$fileName.")");
        }

        return TRUE;
    }

    // Método para cargar la configuración desde la BBDD
    public function getDBConfig(){
        global $CFG_PMS;

        if ( isset ($CFG_PMS) ) return TRUE;
        
        if ( $this->getConfigValue("siteshortname") == "" ){
            $CFG_PMS["siteshortname"] = "PMS";
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
      
    // Método para crear la configuración inicial
    public function mkInitialConfig($arrConfigParams){
        global $LANG;
        
        if ( $this->getConfigValue("install") == 1 ){
            echo '<TR><TD>'.$LANG['install'][0].'</TD><TD CLASS="result"><span class="altTxtOrange">'.strtoupper($LANG['common'][4]).'</span></TD></TR>';
            return FALSE;
        }

        if ( ! $this->getConfigValue("masterPwd") ){
            $objCrypt = new Crypt();
            $hashMPass = $objCrypt->mkHashPassword("0000");
            $this->arrConfigValue["masterPwd"] = $hashMPass;

            if ( $this->writeConfig(TRUE) ){
                echo '<TR><TD>'.$LANG['install'][1].'</TD><TD CLASS="result"><span class="altTxtGreen">'.strtoupper($LANG['common'][6]).'</TD></TR>';
            } else {
                echo '<TR><TD>'.$LANG['install'][2].'</TD><TD CLASS="result"><span class="altTxtRed">'.strtoupper($LANG['common'][5]).'</span></TD></TR>';
                return FALSE;
            }
            unset($objCrypt);
        } else {
            echo '<TR><TD>'.$LANG['install'][3].'</TD><TD CLASS="result"><span class="altTxtOrange">'.strtoupper($LANG['common'][4]).'</span></TD></TR>';
        }

        $strQuery = "SELECT COUNT(vacULogin) FROM users WHERE vacULogin = 'admin'";
        $resQuery = $this->dbh->query($strQuery);
        $adminCount = $resQuery->fetch_array(MYSQLI_NUM);

        if ( $adminCount[0] == 0 ){
            $strQuery = "INSERT INTO users (vacUName,vacULogin,intUGroupFid,intUProfile,blnIsAdmin,vacUPassword,blnFromLdap) 
                        VALUES('PMS Admin','admin',1,0,1,MD5('admin'),0)";
            $resQuery = $this->dbh->query($strQuery);

            if ( $resQuery ){
                echo '<TR><TD>'.$LANG['install'][4].'</TD><TD CLASS="result"><span class="altTxtGreen">'.strtoupper($LANG['common'][6]).'</TD></TR>';
            } else {
                echo '<TR><TD>'.$LANG['install'][5].'</TD><TD CLASS="result"><span class="altTxtRed">'.strtoupper($LANG['common'][5]).'</span></TD></TR>';
                return FALSE;
            }
        } else {
            echo '<TR><TD>'.$LANG['install'][6].'</TD><TD CLASS="result"><span class="altTxtOrange">'.strtoupper($LANG['common'][4]).'</TD></TR>';
        }

        unset($this->arrConfigValue);

        foreach ($arrConfigParams as $configParam => $configValue ){
            if ( ($configParam == "sitename" OR $configParam == "siteshortname" OR $configParam == "siteroot") AND ! $configValue ){
                echo '<TR><TD>'.$LANG['install'][48].' (\''.$configParam.'\')</TD><TD CLASS="result"><span class="altTxtRed">'.strtoupper($LANG['common'][5]).'</span></TD></TR>';
                return FALSE;
            } elseif ( $configValue == "on" ) {
                $configValue = 1;
            } elseif ( $configParam == "submit" OR $configParam == "step" OR $configParam == "instLang" ){
                continue;
            }
            
            $this->arrConfigValue[$configParam] = $configValue;
        }
        
        // Valores por defecto
        $this->arrConfigValue["debug"] = 0;
        $this->arrConfigValue["allowed_exts"] = "PDF,JPG,GIF,PNG,ODT,ODS,DOC,DOCX,XLS,XSL,VSD,TXT,CSV,BAK";
        $this->arrConfigValue["allowed_size"] = 1024;
        $this->arrConfigValue["wikienabled"] = 0;
        $this->arrConfigValue["wikisearchurl"] = "";
        $this->arrConfigValue["wikipageurl"] = "";
        $this->arrConfigValue["wikifilter"] = "";
        $this->arrConfigValue["ldapenabled"] = 0;
        $this->arrConfigValue["ldapserver"] = "";
        $this->arrConfigValue["ldapbase"] = "";
        $this->arrConfigValue["ldapgroup"] = "";
        $this->arrConfigValue["ldapuserattr"] = "";
        $this->arrConfigValue["mailenabled"] = 0;
        $this->arrConfigValue["mailserver"] = "";
        $this->arrConfigValue["mailfrom"] = "";
        $this->arrConfigValue["install"] = 1;
        $this->arrConfigValue["version"] = PMS_VERSION;
        

        if ( $this->writeConfig(TRUE) ) return TRUE;
    }    
    
    // Método para realizar los backups
    public function doDbBackup($bakDirPMS){
        global $CFG_PMS, $LANG;
        $arrOut = "";
        $strError = "";
        
        $siteName = $CFG_PMS["siteshortname"];
        $bakDstDir = $bakDirPMS.'/backup';
        $bakFilePMS = $bakDirPMS.'/backup/'.$siteName.'.tgz';
        $bakFileDB = $bakDirPMS.'/backup/'.$siteName.'_db.sql';

        if ( ! is_dir($bakDstDir) ){
            if ( ! @mkdir($bakDstDir, 0550) ){
                $arrOut[] = '<span class="altTxtRed">'.$LANG['msg'][94].' ('.$bakDstDir.')</span>';
                Common::wrLogInfo("Backup BBDD",$strError.";IP:".$_SERVER['REMOTE_ADDR']);
            }
        }

        if ( ! is_writable($bakDstDir) ){
            $arrOut[] = '<span class="altTxtRed">'.$LANG['msg'][89].'</span>';
        }

        if ( ! is_array($arrOut) ){
            Common::wrLogInfo($LANG['event'][19],"IP:".$_SERVER['REMOTE_ADDR']);
            Common::sendEmail($LANG['mailevent'][3]);
            
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
        global $LANG;
        
        $arrLangAvailable = array('es_ES','en_US');
        
        $this->getConfig();
        
        echo '<TABLE CLASS="data tblConfig">';
        echo '<FORM METHOD="post" NAME="frmConfig" ID="frmConfig" />';      

        echo '<TR><TD COLSPAN="2" CLASS="rowHeader">'.$LANG['config'][1].'</TD></TR>';
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][2];
        Common::printHelpButton("config", 0);
        echo '</TD>';
        echo '<TD><INPUT TYPE="text" NAME="sitename" CLASS="txtLong" ID="sitename" VALUE="'.$this->arrConfigValue["sitename"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][3];
        Common::printHelpButton("config", 1);
        echo '</TD>';
        echo '<TD><INPUT TYPE="text" NAME="siteshortname" VALUE="'.$this->arrConfigValue["siteshortname"].'" /></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][36];
        Common::printHelpButton("config", 2);
        echo '</TD>';
        echo '<TD><INPUT TYPE="text" NAME="siteroot" VALUE="'.$this->arrConfigValue["siteroot"].'" />';
        echo '<IMG SRC="imgs/warning.png" ALT="'.$LANG['config'][35].'" CLASS="iconMini" TITLE="'.$LANG['config'][41].'" /></TD>';

        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][37].'</TD>';
        echo '<TD><SELECT NAME="sitelang" SIZE="1">';
        foreach ( $arrLangAvailable as $langOption ){
            $selected = ( $this->arrConfigValue["sitelang"] == $langOption ) ?  "SELECTED" : "";
            
            echo '<OPTION '.$selected.'>'.$langOption.'</OPTION>';
        }
        echo '</SELECT></TD></TR>';
        

        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][4].'</TD>';
        echo '<TD><INPUT TYPE="text" NAME="session_timeout" VALUE="'.$this->arrConfigValue["session_timeout"].'" /></TD>';
        echo '</TR>';
        
        $chkLog = ( $this->arrConfigValue["logenabled"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][5].'</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="logenabled" CLASS="checkbox" '.$chkLog.' /></TD>';
        echo '</TR>';        

        $chkDebug = ( $this->arrConfigValue["debug"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][6].'</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="debug" CLASS="checkbox" '.$chkDebug.' /></TD>';
        echo '</TR>';
               
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][7];
        Common::printHelpButton("config", 3);
        echo '</TD>';
        echo '<TD><SELECT NAME="account_link" SIZE="1">';
        if ( $this->arrConfigValue["account_link"] == "TRUE" ){
            echo '<OPTION SELECTED>TRUE</OPTION><OPTION>FALSE</OPTION>';
        } else {
            echo '<OPTION>TRUE</OPTION><OPTION SELECTED>FALSE</OPTION>';
        }
        echo '</SELECT></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][8];
        Common::printHelpButton("config", 4);
        echo '</TD>';
        echo '<TD><SELECT NAME="account_count" SIZE="1">';
        
        $arrAccountCount = array(1,2,3,5,10,15,20,25,30,50,"all");
        
        foreach ($arrAccountCount as $num ){
            if ( $this->arrConfigValue["account_count"] == $num){
                echo "<OPTION SELECTED>$num</OPTION>";
            } else {
                echo "<OPTION>$num</OPTION>";
            }
        }
        echo '</SELECT></TD>';
        echo '</TR>';

        $chkFiles = ( $this->arrConfigValue["filesenabled"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][39];
        Common::printHelpButton("config", 5);
        echo '</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="filesenabled" CLASS="checkbox" '.$chkFiles.' /></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][38].'</TD>';
        echo '<TD><INPUT TYPE="text" NAME="add_ext" ID="add_ext" />';
        echo '<IMG SRC="imgs/add.png" TITLE="'.$LANG['buttons'][43].'" CLASS="inputImg" ID="btnAddExt" OnClick="addSelOption(\'allowed_exts\',\'add_ext\')" />';        
        echo '<BR /><SELECT ID="allowed_exts" NAME="allowed_exts[]" MULTIPLE="multiple" SIZE="3">';
        
        if ( $this->arrConfigValue["allowed_exts"] ){
            $allowed_exts = explode(",", $this->arrConfigValue["allowed_exts"]);
            sort($allowed_exts, SORT_STRING);
            
            foreach ( $allowed_exts as $extAllow ){
                echo '<OPTION VALUE="'.$extAllow.'">'.$extAllow.'</OPTION>';
            }
        }
        
        echo '</SELECT>';
        echo '<IMG SRC="imgs/delete.png" TITLE="'.$LANG['buttons'][44].'" CLASS="inputImg" ID="btnDelExt" OnClick="delSelOption(\'allowed_exts\')" />';        
        echo '</TD></TR>';

        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][40];
        Common::printHelpButton("config", 6);
        echo '</TD>';
        echo '<TD><INPUT TYPE="text" NAME="allowed_size" VALUE="'.$this->arrConfigValue["allowed_size"].'" /></TD>';
        echo '</TR>';
        
        echo '<TR><TD COLSPAN="2" CLASS="rowHeader" >'.$LANG['config'][9].'</TD></TR>';
        $chkWiki = ( $this->arrConfigValue["wikienabled"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][10];
        Common::printHelpButton("config", 7);
        echo '</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="wikienabled" CLASS="checkbox" '.$chkWiki.' /></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][11];
        Common::printHelpButton("config", 8);
        echo '</TD>';
        echo '<TD><INPUT TYPE="text" NAME="wikisearchurl" CLASS="txtLong" VALUE="'.$this->arrConfigValue["wikisearchurl"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][12];
        Common::printHelpButton("config", 9);
        echo '</TD>';
        echo '<TD><INPUT TYPE="text" NAME="wikipageurl" CLASS="txtLong" VALUE="'.$this->arrConfigValue["wikipageurl"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][13];
        Common::printHelpButton("config", 10);
        echo '</TD>';
        echo '<TD><INPUT TYPE="text" NAME="add_wikifilter" ID="add_wikifilter" />';
        echo '<IMG SRC="imgs/add.png" TITLE="'.$LANG['buttons'][45].'" CLASS="inputImg" ID="btnAddWikifilter" OnClick="addSelOption(\'wikifilter\',\'add_wikifilter\')" />';
        echo '<BR /><SELECT ID="wikifilter" NAME="wikifilter[]" MULTIPLE="multiple" SIZE="3">';
        
        if ( $this->arrConfigValue["wikifilter"] ){
            $wikifilter = explode("||", $this->arrConfigValue["wikifilter"]);
            sort($wikifilter, SORT_STRING);
            
            foreach ( $wikifilter as $filter ){
                echo '<OPTION VALUE="'.$filter.'">'.$filter.'</OPTION>';
            }
        }
        
        echo '</SELECT>';
        echo '<IMG SRC="imgs/delete.png" TITLE="'.$LANG['buttons'][46].'" CLASS="inputImg" ID="btnDelWikifilter" OnClick="delSelOption(\'wikifilter\')" />';        
        echo '</TD></TR>';

        echo '<TR><TD COLSPAN="2" CLASS="rowHeader" >'.$LANG['config'][14].'</TD></TR>';
        
        $chkLdap = ( $this->arrConfigValue["ldapenabled"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][15];
        Common::printHelpButton("config", 11);
        echo '</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="ldapenabled" CLASS="checkbox" '.$chkLdap.' /></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][16].'</TD>';
        echo '<TD><INPUT TYPE="text" NAME="ldapserver" VALUE="'.$this->arrConfigValue["ldapserver"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][17];
        Common::printHelpButton("config", 12);
        echo '</TD>';
        echo '<TD><INPUT TYPE="text" NAME="ldapbase" CLASS="txtLong" VALUE="'.$this->arrConfigValue["ldapbase"].'" /></TD>';
        echo '</TR>';

        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][18];
        Common::printHelpButton("config", 13);
        echo '</TD>';
        echo '<TD><INPUT TYPE="text" NAME="ldapgroup" CLASS="txtLong" VALUE="'.$this->arrConfigValue["ldapgroup"].'" /></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][19];
        Common::printHelpButton("config", 14);
        echo '</TD>';
        echo '<TD><INPUT TYPE="text" NAME="add_ldapuserattr" ID="add_ldapuserattr" />';
        echo '<IMG SRC="imgs/add.png" TITLE="'.$LANG['buttons'][47].'" CLASS="inputImg" ID="btnAddLdapuserattr" OnClick="addSelOption(\'ldapuserattr\',\'add_ldapuserattr\')" />';
        echo '<BR /><SELECT ID="ldapuserattr" NAME="ldapuserattr[]" MULTIPLE="multiple" SIZE="3">';
        
        if ( $this->arrConfigValue["ldapuserattr"] ){
            $ldapuserattr = explode("||", $this->arrConfigValue["ldapuserattr"]);
            sort($ldapuserattr, SORT_STRING);
            
            foreach ( $ldapuserattr as $filter ){
                echo '<OPTION VALUE="'.$filter.'">'.$filter.'</OPTION>';
            }
        }
        
        echo '</SELECT>';
        echo '<IMG SRC="imgs/delete.png" TITLE="'.$LANG['buttons'][48].'" CLASS="inputImg" ID="btnDelLdapuserattr" OnClick="delSelOption(\'ldapuserattr\')" />';        
        echo '</TD></TR>';
        

        echo '<TR><TD COLSPAN="2" CLASS="rowHeader" >'.$LANG['config'][20].'</TD></TR>';
        $chkMail = ( $this->arrConfigValue["mailenabled"] ) ? 'checked="checked"' : '';
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][21].'</TD>';
        echo '<TD><INPUT TYPE="checkbox" NAME="mailenabled" CLASS="checkbox" '.$chkMail.' /></TD>';
        echo '</TR>';
      
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][22].'</TD>';
        echo '<TD><INPUT TYPE="text" NAME="mailserver" SIZE="20" VALUE="'.$this->arrConfigValue["mailserver"].'" /></TD>';
        echo '</TR>';
        
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][23].'</TD>';
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