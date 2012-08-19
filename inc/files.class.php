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

class Files {
    
    private $dbh;

    function __construct() {
        $objConfig = new Config;
        $this->dbh = $objConfig->connectDb();
    }
    
    public function getMaxUpload(){
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit);
        
        Common::wrLogInfo(__FUNCTION__,"Max. PHP upload: ".$upload_mb."MB");        
    }


    // Función para subir el archivo
    public function fileUpload($intAccId){
        global $LANG;
        
        $objConfig = new Config();
                
        $allowedExts = $objConfig->getConfigValue("allowed_exts");
        $allowedSize = $objConfig->getConfigValue("allowed_size");
        
        if ( $allowedExts ){
            // Extensiones aceptadas
            $extsOk = explode(",",$objConfig->getConfigValue("allowed_exts"));
        } else {
            echo $LANG['msg'][95];
            return FALSE;            
        }
         
        $validated = 0;

        if ( $_FILES && $_FILES['file']['name'] ){   
            // Comprobamos la extensión del archivo
            $fileExt = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);

            if ( ! in_array(strtolower($fileExt), $extsOk) ){
                echo $LANG['msg'][86]." '$fileExt'";
                return;
            }
        } else{
            echo $LANG['msg'][93].":<br />".$_FILES['file']['name'];
            return;
        }

        // Variables con información del archivo
        $fileName = $_FILES['file']['name'];
        $tmpName  = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];

        if ( ! file_exists($tmpName) ){
            // Registramos el máximo tamaño permitido por PHP
            $this->getMaxUpload();
            
            echo $LANG['msg'][81];
            return FALSE;            
        }
        
        if( $fileSize > ($allowedSize * 1000) ){
            echo $LANG['msg'][80]." ".round(($allowedSize / 1000),1)."MB";
            return FALSE;
        }
        
        // Leemos el archivo a una variable
        $fileHandle = fopen($tmpName, 'r');
        
        if ( ! $fileHandle ){
            echo $LANG['msg'][81];
            Common::wrLogInfo(__FUNCTION__,$LANG['msg'][81]);        
            return FALSE;
        }
        
        $fileData = fread($fileHandle, filesize($tmpName));
        $fileData = addslashes($fileData);
        fclose($fileHandle);

        $fileName = $this->dbh->real_escape_string($fileName);

        $strQuery = "INSERT INTO files SET intAccountId = ".(int)$intAccId.", 
                    vacName = '".$this->dbh->real_escape_string($fileName)."', 
                    vacType = '$fileType', intSize = '$fileSize', 
                    blobContent = '$fileData', 
                    vacExtension = '".$this->dbh->real_escape_string($fileExt)."'";
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
        }
        
        echo ( $resQuery) ? $LANG['msg'][82] : $LANG['msg'][83];
    }

    // Función para descargar el archivo
    public function fileDownload($fileId,$view = FALSE){
        global $LANG;
        
        // Verificamos que el ID sea numérico
        if( ! is_numeric($fileId) ){
            echo $LANG['msg'][84];
            return FALSE;
        }

        // Obtenemos el archivo de la BBDD
        $strQuery = "SELECT * FROM files WHERE intId = ".(int)$fileId." LIMIT 1";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error);
            return FALSE;
        }
        
        $resResult = $resQuery->fetch_assoc();
        $resQuery->free();

        if( ! $resResult ){
            echo $LANG['msg'][85];
            return FALSE;
        }
        
        $fileSize = $resResult['intSize'];
        $fileType = $resResult['vacType'];
        $fileName = $resResult['vacName'];
        $fileExt = $resResult['vacExtension'];
        $fileData = $resResult['blobContent'];

        if ( ! $view ){
            // Enviamos el archivo al navegador
            header("Content-length: $fileSize");
            header("Content-type: $fileType");
            header("Content-Disposition: attachment; filename=$fileName");
            
            echo $fileData;
        } else {
            $extsOkImg = array("JPG","GIF","PNG");
            if ( in_array(strtoupper($fileExt), $extsOkImg) ){
                $imgData = chunk_split(base64_encode($fileData));
                echo '<img src="data:'.$fileType.';base64, '.$imgData.'" border="0" />';
//            } elseif ( strtoupper($fileExt) == "PDF" ){
//                echo '<object data="data:application/pdf;base64, '.base64_encode($fileData).'" type="application/pdf"></object>';
            } elseif ( strtoupper($fileExt) == "TXT" ){
                echo '<div id="fancyView" class="backGrey"><pre>'.$fileData.'</pre></div>';
            } else{
                echo '<div id="fancyView" class="msgError" >'.$LANG['msg'][86].'</div>';
            }
        }
        
    }

    public function fileDelete($fileId){
        global $LANG;
        
        // Verificamos que el ID sea numérico
        if(!is_numeric($fileId)){
            echo $LANG['msg'][84];
            return FALSE;
        }

        // Eliminamos el archivo de la BBDD
        $strQuery = "DELETE FROM files WHERE intId = ".(int)$fileId;  
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
        }
        
        echo ( $resQuery ) ? $LANG['msg'][87] : $LANG['msg'][88];
    }

    // Función para generar el listado de archivos guardados
    public function mkFileList($intAccId,$blnDelete){
        global $LANG;
        
        // Obtenemos los archivos de la BBDD para dicha cuenta
        $strQuery = "SELECT intId, vacName, intSize FROM files WHERE intAccountId = ".(int)$intAccId;
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        // Mostramos el listado de archivos para descargarlos
        echo '<form action="ajax_files.php" method="POST" name="files_form" id="files_form">';
        echo '<select name="fileId" size="4" class="files" id="files">';
        while ($file = $resQuery->fetch_assoc()) {
            $fileId = $file["intId"];
            $fileName = $file["vacName"];
            $fileSize = round ($file["intSize"] / 1000,2);

            echo "<option value='$fileId'>$fileName ($fileSize KB)</option>";
        }
        echo '</select>';
        echo '<input name="action" type="hidden" id="action" value="download">';
        echo '</form>';
        echo '<DIV CLASS="actionFiles">';
        echo '<IMG SRC="imgs/download.png" TITLE="'.$LANG['buttons'][22].'" ID="btnDownload" CLASS="inputImg" OnClick="downFile();" />';
        echo '<IMG SRC="imgs/view.png" TITLE="'.$LANG['buttons'][23].'" ID="btnView" CLASS="inputImg" OnClick="downFile(1);" />';

        if ( $blnDelete != 0) echo '<IMG SRC="imgs/delete.png" TITLE="'.$LANG['buttons'][24].'" ID="btnDelete" CLASS="inputImg" OnClick="delFile('.$intAccId.');" />';
        echo '</DIV>';      
        
        $resQuery->free();
    }

    public function countFiles($intAccId){
        // Obtenemos los archivos de la BBDD para dicha cuenta
        $strQuery = "SELECT intId FROM files WHERE intAccountId = ".(int)$intAccId;  
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resResult = $resQuery->num_rows;
        $resQuery->free();
        
        if( is_numeric($resResult) ) return $resResult;
    }
}
?>