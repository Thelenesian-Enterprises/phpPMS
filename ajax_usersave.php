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
        $resXML["status"] = 3;
        $resXML["description"] = $LANG['msg'][35];
        Common::printXML($resXML);
        return;
    }
    
    // Variables POST del formulario
    extract($_POST, EXTR_PREFIX_ALL, "post");

    if ( $post_savetyp == 3 ){
        Users::checkUserAccess("chpass",$post_usrid) || die ('<DIV CLASS="error"'.$LANG['msg'][34].'</DIV');
    } else {
        Users::checkUserAccess("users") || die ('<DIV CLASS="error"'.$LANG['msg'][34].'</DIV');
    }
    
    $objUser = new Users;

    // Nueva cuenta o editar
    if ( $post_savetyp == 1 OR $post_savetyp == 2 ){
        if ( ! $post_usrname && ! $post_ldap ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][58];
        } elseif ( ! $post_usrlogin && ! $post_ldap ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][90];
        } elseif ( $post_usrprofile == "" ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][59];
        } elseif ( $post_usrgroup == "" ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][60];
        } elseif ( ! $post_usremail && ! $post_ldap ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][61];
        }
            
        if ( $resXML["status"] == 1 ) {
            Common::printXML($resXML);
            return;
        }

        $objUser->intUserId = $post_usrid;
        $objUser->strName = addslashes($post_usrname);
        $objUser->strLogin = addslashes($post_usrlogin);
        $objUser->strEmail = addslashes($post_usremail);
        $objUser->strNotes = addslashes($post_usrnotes);
        $objUser->intGroupId = $post_usrgroup;
        $objUser->intProfile = $post_usrprofile;
        $objUser->blnAdminApp = ( $post_chkadminapp == "true" ) ? 1 : 0 ;
        $objUser->blnAdminAcc = ( $post_chkadminacc == "true" ) ? 1 : 0 ;
        $objUser->blnDisabled = ( $post_chkdisabled == "true" ) ? 1 : 0;
        
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

        if ( $post_savetyp == 1 ){
            if ( $post_usrpass == "" && $post_usrpassv == "" ){
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][64];
            } elseif ( $post_usrpass != $post_usrpassv ) {
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][65];
            }

            if ( $resXML["status"] == 1 ) {
                Common::printXML($resXML);
                return;
            }
            
            $objUser->strPwd = $post_usrpass;
            
            if ( $objUser->manageUser("add") ) {
                $resXML["status"] = 0;
                $resXML["description"] = $LANG['msg'][66];

                Common::wrLogInfo($LANG['event'][12],$LANG['eventdesc'][16].": $post_usrname;");
                Common::sendEmail($LANG['event'][12]." '$post_usrname'");
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][67];
            }
        } elseif ( $post_savetyp == 2 ){
            if ( $objUser->manageUser("update") ) {
                $resXML["status"] = 0;
                $resXML["description"] = $LANG['msg'][68];

                Common::wrLogInfo($LANG['event'][13],$LANG['eventdesc'][16].": $post_usrname;");
                Common::sendEmail($LANG['event'][13]." '$post_usrname' ");
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][69];
            }
        }

    // Cambio de clave
    } elseif ( $post_savetyp == 3 ){
        if ( $post_usrpass == "" && $post_usrpassv == "" ){
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][64];
        } elseif ( $post_usrpass != $post_usrpassv ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][65];
        }
        
        if ( $resXML["status"] == 1 ) {
            Common::printXML($resXML);
            return;
        }

        $objUser->intUserId = $post_usrid;
        $objUser->strPwd = $post_usrpass;

        if ( $objUser->manageUser("updatepass") ) {
            $resXML["status"] = 0;
            $resXML["description"] = $LANG['msg'][70];

            Common::wrLogInfo($LANG['event'][14],$LANG['eventdesc'][16].": $post_usrlogin;");
            Common::sendEmail($LANG['event'][14]." '$post_usrlogin'");
        } else {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][71];
        }
    // Eliminar usuario
    } elseif ( $post_savetyp == 4 ){
        $objUser->intUserId = $post_usrid;
                
        if ( $objUser->manageUser("delete") ) {
            $resXML["status"] = 0;
            $resXML["description"] = $LANG['msg'][72];

            Common::wrLogInfo($LANG['event'][15], "Login: '$post_usrlogin';");
            Common::sendEmail($LANG['event'][15]." '$post_usrlogin'");
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