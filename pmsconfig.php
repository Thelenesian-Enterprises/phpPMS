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
    check_session();

    $objConfig = new Config;
    $objAccount = new Account;
    $objCommon = new Common;
    
    foreach ($_POST as $varPost => $varPostValue){
        if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
            $objCommon->arrBackLinks["$varPost"] = $varPostValue;
        }
    }

    $intAccountCount = $objConfig->getConfigValue("account_count");

    Common::printHeader(FALSE,TRUE);
?>
    <BODY>
        <?php 
            Common::printBodyHeader(); 
            Users::checkUserAccess("config") || die ('<DIV CLASS="error">No tiene permisos para acceder a esta página.</DIV>');
        ?>
        <DIV ID="container" ALIGN="center">
        <H2>Configuración</H2>
        <DIV CLASS="action midround">
            <?php $objCommon->printBackLinks(TRUE); ?>
        </DIV>          
        <DIV CLASS="section"">Categorías</DIV>
            <TABLE CLASS="data">
                <TR>
                    <TD CLASS="descCampo">Nueva categoría</TD>
                    <TD WIDTH="75%">
                        <FORM OnSubmit="return configMgmt('addcat');" METHOD="post" NAME="frmAddCategory" ID="frmAddCategory">
                            <INPUT TYPE="text" NAME="categoryName" SIZE="28" MAXLENGTH="255">
                            <INPUT TYPE="hidden" NAME="categoryFunction" VALUE="1">
                            <INPUT TYPE="image" SRC="imgs/add.png" TITLE="Nueva" CLASS="inputImg" ID="btnAdd" />
                        </FORM>
                    </TD>
                </TR>
                <TR>
                    <TD CLASS="descCampo">Modificar categoría</TD>
                    <TD WIDTH="75%">
                        <FORM OnSubmit="return configMgmt('editcat');" METHOD="post" NAME="frmEditCategory" ID="frmEditCategory">
                            <SELECT NAME="categoryId" SIZE="1">
    <?php
                                foreach ( $objAccount->getCategorias() as $catName => $catId){
                                    echo "<OPTION VALUE='".$catId."'>".$catName."</OPTION>";
                                }       

    ?>
                            </SELECT>
                            <BR /><BR />
                            <INPUT TYPE="text" NAME="categoryNameNew" SIZE="15">
                            <INPUT TYPE="hidden" NAME="categoryFunction" VALUE="2">
                            <INPUT TYPE="image" SRC="imgs/save.png" TITLE="Guardar" CLASS="inputImg" ID="btnGuardar" />
                        </FORM>
                    </TD>
                </TR>
                <TR>
                    <TD CLASS="descCampo">Borrar categoría</TD>
                    <TD WIDTH="75%">
                        <FORM OnSubmit="return configMgmt('delcat');" METHOD="post" NAME="frmDelCategory" ID="frmDelCategory">
                            <SELECT NAME="categoryId" SIZE="1">
    <?php
                                foreach ( $objAccount->getCategorias() as $catName => $catId){
                                    echo "<OPTION VALUE='".$catId."'>".$catName."</OPTION>";
                                }       

    ?>
                            </SELECT>
                            <INPUT TYPE="hidden" NAME="categoryFunction" VALUE="3">
                            <INPUT TYPE="image" SRC="imgs/delete.png" title="Eliminar" class="inputImg" />
                        </FORM>
                    </TD>
                </TR>
            </TABLE>
            <DIV CLASS="section"">Opciones de Visualización</DIV>
            <DIV CLASS="action midround">
                <IMG SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" OnClick="configMgmt('saveconfig');">
            </DIV>        
            <TABLE CLASS="data">
                <FORM METHOD="post" NAME="frmConfig" ID="frmConfig">
                <TR>
                    <TD CLASS="descCampo">Mostrar claves encriptadas</TD>
                    <TD>
                        <SELECT NAME="password_show" SIZE="1">
    <?php               if ( $objConfig->getConfigValue("password_show") == "TRUE" ) { // FIXME ?>
                            <OPTION SELECTED>TRUE</OPTION>
                            <OPTION>FALSE</OPTION>
    <?php               } else { ?>
                            <OPTION>TRUE</OPTION>
                            <OPTION SELECTED>FALSE</OPTION>
    <?php               } ?>
                        </SELECT>
                    </TD>
                </TR>
                <TR>
                    <TD CLASS="descCampo">Usar nombre de cuenta como enlace</TD>
                    <TD>
                        <SELECT NAME="account_link" SIZE="1">
    <?php               if ( $objConfig->getConfigValue("account_link") == "TRUE" ) { // FIXME ?>
                            <OPTION SELECTED>TRUE</OPTION>
                            <OPTION>FALSE</OPTION>
    <?php               } else { ?>
                            <OPTION>TRUE</OPTION>
                            <OPTION SELECTED>FALSE</OPTION>
    <?php               } ?>
                        </SELECT>
                    </TD>
                </TR>
                <TR>
                    <TD CLASS="descCampo">Resultados de búsqueda por página</TD>
                    <TD>
                        <SELECT NAME="account_count" SIZE="1">
    <?php
                        $arrAccountCount = array(1,2,3,5,10,15,20,25,30,50,"all");

                        foreach ($arrAccountCount as $num ){
                            if ( $intAccountCount == $num){
                                echo "<OPTION SELECTED>$num</OPTION>";
                            } else {
                                echo "<OPTION>$num</OPTION>";
                            }
                        }
    ?>
                        </SELECT>
                    </TD>
                </TR>
                <TR>
                    <TD CLASS="descCampo">Comprobar clave al desencriptar</TD>
                    <TD>
                        <INPUT TYPE="hidden" NAME="md5_pass_old" VALUE="<?php echo ($objConfig->getConfigValue("md5_pass")) ?>">
                        <SELECT NAME="md5_pass" SIZE="1">
    <?php               if ( $objConfig->getConfigValue("md5_pass") == "TRUE" ) { // FIXME ?>
                            <OPTION SELECTED>TRUE</OPTION>
                            <OPTION>FALSE</OPTION>
    <?php               } else { ?>
                            <OPTION>TRUE</OPTION>
                            <OPTION SELECTED>FALSE</OPTION>
    <?php               } ?>
                        </SELECT>
                    </TD>
                </TR>
                <INPUT TYPE="hidden" NAME="action" VALUE="config" />
                </FORM>            
            </TABLE>
            <DIV CLASS="section">Opciones de Clave Maestra</DIV>
            <DIV CLASS="action midround">
                <IMG SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" OnClick="configMgmt('savempwd');">
            </DIV>
            <TABLE CLASS="data">
                <FORM METHOD="post" NAME="frmCrypt" ID="frmCrypt">
                <TR>
                    <TD CLASS="descCampo">Clave Maestra Actual</TD>
                    <TD><INPUT TYPE="password" NAME="curMasterPwd" SIZE="20" MAXLENGTH="255"></TD>
                </TR>            
                <TR>
                    <TD CLASS="descCampo">Clave Maestra</TD>
                    <TD><INPUT TYPE="password" NAME="newMasterPwd" SIZE="20" MAXLENGTH="255"></TD>
                </TR>
                <TR>
                    <TD CLASS="descCampo">Clave Maestra (Repetir)</TD>
                    <TD><INPUT TYPE="password" NAME="newMasterPwdR" SIZE="20" MAXLENGTH="255"></TD>
                </TR>
                <TR>
                    <TD CLASS="descCampo">Confirmar Cambio de Clave</TD>
                    <TD>
                        <INPUT TYPE="checkbox" NAME="confirmPassChange" value="1" />
                        <BR />
                        <IMG SRC="imgs/warning.png" ALT="Atención" CLASS="iconMini" />Se volverán a encriptar las claves de todas las cuentas.
                        <BR />
                        <IMG SRC="imgs/warning.png" ALT="Atención" CLASS="iconMini" />Los usuarios deberán de introducir la nueva clave maestra.
                    </TD>
                </TR>
                <INPUT TYPE="hidden" NAME="action" VALUE="crypt" />
                </FORM>            
            </TABLE>        
            <DIV ID="resAccion"></DIV>
<?php Common::PrintFooter(); ?>
        </DIV>