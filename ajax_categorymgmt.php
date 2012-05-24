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
    
    // Comprobamos si la sesión ha caducado
    if ( check_session(TRUE) ) {
        $resXML["status"] = 1;
        $resXML["description"] = "La sesión no se ha iniciado o ha caducado";
        Common::printXML($resXML);
        return;
    }

    Users::checkUserAccess("config") || die ('<DIV CLASS="error">No tiene permisos para acceder a esta página.</DIV>');
    
    $intCategoryFunction = $_POST["categoryFunction"];
    $strCategoryName = $_POST["categoryName"];
    $strCategoryNameNew = $_POST["categoryNameNew"];
    $intCategoryId = $_POST["categoryId"];

    $objCategory = new Category;

    switch ($intCategoryFunction) {
        case 1:
            if ( $strCategoryName == "" ) {
                $resXML["status"] = 1;
                $resXML["description"] = "Es obligatorio indicar un nombre";
            } else {
                // Comprobamos si la categoría existe
                if ( $objCategory->getCategoryIdByName($strCategoryName) == 0 ) {
                    if ( $objCategory->categoryAdd($strCategoryName) ){
                        $resXML["status"] = 0;
                        $resXML["description"] = "Categoría añadida correctamente";
                        Common::wrLogInfo("Categoría nueva", $strCategoryName);
                    } else {
                        $resXML["status"] = 1;
                        $resXML["description"] = "Error al añadir la categoría";
                    }
                } else {
                    $resXML["status"] = 1;
                    $resXML["description"] = "Ya existe una categoría con ese nombre";                        
                }
            }
            break;
        case 2:
            if ( $strCategoryNameNew == "" ) {
                $resXML["status"] = 1;
                $resXML["description"] = "Es obligatorio indicar el nombre de la categoría";
            } else {
                // Comprobamos si la categoría existe
                if ( $objCategory->getCategoryIdByName("$strCategoryNameNew") != 0 ) {
                    $resXML["status"] = 1;
                    $resXML["description"] = "Ya existe una categoría con ese nombre";                        
                } else {
                    if ( $objCategory->editCategoryById($intCategoryId, $strCategoryNameNew) ){
                        $resXML["status"] = 0;
                        $resXML["description"] = "Categoría modificada correctamente";
                        Common::wrLogInfo("Categoría modificada", $intCategoryId." -> ".$strCategoryNameNew);
                    } else {
                        $resXML["status"] = 1;
                        $resXML["description"] = "Error al modificar la categoría";
                    }
                }
            }
            break;
        case 3:
            // Comprobamos si la categoría está en uso por una cuenta
            if ( $objCategory->isCategoryInUse($intCategoryId) ) {
                $resXML["status"] = 1;
                $resXML["description"] = "Categoría en uso, no es posible eliminar";                        
            } else {
                if ( $objCategory->categoryDel($intCategoryId) ){
                    $resXML["status"] = 0;
                    $resXML["description"] = "Categoría eliminada correctamente";
                    Common::wrLogInfo("Categoría eliminada", $intCategoryId);
                } else {
                    $resXML["status"] = 1;
                    $resXML["description"] = "Error al eliminar la categoría";
                }
            }
            break;
    }
    
    common::printXML($resXML);
?>