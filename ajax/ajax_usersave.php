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
    $resXML["status"] = 3;
    $resXML["description"] = $LANG['msg'][35];
    Common::printXML($resXML);
    return;
}

// Variables POST del formulario
//extract($_POST, EXTR_PREFIX_ALL, "post");
$frmSaveType = ( isset($_POST["savetyp"]) ) ? (int)$_POST["savetyp"] : 0;
$frmUsrId = ( isset($_POST["usrid"]) ) ? (int)$_POST["usrid"] : 0;
$frmLdap = ( isset($_POST["ldap"]) ) ? (int)$_POST["ldap"] : 0;
$frmUsrName = ( isset($_POST["usrname"]) ) ? Common::sanitize($_POST["usrname"]) : "";
$frmUsrLogin = ( isset($_POST["usrlogin"]) ) ? Common::sanitize($_POST["usrlogin"]) : "";
$frmUsrProfile = ( isset($_POST["usrprofile"]) ) ? $_POST["usrprofile"] : "";
$frmUsrGroup = ( isset($_POST["usrgroup"]) ) ? $_POST["usrgroup"] : "";
$frmUsrEmail = ( isset($_POST["usremail"]) ) ? Common::sanitize($_POST["usremail"]) : "";
$frmUsrNotes = ( isset($_POST["usrnotes"]) ) ? Common::sanitize($_POST["usrnotes"]) : "";
$frmUsrPass = ( isset($_POST["usrpass"]) ) ? $_POST["usrpass"] : "";
$frmUsrPassV = ( isset($_POST["usrpassv"]) ) ? $_POST["usrpassv"] : "";
$frmAdminApp = ( isset($_POST["chkadminapp"]) && $_POST["chkadminapp"] == "true" ) ? 1 : 0;
$frmAdminAcc = ( isset($_POST["chkadminacc"]) && $_POST["chkadminacc"] == "true" ) ? 1 : 0;
$frmDisabled = ( isset($_POST["chkdisabled"]) && $_POST["chkdisabled"] == "true" ) ? 1 : 0;


if ( $frmSaveType == 3 ){
    Users::checkUserAccess("chpass",$frmUsrId) || die ('<DIV CLASS="error"'.$LANG['msg'][34].'</DIV');
} else {
    Users::checkUserAccess("users") || die ('<DIV CLASS="error"'.$LANG['msg'][34].'</DIV');
}

$objUser = new Users;

// Nueva cuenta o editar
if ( $frmSaveType == 1 OR $frmSaveType == 2 ){
    if ( ! $frmUsrName && ! $frmLdap ) {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][58];
    } elseif ( ! $frmUsrLogin && ! $frmLdap ) {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][90];
    } elseif ( ! $frmUsrProfile ) {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][59];
    } elseif ( ! $frmUsrGroup ) {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][60];
    } elseif ( ! $frmUsrEmail && ! $frmLdap ) {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][61];
    }

    if ( $resXML["status"] == 1 ) {
        Common::printXML($resXML);
        return;
    }

    $objUser->intUserId = $frmUsrId;
    $objUser->strName = $frmUsrName;
    $objUser->strLogin = $frmUsrLogin;
    $objUser->strEmail = $frmUsrEmail;
    $objUser->strNotes = $frmUsrNotes;
    $objUser->intGroupId = $frmUsrGroup;
    $objUser->intProfile = $frmUsrProfile;
    $objUser->blnAdminApp = $frmAdminApp;
    $objUser->blnAdminAcc = $frmAdminAcc;
    $objUser->blnDisabled = $frmDisabled;

    switch ($objUser->checkUserExist()){
        case 1:
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][62];
            break;
        case 2:
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][63];
            break;
    }

    if ( $resXML["status"] == 1 ) {
        Common::printXML($resXML);
        return;
    }

    if ( $frmSaveType == 1 ){
        if ( ! $frmUsrPass && ! $frmUsrPassV ){
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][64];
        } elseif ( $frmUsrPass != $frmUsrPassV ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][65];
        }

        if ( $resXML["status"] == 1 ) {
            Common::printXML($resXML);
            return;
        }

        $objUser->strPwd = $frmUsrPass;

        if ( $objUser->manageUser("add") ) {
            $resXML["status"] = 0;
            $resXML["description"] = $LANG['msg'][66];

            Common::wrLogInfo($LANG['event'][12],$LANG['eventdesc'][16].": $frmUsrName;");
            Common::sendEmail($LANG['event'][12]." '$frmUsrName'");
        } else {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][67];
        }
    } elseif ( $frmSaveType == 2 ){
        if ( $objUser->manageUser("update") ) {
            $resXML["status"] = 0;
            $resXML["description"] = $LANG['msg'][68];

            Common::wrLogInfo($LANG['event'][13],$LANG['eventdesc'][16].": $frmUsrName;");
            Common::sendEmail($LANG['event'][13]." '$frmUsrName' ");
        } else {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][69];
        }
    }
// Cambio de clave
} elseif ( $frmSaveType == 3 ){
    if ( ! $frmUsrPass || ! $frmUsrPassV ){
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][64];
    } elseif ( $frmUsrPass != $frmUsrPassV ) {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][65];
    }

    if ( $resXML["status"] == 1 ) {
        Common::printXML($resXML);
        return;
    }

    $objUser->intUserId = $frmUsrId;
    $objUser->strPwd = $frmUsrPass;

    if ( $objUser->manageUser("updatepass") ) {
        $resXML["status"] = 0;
        $resXML["description"] = $LANG['msg'][70];

        Common::wrLogInfo($LANG['event'][14],$LANG['eventdesc'][16].": $frmUsrLogin;");
        Common::sendEmail($LANG['event'][14]." '$frmUsrLogin'");
    } else {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][71];
    }
// Eliminar usuario
} elseif ( $frmSaveType == 4 && ! PMS_DEMOMODE ){
    $objUser->intUserId = $frmUsrId;

    if ( $objUser->manageUser("delete") ) {
        $resXML["status"] = 0;
        $resXML["description"] = $LANG['msg'][72];

        Common::wrLogInfo($LANG['event'][15], "Login: '$frmUsrLogin';");
        Common::sendEmail($LANG['event'][15]." '$frmUsrLogin'");
    } else {
        $resXML["status"] = 1;
        $resXML["description"] = $LANG['msg'][73];
    }        
} else {
    $resXML["description"] = $LANG['msg'][24];
    $resXML["status"] = 1;
}

Common::printXML($resXML);
?>