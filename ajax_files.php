<?
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

// TODO: comprobar permisos para eliminar archivos

    define('PMS_ROOT', '.');
    include_once (PMS_ROOT . "/inc/includes.php");

    if ( $_GET['id'] ){
        $accountId = $_GET['id'];
        $blnDelete = $_GET['del'];

        $objFile = new Files;
        $objFile->mkFileList($accountId,$blnDelete);
        return;
    } 
    
    switch ($_POST['action']){
        case "upload":
            $accountId = $_POST['accountId'];

            $objFile = new Files;
            $objFile->fileUpload($accountId);
            break;
        case "download":
            $fileId = $_POST['fileId'];

            $objFile = new Files;
            $objFile->fileDownload($fileId);
            break;
        case "delete":
            $fileId = $_POST['fileId'];

            $objFile = new Files;
            $objFile->fileDelete($fileId);
            break;
    }
?>
