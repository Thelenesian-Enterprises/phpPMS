<?
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

class Files {
    
    private $dbh;

    function __construct() {
        $objConfig = new Config;
        $this->dbh = $objConfig->connectDb();
    }
    
    // Función para subir el archivo
    public function fileUpload($intAccId){
        // Extensiones aceptadas.
        $extsOk = array("PDF","JPG","GIF","PNG","ODT","ODS","DOC","DOCX","XLS","XSL","VSD","TXT","CSV","LIC","PPK");

        $validated = 0;

        if( $_FILES && $_FILES['file']['name'] ){   
            // Comprobamos la extensión del archivo
            $fileExt = pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);

            if ( ! in_array(strtoupper($fileExt), $extsOk) ){
                echo "Extensión no permitida '$fileExt'";
                return;
            }
        } else{
            echo "Archivo inválido:<br />".$_FILES['file']['name'];
            return;
        }

        // Variables con información del archivo
        $fileName = $_FILES['file']['name'];
        $tmpName  = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];

        if( $fileSize > 1024000 ){
            echo "El archivo es mayor de 1M";
            return FALSE;
        }

        // Leemos el archivo a una variable
        $fileHandle = fopen($tmpName, 'r');
        
        if ( ! $fileHandle ){
            echo "Error interno al leer el archivo";
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
        
        echo ( $resQuery) ? "Archivo guardado correctamente" : "No se pudo guardar el archivo";
    }

    // Función para descargar el archivo
    public function fileDownload($fileId,$view = FALSE){
        // Verificamos que el ID sea numérico
        if( ! is_numeric($fileId) ){
            echo "No es un ID de archivo válido";
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
            echo "El archivo no existe";
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
                echo '<div id="fancyView" class="fancyErr" ><span class="altTxtRed">Tipo de archivo no soportado</span></div>';
            }
        }
        
    }

    public function fileDelete($fileId){
        // Verificamos que el ID sea numérico
        if(!is_numeric($fileId)){
            echo "No es un ID de archivo válido";
            return FALSE;
        }

        // Eliminamos el archivo de la BBDD
        $strQuery = "DELETE FROM files WHERE intId = ".(int)$fileId;  
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
        }
        
        echo ( $resQuery ) ? "Archivo eliminado correctamente" : "Error al eliminar el archivo";
    }

    // Función para generar el listado de archivos guardados
    public function mkFileList($intAccId,$blnDelete){
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
        echo '<IMG SRC="imgs/download.png" TITLE="Descargar archivo" ID="btnDownload" CLASS="inputImg" OnClick="downFile();" />';
        echo '<IMG SRC="imgs/view.png" TITLE="Ver archivo" ID="btnView" CLASS="inputImg" OnClick="downFile(1);" />';

        if ( $blnDelete != 0) echo '<IMG SRC="imgs/delete.png" TITLE="Eliminar archivo" ID="btnDelete" CLASS="inputImg" OnClick="delFile('.$intAccId.');" />';
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