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

$resXML = array( "status" => 0, "description" => "");

// Comprobamos si la sesión ha caducado
if ( check_session(TRUE) ) {
    $resXML["status"] = 1;
    $resXML["description"] = $LANG['msg'][35];
    printXML($resXML);
}

// Variables POST del formulario
$frmSaveType = ( isset( $_POST["savetyp"]) ) ? (int)$_POST["savetyp"] : 0;
$frmAccountId = ( isset( $_POST["accountid"]) ) ? (int)$_POST["accountid"] : 0;
$frmSelCustomer = ( isset( $_POST["sel_cliente"]) ) ? Common::sanitize($_POST["sel_cliente"]) : "";
$frmNewCustomer = ( isset( $_POST["cliente_new"]) ) ? Common::sanitize($_POST["cliente_new"]) : "";
$frmOldCustomer = ( isset( $_POST["cliente_old"]) ) ? Common::sanitize($_POST["cliente_old"]) : "";
$frmName = ( isset( $_POST["name"]) ) ? Common::sanitize($_POST["name"]) : "";
$frmLogin = ( isset( $_POST["login"]) ) ? Common::sanitize($_POST["login"]) : "";
$frmPassword = ( isset( $_POST["password"]) ) ? Common::sanitize($_POST["password"]) : "";
$frmPasswordV = ( isset( $_POST["password2"]) ) ? Common::sanitize($_POST["password2"]) : "";
$frmCategoryId = ( isset( $_POST["categoryId"]) ) ? (int)$_POST["categoryId"] : 0;
$frmUGroups = ( isset( $_POST["ugroups"]) ) ? $_POST["ugroups"] : "";
$frmNotes = ( isset( $_POST["notice"]) ) ? Common::sanitize($_POST["notice"]) : "";
$frmUrl = ( isset( $_POST["url"]) ) ? Common::sanitize($_POST["url"]) : "";

// Datos del Usuario
$intUId = $_SESSION["uid"];
$intUGroupFId = $_SESSION["ugroup"];

switch ( $frmSaveType ){
    case 1:
        // Comprobaciones para nueva cuenta
        if ( ! $frmName ) {
            $resXML["description"] = $LANG['msg'][9];
            $resXML["status"] = 1;
        } elseif ( ! $frmSelCustomer && ( ! $frmNewCustomer || preg_match("/^".$LANG['accounts'][15]."/i", $frmNewCustomer) ) ) {
            $resXML["description"] = $LANG['msg'][8];
            $resXML["status"] = 1;
        } elseif ( ! $frmLogin ) {
            $resXML["description"] = $LANG['msg'][10];
            $resXML["status"] = 1;
        } elseif ( ! $frmPassword ) {
            $resXML["description"] = $LANG['msg'][11];
            $resXML["status"] = 1;
        } elseif ( $frmPassword != $frmPasswordV ) {
            $resXML["description"] = $LANG['msg'][12];
            $resXML["status"] = 1;
        }
        break;
    case 2:
        // Comprobaciones para modificación de cuenta
        if ( ! $frmSelCustomer ) {
            $resXML["description"] = $LANG['msg'][8];
            $resXML["status"] = 1;
        } elseif ( ! $frmName ) {
            $resXML["description"] = $LANG['msg'][9];
            $resXML["status"] = 1;
        } elseif ( ! $frmLogin ) {	
            $resXML["description"] = $LANG['msg'][10];
            $resXML["status"] = 1;
        }
        break;
    case 3:
        break;
    case 4:
        // Comprobaciones para modficación de clave
        if ( ! $frmPassword && ! $frmPasswordV ){
            $resXML["description"] = $LANG['msg'][13];
            $resXML["status"] = 1;
        } elseif ( $frmPassword != $frmPasswordV ) {
            $resXML["description"] = $LANG['msg'][12];
            $resXML["status"] = 1;
        }
        break;
    default:
        $resXML["description"] = $LANG['msg'][24];
        $resXML["status"] = 1;
        break;
}

// En caso de error se detiene
if ( $resXML["status"] == 1 ) {
    Common::printXML($resXML);
    return;
}

if ( $frmSaveType == 1 OR $frmSaveType == 4 ) {
    $objCrypt = new Crypt;
    $blnCryptModule = $objCrypt->checkCryptModule();
    if ($blnCryptModule == FALSE ) {
        $resXML["description"] = $LANG['msg'][14];
        $resXML["status"] = 1;
        Common::printXML($resXML);
        return;
    }

    if ( ! $objCrypt->mkPassEncrypt($frmPassword) ) {
        $resXML["description"] = $LANG['msg'][15];
        $resXML["status"] = 1;
        Common::printXML($resXML);
        return;
    }

    $pwdCrypt = $objCrypt->pwdCrypt;
    $strInitialVector = $objCrypt->strInitialVector;
}

$objAccount = new Account;

switch ($frmSaveType){
    case 1:
        if ( $frmNewCustomer && ! preg_match("/^".$LANG['accounts'][15]."/i", $frmNewCustomer) ){
            $objAccount->strAccCliente = $frmNewCustomer;
        } else {
            $objAccount->strAccCliente = $frmSelCustomer;
        }
        $objAccount->strAccName = $frmName;
        $objAccount->intAccCategoryId = $frmCategoryId;
        $objAccount->strAccUserGroupsId = $frmUGroups;
        $objAccount->strAccLogin = $frmLogin;
        $objAccount->strAccUrl = $frmUrl;
        $objAccount->strAccPwd = $pwdCrypt;
        $objAccount->strAccMd5Pwd = md5($frmPassword);
        $objAccount->strAccIv = $strInitialVector;
        $objAccount->strAccNotes = $frmNotes;
        $objAccount->intAccUserId= $intUId;
        $objAccount->intAccUserGroupId = $intUGroupFId;

        if ( $objAccount->createAccount() ){
            $resXML["description"] = $LANG['msg'][16];
            $resXML["status"] = 0;

            Common::wrLogInfo($LANG['event'][2],$LANG['eventdesc'][16].": $frmName;");
            Common::sendEmail($LANG['event'][2]);
        } else{
            $resXML["description"] = $LANG['msg'][17];
            $resXML["status"] = 1;
        }
        break;
    case 2:
        if ( $frmSelCustomer != $frmNewCustomer && $frmNewCustomer != $frmOldCustomer && $frmNewCustomer ){
            $objAccount->strAccCliente = $frmNewCustomer;
        } else {
            $objAccount->strAccCliente = $frmSelCustomer;
        }		
        $objAccount->intAccId = $frmAccountId;
        $objAccount->strAccName = $frmName;
        $objAccount->intAccCategoryId = $frmCategoryId;
        $objAccount->strAccUserGroupsId = $frmUGroups;
        $objAccount->strAccLogin = $frmLogin;
        $objAccount->strAccUrl = $frmUrl;
        $objAccount->strAccNotes = $frmNotes;
        $objAccount->intAccUserEditId = $intUId;

        if ( $objAccount->updateAccount() ){
            $resXML["description"] = $LANG['msg'][18];
            $resXML["status"] = 0;

            Common::wrLogInfo($LANG['event'][3], "ID: $frmAccountId;".$LANG['eventdesc'][16].": $frmName");
            Common::sendEmail($LANG['event'][3]);
        } else {
            $resXML["description"] = $LANG['msg'][19];
            $resXML["status"] = 1;
        }
        break;
    case 3:
        if ( $objAccount->deleteAccount($frmAccountId, $intUId) ){
            $resXML["description"] = $LANG['msg'][20];
            $resXML["status"] = 0;

            Common::wrLogInfo($LANG['event'][5], "ID: $frmAccountId;");
            Common::sendEmail($LANG['event'][5]);
        } else{
            $resXML["description"] = $LANG['msg'][21];
            $resXML["status"] = 1;
        }
        break;
    case 4:
        $objAccount->intAccId = $frmAccountId;
        $objAccount->strAccPwd = $pwdCrypt;
        $objAccount->strAccMd5Pwd = md5($frmPassword);
        $objAccount->strAccIv = $strInitialVector;
        $objAccount->intAccUserEditId = $intUId;

        if ( $objAccount->updateAccountPass() ){
            $resXML["description"] = $LANG['msg'][22];
            $resXML["status"] = 0;

            Common::wrLogInfo($LANG['event'][4], "ID: $frmAccountId");
            Common::sendEmail($LANG['event'][4]);
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