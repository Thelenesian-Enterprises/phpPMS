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
 * Clase para la gestión de las categorías
 *
 * @author nuxsmin
 * @version 0.9b
 * @link http://www.cygnux.org/phppms
 */

class Category{

    private $dbh;

    function __construct() {
        $objConfig = new Config;
        $this->dbh = $objConfig->connectDb();
    }
        
    function getCategoryIdByName ($strCategoryName) {
        $strQuery = "SELECT intCategoryId FROM categories WHERE vacCategoryName = '".$strCategoryName."' LIMIT 1";
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $intRowCount = $resQuery->num_rows;

        if ( $intRowCount == 0 ) {
            return FALSE;
        } else {
            $resResult = $resQuery->fetch_assoc();
            $intCategoryId = $resResult["intCategoryId"];
        }

        return $intCategoryId;
    }

    function getCategoryId($intCategoryId) {
        $strQuery = "SELECT intCategoryId FROM categories WHERE intCategoryId = $intCategoryId LIMIT 1";
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $intRowCount = $resQuery->num_rows;

        if ( $intRowCount == 0 ) {
            $intCategoryId = 0;
        } else {
            $resResult = $resQuery->fetch_assoc();
            $intCategoryId = $resResult["intCategoryId"];
        }

        return $intCategoryId;
    }
    
    function categoryAdd($strCategoryName) {
        $strQuery = "INSERT INTO categories (vacCategoryName) VALUES ('".$strCategoryName."')";
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return $resQuery;
    }

    function isCategoryInUse($intCategoryId) {
        $strQuery = "SELECT intCategoryFid FROM accounts WHERE intCategoryFid = ".$intCategoryId;
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $intCategoryCount = $resQuery->num_rows;
        
        if ( $intCategoryCount > 0) return TRUE;
        
        return FALSE;
    }

    function categoryDel($intCategoryId) {
        $strQuery = "DELETE FROM categories WHERE intCategoryId = $intCategoryId";
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return $resQuery;
    }

    function editCategoryById($intCategoryId, $strCategoryNameNew) {
        $strQuery = "UPDATE categories SET vacCategoryName = '$strCategoryNameNew' WHERE intCategoryId = $intCategoryId";
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return $resQuery;
    }
}
?>