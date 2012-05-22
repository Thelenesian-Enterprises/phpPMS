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
 *
 * @author nuxsmin
 * @version 0.9b
 * @link http://www.cygnux.org/phppms
 * 
 */

    define('PMS_ROOT', '.');
    include_once (PMS_ROOT."/inc/includes.php");

    $resXML = array( "status" => 0, "description" => "");
    
    // Comprobamos si la sesión ha caducado
    if ( check_session(TRUE) ) {
        $resXML["description"] = "La sesión no se ha iniciado o ha caducado";
        printXML($resXML);
    }

    Users::checkUserAccess("users") || die ('<DIV CLASS="error">No tiene permisos para acceder a esta página.</DIV>');
    
    $objGroup = new Users;
    
    // Variables POST del formulario
    extract($_POST, EXTR_PREFIX_ALL, "post");

    // Nuevo grupo o editar
    if ( $post_savetyp == 1 OR $post_savetyp == 2 ){ 
        if ( ! $post_grpname ) {
            $resXML["status"] = 1;
            $resXML["description"] = "Es necesario un nombre de grupo";
            Common::printXML($resXML);
            return;
        }             

        $objGroup->intUGroupId = $post_grpid;
        $objGroup->strUGroupName = addslashes($post_grpname);
        $objGroup->strUGroupDesc = addslashes($post_grpdesc);
        
        if ( ! $objGroup->checkGroupExist()){
            $resXML["status"] = 1;
            $resXML["description"] = "Nombre de grupo duplicado";
            Common::printXML($resXML);
            return;
        }

        if ( $post_savetyp == 1 ){
            if ( $objGroup->manageGroup("add") ) {
                $resXML["status"] = 0;
                $resXML["description"] = "Grupo creado correctamente";

                Common::wrLogInfo("Nuevo grupo", "Nombre: $post_grpname;");
                Common::sendEmail("Nuevo grupo '$post_grpname' por '$strULogin'");
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = "Error al crear el grupo";
            }
        } else if ( $post_savetyp == 2 ){
            if ( $objGroup->manageGroup("update") ) {
                $resXML["status"] = 0;
                $resXML["description"] = "Grupo actualizado correctamente";

                Common::wrLogInfo("Modificar grupo", "Nombre: $post_grpname;");
                Common::sendEmail("Modificar grupo '$post_grpname' por '$strULogin'");
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = "Error al actualizar el grupo";
            }
        }

    // Eliminar grupo
    } elseif ( $post_savetyp == 3 ){
        $objGroup->intUGroupId = $post_grpid;
        
        $resGroupUse = $objGroup->checkGroupInUse();
                
        if ( is_string($resGroupUse) ) {
            $resXML["status"] = 2;
            $resXML["description"] = "No es posible eliminar:;;Grupo en uso por $resGroupUse";      
        } else {
            if ( $objGroup->manageGroup("delete") ) {
                $resXML["status"] = 0;
                $resXML["description"] = "Grupo eliminado correctamente";

                Common::wrLogInfo("Eliminar grupo", "Nombre: '$post_grpname';");
                Common::sendEmail("Eliminar grupo '$post_grpname' por '$strULogin'");
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = "Error al eliminar el grupo";
            }
        }
    } else {
        $resXML["description"] = "No es una acción válida";
        $resXML["status"] = 1;
    }
       
    Common::printXML($resXML);
?>