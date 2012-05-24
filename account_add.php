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
    include_once (PMS_ROOT . "/inc/includes.php");
    check_session();
    
    $objAccount = new Account;
    $objCommon = new Common;

    foreach ($_POST as $varPost => $varPostValue){
        if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
            $objCommon->arrBackLinks["$varPost"] = $varPostValue;
        }
    }
    
    Common::printHeader(FALSE,TRUE);
?>
    <BODY ONLOAD="document.addaccount.sel_cliente.focus()">

        <?php Common::printBodyHeader(); ?>

        <DIV ID="container" ALIGN="center">
            <H2>Nueva Cuenta</H2>
            <DIV CLASS="action midround">
                <IMG SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" OnClick="saveAccount('frmAddAccount');" />
                <?php $objCommon->printBackLinks(TRUE); ?>
            </DIV>  
            <TABLE CLASS="data">
                <FORM ACTION="" METHOD="post" NAME="addaccount" ID="frmAddAccount">
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Cliente</TD>
                    <TD>
                        <SELECT NAME="sel_cliente" SIZE="1">
<?php
                        foreach ( $objAccount->getClientes() as $cliente ){
                            if ( $cliente == $objAccount->strCliente ){
                                echo "<OPTION SELECTED>$cliente</OPTION>";
                            } else {
                                echo "<OPTION>$cliente</OPTION>";
                            }
                        }
?>
                        </SELECT>		
                        <BR /><BR />
                        <INPUT TYPE="text" NAME="cliente_new" SIZE="50" VALUE="Buscar en desplegable o introducir aquí" onClick="this.value='';">
                    </TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Categoría</TD>
                    <TD WIDTH="75%">
                        <SELECT NAME="categoryId" SIZE="1">
<?php
                        foreach ( $objAccount->getCategorias() as $catName => $catId){
                            echo "<OPTION VALUE='".$catId."'>".$catName."</OPTION>";
                        }       
?>
                        </SELECT>
                    </TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Servicio / Recurso</TD>
                    <TD WIDTH="75%"><INPUT NAME="name" TYPE="text" SIZE="30" MAXLENGTH="255"></TD>
                </TR>
                <TR>
                    <TD WIDTH=25% CLASS="descCampo">URL / IP</TD>
                    <TD WIDTH=75%><INPUT NAME="url" TYPE="text" SIZE="30" MAXLENGTH="255"></TD>
                </TR>
                <TR>
                    <TD WIDTH=25% CLASS="descCampo">Login</TD>
                    <TD WIDTH=75%><INPUT NAME="login" TYPE="text" SIZE="30" MAXLENGTH="255"></TD>
                </TR>
                <TR>
                    <TD WIDTH=25% CLASS="descCampo">Clave</TD>
                    <TD WIDTH=75%><INPUT NAME="password" TYPE="password" SIZE="30" MAXLENGTH="255"></TD>
                </TR>
                <TR>
                    <TD WIDTH=25% CLASS="descCampo">Clave (repetir)</TD>
                    <TD WIDTH=75%><INPUT NAME="password2" TYPE="password" SIZE="30" MAXLENGTH="255"></TD>
                </TR>
                <TR>
                    <TD WIDTH=25% CLASS="descCampo">Notas</TD>
                    <TD WIDTH=75%><TEXTAREA NAME="notice" TYPE="text" COLS="30" ROWS="5" MAXLENGTH="255"></TEXTAREA></TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Grupos Secundarios</TD>
                    <TD>
                       <SELECT NAME="ugroups[]" MULTIPLE="multiple" SIZE="5" >
<?php 
                        $arrGroups = $objAccount->getSecGroups();

                        foreach ( $arrGroups as $groupName => $groupId ){
                            if ( $groupId != $_SESSION["ugroup"] ){
                                echo "<OPTION VALUE='$groupId'>$groupName</OPTION>";
                            }
                        }
?>
                        </SELECT>
                    </TD>
                </TR>
                <INPUT TYPE="hidden" NAME="savetyp" VALUE="1">
                </FORM>
            </TABLE>
            <DIV ID="resAccion"></DIV>
<?php Common::PrintFooter(); ?>
        </DIV>