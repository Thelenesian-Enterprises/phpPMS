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

    $resXML = array( "status" => 0, "description" => "");
    
    // Comprobamos si la sesión ha caducado
    if ( check_session(TRUE) ) {
        $resXML["status"] = 3;
        $resXML["description"] = $LANG['msg'][35];
        Common::printXML($resXML);
        return;
    }

    Users::checkUserAccess("users") || die ('<DIV CLASS="error"'.$LANG['msg'][34].'</DIV');
    
    $objGroup = new Users;
    
    // Variables POST del formulario
    extract($_POST, EXTR_PREFIX_ALL, "post");

    // Nuevo grupo o editar
    if ( $post_savetyp == 1 OR $post_savetyp == 2 ){ 
        if ( ! $post_grpname ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][49];
            Common::printXML($resXML);
            return;
        }             

        $objGroup->intUGroupId = $post_grpid;
        $objGroup->strUGroupName = addslashes($post_grpname);
        $objGroup->strUGroupDesc = addslashes($post_grpdesc);
        
        if ( ! $objGroup->checkGroupExist()){
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][50];
            Common::printXML($resXML);
            return;
        }

        if ( $post_savetyp == 1 ){
            if ( $objGroup->manageGroup("add") ) {
                $resXML["status"] = 0;
                $resXML["description"] = $LANG['msg'][51];

                Common::wrLogInfo($LANG['event'][9],$post_grpname);
                Common::sendEmail($LANG['event'][9]." '$post_grpname'");
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][52];
            }
        } else if ( $post_savetyp == 2 ){
            if ( $objGroup->manageGroup("update") ) {
                $resXML["status"] = 0;
                $resXML["description"] = $LANG['msg'][53];

                Common::wrLogInfo($LANG['event'][10],$post_grpname);
                Common::sendEmail($LANG['event'][10]." '$post_grpname'");
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][54];
            }
        }

    // Eliminar grupo
    } elseif ( $post_savetyp == 3 ){
        $objGroup->intUGroupId = $post_grpid;
        
        $resGroupUse = $objGroup->checkGroupInUse();
                
        if ( is_string($resGroupUse) ) {
            $resXML["status"] = 2;
            $resXML["description"] = $LANG['msg'][56]." $resGroupUse";      
        } else {
            if ( $objGroup->manageGroup("delete") ) {
                $resXML["status"] = 0;
                $resXML["description"] = $LANG['msg'][55];

                Common::wrLogInfo($LANG['event'][11],$post_grpname);
                Common::sendEmail($LANG['event'][11]." '$post_grpname'");
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][57];
            }
        }
    } else {
        $resXML["description"] = $LANG['msg'][24];
        $resXML["status"] = 1;
    }
       
    Common::printXML($resXML);
?>