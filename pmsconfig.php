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
            <DIV CLASS="section">Opciones de Generales</DIV>
            <DIV CLASS="action midround">
                <IMG SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" OnClick="configMgmt('saveconfig');">
            </DIV>        
            <? $objConfig->getConfigTable() ?>
            <DIV CLASS="section"">Categorías</DIV>
                <TABLE CLASS="data tblConfig">
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
                <DIV CLASS="section">Opciones de Clave Maestra</DIV>
                <DIV CLASS="action midround">
                    <IMG SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" OnClick="configMgmt('savempwd');">
                </DIV>
                <TABLE CLASS="data tblConfig">
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