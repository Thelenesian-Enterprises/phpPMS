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

    $objAccount = new Account;
    $objCommon = new Common;

    $intAccId = $_POST["accountid"];
    $intDecode = $_POST["decode"];
    $strMasterpass = $_POST["masterpass"];

    foreach ($_POST as $varPost => $varPostValue){
        if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
            $objCommon->arrBackLinks["$varPost"] = $varPostValue;
        }
    }    

    $objAccount->getAccount($intAccId);
        
    Common::printHeader(FALSE,TRUE);
?>
    <BODY ONLOAD="document.editpass.password.focus()">
    <?php 
        $objCommon->printBodyHeader();
        if ( ! $objAccount->checkAccountAccess("chpass") ) die ("No tiene permisos para modificar esta cuenta.");      
    ?>
        <DIV ID="container" ALIGN="center">
            <H2>Modificar Clave de Cuenta</H2>
            <DIV CLASS="action midround">
                <IMG SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" ID="btnGuardar" OnClick="saveAccount('frmEditPass');">
                <?php $objCommon->printBackLinks(TRUE); ?>
            </DIV> 
            <FORM ACTION="" METHOD="post" NAME="editpass" ID="frmEditPass" >
            <TABLE CLASS="data">
                <TR>
                    <TD CLASS="descCampo">Nombre</TD>
                    <TD><?php echo $objAccount->strAccName; ?></TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Categoría</TD>
                    <TD><?php echo $objAccount->strAccCategoryName; ?></TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Login</TD>
                    <TD><?php echo $objAccount->strAccLogin; ?></TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">URL:</TD>
                    <TD><?php echo '<A HREF="'.$objAccount->strAccUrl.'" TARGET="_blank">'.$objAccount->strAccUrl; ?></TD>
                </TR>
                <TR>
                    <TD width=25% CLASS="descCampo">Clave</TD>
                    <TD><INPUT TYPE="password" SIZE="100" NAME="password"></TD>
                </TR>
                <TR>
                    <TD width="25%" CLASS="descCampo">Clave (repetir)</TD>
                    <TD><INPUT TYPE="password" SIZE="100" NAME="password2"></TD>
                </TR>
            </TABLE>
            <INPUT TYPE="hidden" NAME="savetyp" VALUE="4">
            <INPUT TYPE="hidden" NAME="accountid" VALUE="<?php echo ($objAccount->intAccId) ?>">
            </FORM>
            <DIV ID="resAccion"></DIV>
<?php Common::PrintFooter(); ?>