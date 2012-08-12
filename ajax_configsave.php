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
 * @version 0.91b
 * @link http://www.cygnux.org/phppms
 * 
 */

    define('PMS_ROOT', '.');
    include_once (PMS_ROOT."/inc/includes.php");
    
    // Comprobamos si la sesión ha caducado
    if ( check_session(TRUE) ) {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][35];
        Common::printXML($resXML);
        return;
    }
    
    Users::checkUserAccess("config") || die ('<DIV CLASS="error"'.$LANG['msg'][34].'</DIV');
    
    // Variables POST del formulario
    extract($_POST, EXTR_PREFIX_ALL, "post");
    
    $strAction = $_POST["action"];
    $objConfig = new Config;

    if ( $post_action == "config" ){
        
        if ( $post_account_count == "all" ) {
            $intAccountCount = 99;
        } else {
            $intAccountCount = $post_account_count;
        }
        
        if ( ! $post_sitename OR ! $post_siteshortname ){
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][36];
            Common::printXML($resXML);
            return;                
        }

        if ( $post_wikienabled AND ( ! $post_wikisearchurl OR ! $post_wikipageurl OR ! is_array($post_wikifilter) )){
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][37];
            Common::printXML($resXML);
            return;            
        } elseif ( $post_wikienabled ) {
            $objConfig->arrConfigValue["wikienabled"] = 1;
            $objConfig->arrConfigValue["wikisearchurl"] = $post_wikisearchurl;
            $objConfig->arrConfigValue["wikipageurl"] = $post_wikipageurl;
            $objConfig->arrConfigValue["wikifilter"] = implode("||", $post_wikifilter);
            
        } else{
            $objConfig->arrConfigValue["wikienabled"] = 0;
        }
        
        if ( $post_ldapenabled AND ( ! $post_ldapserver OR ! $post_ldapbase OR ! $post_ldapgroup OR ! is_array($post_ldapuserattr) )){
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][38];
            Common::printXML($resXML);
            return;            
        } elseif ( $post_ldapenabled ){
            $objConfig->arrConfigValue["ldapenabled"] = 1;
            $objConfig->arrConfigValue["ldapserver"] = $post_ldapserver;
            $objConfig->arrConfigValue["ldapbase"] = $post_ldapbase;
            $objConfig->arrConfigValue["ldapgroup"] = $post_ldapgroup;
            $objConfig->arrConfigValue["ldapuserattr"] = implode("||", $post_ldapuserattr);

        } else {
            $objConfig->arrConfigValue["ldapenabled"] = 0;
        }

        if ( $post_mailenabled AND ( ! $post_mailserver OR ! $post_mailfrom ) ){
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][39];
            Common::printXML($resXML);
            return;             
        } elseif ( $post_mailenabled ) {
            $objConfig->arrConfigValue["mailenabled"] = 1;
            $objConfig->arrConfigValue["mailserver"] = $post_mailserver;
            $objConfig->arrConfigValue["mailfrom"] = $post_mailfrom;            
        } else {
            $objConfig->arrConfigValue["mailenabled"] = 0;
        }
        
        if ( $post_allowed_size > 16384 ){
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][97];
            Common::printXML($resXML);
            return;
        } else {
            $objConfig->arrConfigValue["allowed_size"] = ( $post_allowed_size ) ? (int)$post_allowed_size : 1024;
        }
        
        if ( is_array($post_allowed_exts) ){
            $objConfig->arrConfigValue["allowed_exts"] = implode(",", $post_allowed_exts);
        } else {
            $objConfig->arrConfigValue["allowed_exts"] = "";
        }
        
        $objConfig->arrConfigValue["account_link"] = $post_account_link;
        $objConfig->arrConfigValue["account_count"] = $post_account_count;        
        $objConfig->arrConfigValue["sitename"] = $post_sitename;
        $objConfig->arrConfigValue["siteshortname"] = $post_siteshortname;
        $objConfig->arrConfigValue["siteroot"] = $post_siteroot;
        $objConfig->arrConfigValue["sitelang"] = $post_sitelang;
        
        $objConfig->arrConfigValue["session_timeout"] = ( $post_session_timeout ) ? (int)$post_session_timeout : 300;
        $objConfig->arrConfigValue["logenabled"] = ( $post_logenabled ) ? 1 : 0;
        $objConfig->arrConfigValue["debug"] = ( $post_debug ) ? 1 : 0;
        $objConfig->arrConfigValue["filesenabled"] = ( $post_filesenabled ) ? 1 : 0;
                
//        if ($blnMd5Password == "FALSE" AND $blnMd5PasswordOld == "TRUE") {
//            $clsAccount = new Account;
//            $clsAccount->ResetAllAccountMd5Pass();
//        }

        if ( $objConfig->writeConfig() ){
            $resXML["status"] = 0;
            $resXML["description"] = $LANG['msg'][40];
            Common::wrLogInfo($LANG['event'][21], "");
            Common::sendEmail($LANG['mailevent'][5]);
        } else {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][41];
        }

    } elseif ( $strAction == "crypt"){
        $strCurMasterPass = $_POST["curMasterPwd"];    
        $strNewMasterPass = $_POST["newMasterPwd"];
        $strNewMasterPassR = $_POST["newMasterPwdR"];
        $intConfirmPassChange = $_POST["confirmPassChange"];
        
        if ( $strNewMasterPass != "" AND $strCurMasterPass != ""){
            if ( $intConfirmPassChange == 1 ){
                if ( $strNewMasterPass == $strNewMasterPassR ){
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