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
    check_session();

    global $CFG_PMS;

    $objCommon = new Common;
    
    $siteName = $CFG_PMS["siteshortname"];
    $doBackup = $_POST["doBackup"];
    
    $bakDirPMS = dirname(__FILE__);
    $bakFilePMS = $bakDirPMS.'/backup/'.$siteName.'.tgz';
   
    Common::printHeader(FALSE,TRUE);
?>
    <BODY>
        <?php 
            Common::printBodyHeader();
            Users::checkUserAccess("backup") || die ('<DIV CLASS="error">No tiene permisos para acceder a esta página.</DIV>');
        ?>
	<DIV ID="container" ALIGN="center">
            <H2>Backup <? echo $CFG_PMS["siteshortname"]; ?></H2>
            <DIV CLASS="action midround">
                <FORM ACTION="pmsbackup.php" METHOD="post" NAME="frmBackup">
                      <INPUT TYPE="hidden" NAME="doBackup" VALUE="1" />                    
                </FORM>
                <IMG SRC="imgs/backup.png" TITLE="Realizar backup" CLASS="inputImg" OnClick="document.frmBackup.submit();" />
                <?php $objCommon->printBackLinks(TRUE); ?>
            </DIV>              
            <TABLE CLASS="data">
                <TR>
                    <TD CLASS="descCampo">Resultado</TD>
                    <TD>
                    <?php
                    if ( $doBackup == 1 ){
                        $objConfig = new Config;
        
                        $arrOut = $objConfig->doDbBackup(dirname(__FILE__));
                        if ( $arrOut ){
                            foreach ($arrOut as $strOut){
                                echo ( $strOut != "" ) ? $strOut."<br />" : "";
                            }
                            echo "Proceso de backup finalizado";
                        } else {
                            echo "Error al realizar el backup";
                        }
                        
                    } else {
                        echo ( file_exists($bakFilePMS) ) ? "Último backup: ".date ("F d Y H:i:s.", filemtime($bakFilePMS)) : "No se encontraron backups" ;
                    }		
                    ?>
                    </TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Descargar</TD>
                    <TD>
                    <?php echo (file_exists($bakFilePMS)) ? '<a href="backup/'.$siteName.'_db.sql">BBDD</a> - <a href="backup/'.$siteName.'.tgz">'.$siteName.'</a>' : "No hay backups para descargar" ; ?>
                    </TD>
                </TR>
            </TABLE>
            <DIV ID="resAccion"></DIV>
<?php Common::PrintFooter(); ?>