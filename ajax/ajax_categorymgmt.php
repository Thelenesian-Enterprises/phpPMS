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

$intCategoryFunction = ( isset($_POST["categoryFunction"]) ) ? (int)$_POST["categoryFunction"] : 0;
$strCategoryName = ( isset($_POST["categoryName"]) ) ? Common::sanitize($_POST["categoryName"]) : "";
$strCategoryNameNew = ( isset($_POST["categoryNameNew"]) ) ? Common::sanitize($_POST["categoryNameNew"]) : "";
$intCategoryId = ( isset($_POST["categoryId"]) ) ? (int)$_POST["categoryId"] : 0;

$objCategory = new Category;

switch ($intCategoryFunction) {
    case 1:
        if ( $strCategoryName == "" ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][25];
        } else {
            // Comprobamos si la categoría existe
            if ( ! $objCategory->getCategoryIdByName($strCategoryName) ) {
                if ( $objCategory->categoryAdd($strCategoryName) ){
                    $resXML["status"] = 0;
                    $resXML["description"] = $LANG['msg'][26];
                    Common::wrLogInfo($LANG['event'][6], $strCategoryName);
                } else {
                    $resXML["status"] = 1;
                    $resXML["description"] = $LANG['msg'][27];
                }
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][28];
            }
        }
        break;
    case 2:
        if ( $strCategoryNameNew == "" ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][25];
        } else {
            // Comprobamos si la categoría existe
            if ( $objCategory->getCategoryIdByName($strCategoryNameNew) != 0 ) {
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][28];
            } else {
                if ( $objCategory->editCategoryById($intCategoryId, $strCategoryNameNew) ){
                    $resXML["status"] = 0;
                    $resXML["description"] = $LANG['msg'][29];
                    Common::wrLogInfo($LANG['event'][7], $intCategoryId." -> ".$strCategoryNameNew);
                } else {
                    $resXML["status"] = 1;
                    $resXML["description"] = $LANG['msg'][30];
                }
            }
        }
        break;
    case 3:
        // Comprobamos si la categoría está en uso por una cuenta
        if ( $objCategory->isCategoryInUse($intCategoryId) ) {
            $resXML["status"] = 1;
            $resXML["description"] = $LANG['msg'][31];
        } else {
            if ( $objCategory->categoryDel($intCategoryId) ){
                $resXML["status"] = 0;
                $resXML["description"] = $LANG['msg'][32];
                Common::wrLogInfo($LANG['event'][8], $intCategoryId);
            } else {
                $resXML["status"] = 1;
                $resXML["description"] = $LANG['msg'][33];
            }
        }
        break;
    default:
        $resXML["description"] = $LANG['msg'][24];
        $resXML["status"] = 1;
}

common::printXML($resXML);
?>