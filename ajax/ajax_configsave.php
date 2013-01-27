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

define('PMS_ROOT', '..');
include_once (PMS_ROOT."/inc/includes.php");

// Comprobamos si la sesión ha caducado
if ( check_session(TRUE) ) {
    $resXML["status"] = 1;
    $resXML["description"] = $LANG['msg'][35];
    Common::printXML($resXML);
    return;
}

Users::checkUserAccess("config") || die ('<DIV CLASS="error"'.$LANG['msg'][34].'</DIV');

$frmAction = ( isset( $_POST["action"]) ) ? $_POST["action"] : "";

$objConfig = new Config;

if ( $frmAction == "config" ){
    $frmSiteName = ( isset( $_POST["sitename"]) ) ? $_POST["sitename"] : "";
    $frmSiteShortName = ( isset( $_POST["siteshortname"]) ) ? $_POST["siteshortname"] : "";
    $frmSiteRoot = ( isset( $_POST["siteroot"]) ) ? $_POST["siteroot"] : "";
    $frmSiteLang = ( isset( $_POST["sitelang"]) ) ? $_POST["sitelang"] : "";
    $frmSessionTimeout = ( isset( $_POST["session_timeout"]) ) ? (int)$_POST["session_timeout"] : 300;
    $frmLogEnabled = ( isset( $_POST["logenabled"]) ) ? 1 : 0;
    $frmDebugEnabled = ( isset( $_POST["debug"]) ) ? 1 : 0;
    $frmFilesEnabled = ( isset( $_POST["filesenabled"]) ) ? 1 : 0;
    $frmAccountLink = ( isset( $_POST["account_link"]) ) ? $_POST["account_link"] : "";
    $frmAccountCount = ( isset( $_POST["account_count"]) ) ? (int)$_POST["account_count"] : 10;
    $frmAllowedSize = ( isset( $_POST["allowed_size"]) ) ? (int)$_POST["allowed_size"] : 1024;
    $frmAllowedExts = ( isset( $_POST["allowed_exts"]) ) ? $_POST["allowed_exts"] : "";

    $frmWikiEnabled = ( isset( $_POST["wikienabled"]) ) ? 1 : 0;
    $frmWikiSearchUrl = ( isset( $_POST["wikisearchurl"]) ) ? $_POST["wikisearchurl"] : "";
    $frmWikiPageUrl = ( isset( $_POST["wikipageurl"]) ) ? $_POST["wikipageurl"] : "";
    $frmWikiFilter = ( isset( $_POST["wikifilter"]) ) ? $_POST["wikifilter"] : "";

    $frmLdapEnabled = ( isset( $_POST["ldapenabled"]) ) ? 1 : 0;
    $frmLdapServer = ( isset( $_POST["ldapserver"]) ) ? $_POST["ldapserver"] : "";
    $frmLdapBase = ( isset( $_POST["ldapbase"]) ) ? $_POST["ldapbase"] : "";
    $frmLdapGroup = ( isset( $_POST["ldapgroup"]) ) ? $_POST["ldapgroup"] : "";
    $frmLdapUserAttr = ( isset( $_POST["ldapuserattr"]) ) ? $_POST["ldapuserattr"] : "";

    $frmMailEnabled = ( isset( $_POST["mailenabled"]) ) ? 1 : 0;
    $frmMailServer = ( isset( $_POST["mailserver"]) ) ? $_POST["mailserver"] : "";
    $frmMailFrom = ( isset( $_POST["mailfrom"]) ) ? $_POST["mailfrom"] : "";

    if ( $frmAccountCount == "all" ) {
        $intAccountCount = 99;
    } else {
        $intAccountCount = $frmAccountCount;
    }

    if ( ! $frmSiteName OR ! $frmSiteShortName ){
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][36];
        Common::printXML($resXML);
        return;                
    }

    if ( $frmWikiEnabled AND ( ! $frmWikiSearchUrl OR ! $frmWikiPageUrl OR ! is_array($frmWikiFilter) )){
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][37];
        Common::printXML($resXML);
        return;            
    } elseif ( $frmWikiEnabled ) {
        $objConfig->arrConfigValue["wikienabled"] = 1;
        $objConfig->arrConfigValue["wikisearchurl"] = $frmWikiSearchUrl;
        $objConfig->arrConfigValue["wikipageurl"] = $frmWikiPageUrl;
        $objConfig->arrConfigValue["wikifilter"] = implode("||", $frmWikiFilter);
    } else{
        $objConfig->arrConfigValue["wikienabled"] = 0;
    }

    if ( $frmLdapEnabled AND ( ! $frmLdapServer OR ! $frmLdapBase OR ! $frmLdapGroup OR ! is_array($frmLdapUserAttr) )){
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][38];
        Common::printXML($resXML);
        return;            
    } elseif ( $frmLdapEnabled ){
        $objConfig->arrConfigValue["ldapenabled"] = 1;
        $objConfig->arrConfigValue["ldapserver"] = $frmLdapServer;
        $objConfig->arrConfigValue["ldapbase"] = $frmLdapBase;
        $objConfig->arrConfigValue["ldapgroup"] = $frmLdapGroup;
        $objConfig->arrConfigValue["ldapuserattr"] = implode("||", $frmLdapUserAttr);
    } else {
        $objConfig->arrConfigValue["ldapenabled"] = 0;
    }

    if ( $frmMailEnabled AND ( ! $frmMailServer OR ! $frmMailFrom ) ){
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][39];
        Common::printXML($resXML);
        return;             
    } elseif ( $frmMailEnabled ) {
        $objConfig->arrConfigValue["mailenabled"] = 1;
        $objConfig->arrConfigValue["mailserver"] = $frmMailServer;
        $objConfig->arrConfigValue["mailfrom"] = $frmMailFrom;            
    } else {
        $objConfig->arrConfigValue["mailenabled"] = 0;
    }

    if ( $frmAllowedSize > 16384 ){
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][97];
        Common::printXML($resXML);
        return;
    } else {
        $objConfig->arrConfigValue["allowed_size"] = $frmAllowedSize;
    }

    $objConfig->arrConfigValue["allowed_exts"] = ( is_array($frmAllowedExts) ) ? implode(",", $frmAllowedExts) : "";

    $objConfig->arrConfigValue["account_link"] = $frmAccountLink;
    $objConfig->arrConfigValue["account_count"] = $frmAccountCount;        
    $objConfig->arrConfigValue["sitename"] = $frmSiteName;
    $objConfig->arrConfigValue["siteshortname"] = $frmSiteShortName;
    $objConfig->arrConfigValue["siteroot"] = $frmSiteRoot;
    $objConfig->arrConfigValue["sitelang"] = $frmSiteLang;

    $objConfig->arrConfigValue["session_timeout"] = $frmSessionTimeout;
    $objConfig->arrConfigValue["logenabled"] = $frmLogEnabled;
    $objConfig->arrConfigValue["debug"] = $frmDebugEnabled;
    $objConfig->arrConfigValue["filesenabled"] = $frmFilesEnabled;

//        if ($blnMd5Password == "FALSE" AND $blnMd5PasswordOld == "TRUE") {
//            $clsAccount = new Account;
//            $clsAccount->ResetAllAccountMd5Pass();
//        }

    if ( $objConfig->writeConfig() ){
        $resXML["status"] = 0;
        $resXML["description"] = $LANG['msg'][40];
        Common::wrLogInfo($LANG['event'][21], "");
        Common::sendEmail($LANG['event'][21]);
    } else {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][41];
    }

} elseif ( $frmAction == "crypt"){
    $strCurMasterPass = ( isset($_POST["curMasterPwd"]) ) ? $_POST["curMasterPwd"] : "";    
    $strNewMasterPass = ( isset($_POST["newMasterPwd"]) ) ? $_POST["newMasterPwd"] : "";
    $strNewMasterPassR = ( isset($_POST["newMasterPwdR"]) ) ? $_POST["newMasterPwdR"] : "";
    $intConfirmPassChange = ( isset($_POST["confirmPassChange"]) ) ? $_POST["confirmPassChange"] : "";

    if ( $strNewMasterPass != "" AND $strCurMasterPass != ""){
        if ( $intConfirmPassChange == 1 ){
            if ( $strNewMasterPass == $strNewMasterPassR ){
                if ( $strNewMasterPass == $strCurMasterPass){
                    $resXML["status"] = 1;
                    $resXML["description"] = $LANG['msg'][101];
                    Common::printXML($resXML);
                    return;                                           
                }

                $objCrypt = new Crypt;

                if ( ! $objCrypt->checkHashPass($strCurMasterPass, $objConfig->getConfigValue("masterPwd")) ){
                    $resXML["status"] = 1;
                    $resXML["description"] = $LANG['msg'][42];
                    Common::printXML($resXML);
                    return;                    
                }

                $objAccount = new Account;

                if ( ! $objAccount->updateAllAccountsMPass($strCurMasterPass,$strNewMasterPass) ){
                    $resXML["status"] = 1;
                    $resXML["description"] = $LANG['msg'][43];
                    Common::printXML($resXML);
                    return;
                }

                $hashMPass = $objCrypt->mkHashPassword($strNewMasterPass);
                $objConfig->arrConfigValue["masterPwd"] = $hashMPass;
                $objConfig->arrConfigValue["lastupdatempass"] = time();

                if ( $objConfig->writeConfig() ){
                    Common::sendEmail($LANG['event'][17]);
                    
                    $resXML["status"] = 0;
                    $resXML["description"] = $LANG['msg'][44];
                } else {
                    $resXML["status"] = 1;
                    $resXML["description"] = $LANG['msg'][45];
                }
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][46];
                Common::printXML($resXML);
                return;
            }
        } else{
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][47];
            Common::printXML($resXML);
            return;
        }
    } else {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][48];
    }
} else {
    $resXML["status"] = 1;
    $resXML["description"] = $LANG['msg'][24];
}

Common::printXML($resXML);
?>