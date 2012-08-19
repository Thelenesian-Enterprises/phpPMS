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

    define('PMS_ROOT', '.');
    include_once (PMS_ROOT."/inc/includes.php");

    $resXML = array( "status" => 0, "description" => "");
    
    // Comprobamos si la sesión ha caducado
    if ( check_session(TRUE) ) {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][35];
        printXML($resXML);
    }
    
    $objAccount = new Account;
    
    // Variables POST del formulario
    extract($_POST, EXTR_PREFIX_ALL, "post");
      
    // Datos del Usuario
    $intUId = $_SESSION["uid"];
    $intUGroupFId = $_SESSION["ugroup"];
    
    // Comprobaciones para nueva cuenta
    if ( $post_savetyp == 1 ) {
        if ( $post_sel_cliente == "" && ( $post_cliente_new == "" || preg_match("/^".$LANG['accounts'][15]."/i", $post_cliente_new) ) ) {
            $resXML["description"] = $LANG['msg'][8];
            $resXML["status"] = 1;
        } elseif ( $post_name == "" ) {
            $resXML["description"] = $LANG['msg'][9];
            $resXML["status"] = 1;
        } elseif ( $post_login == "" ) {
            $resXML["description"] = $LANG['msg'][10];
            $resXML["status"] = 1;
        } elseif ( $post_password == "" ) {
            $resXML["description"] = $LANG['msg'][11];
            $resXML["status"] = 1;
        } elseif ( $post_password != $post_password2 ) {
            $resXML["description"] = $LANG['msg'][12];
            $resXML["status"] = 1;
        }
    }

    // Comprobaciones para modificación de cuenta
    if ( $post_savetyp == 2 ) {
        if ( $post_sel_cliente == "" ) {
            $resXML["description"] = $LANG['msg'][8];
            $resXML["status"] = 1;
        } elseif ( $post_name == "" ) {
            $resXML["description"] = $LANG['msg'][9];
            $resXML["status"] = 1;
        } elseif ( $post_login == "" ) {	
            $resXML["description"] = $LANG['msg'][10];
            $resXML["status"] = 1;
        }
    }	

    // Comprobaciones para modficación de clave
    if ( $post_savetyp == 4 ) {
        if ( $post_password == "" && $post_password2 == "" ){
            $resXML["description"] = $LANG['msg'][13];
            $resXML["status"] = 1;
        } elseif ( $post_password != $post_password2 ) {
            $resXML["description"] = $LANG['msg'][12];
            $resXML["status"] = 1;
        }
    }

    // En caso de error se detiene
    if ( $resXML["status"] == 1 ) {
        Common::printXML($resXML);
        return;
    }

    if ( $post_savetyp == 1 OR $post_savetyp == 4 ) {
        $objCrypt = new Crypt;
        $blnCryptModule = $objCrypt->checkCryptModule();
        if ($blnCryptModule == FALSE ) {
            $resXML["description"] = $LANG['msg'][14];
            $resXML["status"] = 1;
            Common::printXML($resXML);
            return;
        }
        
        if ( ! $objCrypt->mkPassEncrypt($post_password) ) {
            $resXML["description"] = $LANG['msg'][15];
            $resXML["status"] = 1;
            Common::printXML($resXML);
            return;
        }
        
        $pwdCrypt = $objCrypt->pwdCrypt;
        $strInitialVector = $objCrypt->strInitialVector;
    }

    switch ($post_savetyp){
        case 1:
            if ( $post_cliente_new != "" && ! preg_match("/^".$LANG['accounts'][15]."/i", $post_cliente_new) ){
                $objAccount->strAccCliente = $post_cliente_new;
            } else {
                $objAccount->strAccCliente = $post_sel_cliente;
            }
            $objAccount->strAccName = $post_name;
            $objAccount->intAccCategoryId = $post_categoryId;
            $objAccount->strAccUserGroupsId = $post_ugroups;
            $objAccount->strAccLogin = $post_login;
            $objAccount->strAccUrl = $post_url;
            $objAccount->strAccPwd = $pwdCrypt;
            $objAccount->strAccMd5Pwd = md5($post_password);
            $objAccount->strAccIv = $strInitialVector;
            $objAccount->strAccNotes = $post_notice;
            $objAccount->intAccUserId= $intUId;
            $objAccount->intAccUserGroupId = $intUGroupFId;
            
            if ( $objAccount->createAccount() ){
                $resXML["description"] = $LANG['msg'][16];
                $resXML["status"] = 0;

                Common::wrLogInfo($LANG['event'][2],$LANG['eventdesc'][16].": $post_name;");
                Common::sendEmail($LANG['mailevent'][0]." '$post_name'");                
            } else{
                $resXML["description"] = $LANG['msg'][17];
                $resXML["status"] = 1;
            }
            break;
        case 2:
            if ( $post_sel_cliente != $post_cliente_new && $post_cliente_new != $post_cliente_old && $post_cliente_new != "" ){
                    $objAccount->strAccCliente = $post_cliente_new;
            } else {
                    $objAccount->strAccCliente = $post_sel_cliente;
            }		
            $objAccount->intAccId = $post_accountid;
            $objAccount->strAccName = $post_name;
            $objAccount->intAccCategoryId = $post_categoryId;
            $objAccount->strAccUserGroupsId = $post_ugroups;
            $objAccount->strAccLogin = $post_login;
            $objAccount->strAccUrl = $post_url;
            $objAccount->strAccNotes = $post_notice;
            $objAccount->intAccUserEditId = $intUId;
            
            if ( $objAccount->updateAccount() ){
                $resXML["description"] = $LANG['msg'][18];
                $resXML["status"] = 0;

                Common::wrLogInfo($LANG['event'][3], "ID: $post_accountid;".$LANG['eventdesc'][16].": $post_name");
                Common::sendEmail($LANG['mailevent'][1]." '$post_name'");
            } else {
                $resXML["description"] = $LANG['msg'][19];
                $resXML["status"] = 1;
            }
            break;
        case 3:
            if ( $objAccount->deleteAccount($post_accountid, $intUId) ){
                $resXML["description"] = $LANG['msg'][20];
                $resXML["status"] = 0;

                Common::wrLogInfo($LANG['event'][5], "ID: $post_accountid;");
                Common::sendEmail($LANG['mailevent'][2]." '$post_accountid'");
            } else{
                $resXML["description"] = $LANG['msg'][21];
                $resXML["status"] = 1;
            }
            break;
        case 4:
            $objAccount->intAccId = $post_accountid;
            $objAccount->strAccPwd = $pwdCrypt;
            $objAccount->strAccMd5Pwd = md5($post_password);
            $objAccount->strAccIv = $strInitialVector;
            $objAccount->intAccUserEditId = $intUId;
            
            if ( $objAccount->updateAccountPass() ){
                $resXML["description"] = $LANG['msg'][22];
                $resXML["status"] = 0;
                
                Common::wrLogInfo($LANG['event'][4], "ID: $post_accountid");
                Common::sendEmail($LANG['mailevent'][4]." '$post_accountid'");
            } else {
                $resXML["description"] = $LANG['msg'][23];
                $resXML["status"] = 1;
            }
            
            break;
        default:
            $resXML["description"] = $LANG['msg'][24];
            $resXML["status"] = 1;
	}
       
        Common::printXML($resXML);
?>