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

// TODO: comprobar permisos para eliminar archivos

    define('PMS_ROOT', '..');
    include_once (PMS_ROOT . "/inc/includes.php");
    
    $objConfig = new Config();
    
    if ( $objConfig->getConfigValue("filesenabled") == 0 ){
        echo $LANG['msg'][96];
        return FALSE;              
    }

    if ( isset($_GET['id']) ){
        $accountId = (int)$_GET['id'];
        $blnDelete = $_GET['del'];

        $objFile = new Files;
        $objFile->mkFileList($accountId,$blnDelete);
        return;
    } 
    
    $action = ( isset($_POST["action"]) ) ? Common::sanitize($_POST["action"]) : "";
    
    switch ( $action ){
        case "upload":
            $accountId = (int)$_POST['accountId'];

            $objFile = new Files;
            $objFile->fileUpload($accountId);
            break;
        case "download":
            $fileId = (int)$_POST['fileId'];

            $objFile = new Files;
            $objFile->fileDownload($fileId);
            break;
        case "view":
            $fileId = (int)$_POST['fileId'];

            $objFile = new Files;
            $objFile->fileDownload($fileId,TRUE);
            break;        
        case "delete":
            $fileId = (int)$_POST['fileId'];

            $objFile = new Files;
            $objFile->fileDelete($fileId);
            break;
    }
?>