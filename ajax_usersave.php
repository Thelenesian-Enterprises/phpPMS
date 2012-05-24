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

    define('PMS_ROOT', '.');
    include_once (PMS_ROOT."/inc/includes.php");

    $resXML = array( "status" => 0, "description" => "");
    
    // Comprobamos si la sesión ha caducado
    if ( check_session(TRUE) ) {
        $resXML["status"] = 3;
        $resXML["description"] = "La sesión no se ha iniciado o ha caducado";
        Common::printXML($resXML);
        return;
    }
    
    // Variables POST del formulario
    extract($_POST, EXTR_PREFIX_ALL, "post");

    if ( $post_savetyp == 3 ){
        Users::checkUserAccess("chpass",$post_usrid) || die ('<DIV CLASS="error">No tiene permisos para acceder a esta página.</DIV>');        
    } else {
        Users::checkUserAccess("users") || die ('<DIV CLASS="error">No tiene permisos para acceder a esta página.</DIV>');
    }
    
    $objUser = new Users;

    // Nueva cuenta o editar
    if ( $post_savetyp == 1 OR $post_savetyp == 2 ){
        if ( ! $post_usrname && ! $post_ldap ) {
            $resXML["status"] = 1;
            $resXML["description"] = "Es necesario un nombre de usuario";
        } elseif ( ! $post_usrlogin && ! $post_ldap ) {
            $resXML["status"] = 1;
            $resXML["description"] = "Es necesario un login";
        } elseif ( $post_usrprofile == "" ) {
            $resXML["status"] = 1;
            $resXML["description"] = "Es necesario un perfil";
        } elseif ( $post_usrgroup == "" ) {
            $resXML["status"] = 1;
            $resXML["description"] = "Es necesario un grupo";
        } elseif ( ! $post_usremail && ! $post_ldap ) {
            $resXML["status"] = 1;
            $resXML["description"] = "Es necesario un email";
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
        $objUser->blnAdmin = $post_chkadmin;
        $objUser->blnDisabled = $post_chkdisabled;
        
        switch ($objUser->checkUserExist()){
            case 1:
                $resXML["status"] = 1;
                $resXML["description"] = "Login de usuario duplicado";
                break;
            case 2:
                $resXML["status"] = 1;
                $resXML["description"] = "Email de usuario duplicado";
                break;
        }
        
        if ( $resXML["status"] == 1 ) {
            Common::printXML($resXML);
            return;
        }

        if ( $post_savetyp == 1 ){
            if ( $post_usrpass == "" && $post_usrpassv == "" ){
                $resXML["status"] = 1;
                $resXML["description"] = "La clave no puede estar en blanco";
            } elseif ( $post_usrpass != $post_usrpassv ) {
                $resXML["status"] = 1;
                $resXML["description"] = "Las claves no coinciden";
            }

            if ( $resXML["status"] == 1 ) {
                Common::printXML($resXML);
                return;
            }
            
            $objUser->strPwd = $post_usrpass;
            
            if ( $objUser->manageUser("add") ) {
                $resXML["status"] = 0;
                $resXML["description"] = "Usuario creado correctamente";

                Common::wrLogInfo("Nuevo usuario", "Nombre: $post_usrname;");
                Common::sendEmail("Nuevo usuario '$post_usrname' por '$strULogin'");
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = "Error al crear el usuario";
            }
        } elseif ( $post_savetyp == 2 ){
            if ( $objUser->manageUser("update") ) {
                $resXML["status"] = 0;
                $resXML["description"] = "Usuario actualizado correctamente";

                Common::wrLogInfo("Modificar usuario", "Nombre: $post_usrname;");
                Common::sendEmail("Modificar usuario '$post_usrname' por '$strULogin'");
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = "Error al actualizar el usuario";
            }
        }

    // Cambio de clave
    } elseif ( $post_savetyp == 3 ){
        if ( $post_usrpass == "" && $post_usrpassv == "" ){
            $resXML["status"] = 1;
            $resXML["description"] = "La clave no puede estar en blanco";
        } elseif ( $post_usrpass != $post_usrpassv ) {
            $resXML["status"] = 1;
            $resXML["description"] = "Las claves no coinciden";
        }
        
        if ( $resXML["status"] == 1 ) {
            Common::printXML($resXML);
            return;
        }

        $objUser->intUserId = $post_usrid;
        $objUser->strPwd = $post_usrpass;

        if ( $objUser->manageUser("updatepass") ) {
            $resXML["status"] = 0;
            $resXML["description"] = "Clave actualizada correctamente";

            Common::wrLogInfo("Modificar clave usuario", "Nombre: $post_usrlogin;");
            Common::sendEmail("Modificar clave usuario '$post_usrlogin' por '$strULogin'");
        } else {
            $resXML["status"] = 1;
            $resXML["description"] = "Error al modificar la clave";
        }
    // Eliminar usuario
    } elseif ( $post_savetyp == 4 ){
        $objUser->intUserId = $post_usrid;
                
        if ( $objUser->manageUser("delete") ) {
            $resXML["status"] = 0;
            $resXML["description"] = "Usuario eliminado correctamente";

            Common::wrLogInfo("Eliminar usuario", "Login: '$post_usrlogin';");
            Common::sendEmail("Eliminar usuario '$post_usrlogin' por '$strULogin'");
        } else {
            $resXML["status"] = 1;
            $resXML["description"] = "Error al eliminar el usuario";
        }        
    } else {
        $resXML["description"] = "No es una acción válida";
        $resXML["status"] = 1;
    }
       
    Common::printXML($resXML);
?>