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

    // Variables POST
    $intAccId = $_POST["accountid"];
    
    foreach ($_POST as $varPost => $varPostValue){
        if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
            $objCommon->arrBackLinks["$varPost"] = $varPostValue;
        }
    }
    
    // Obtenemos los datos de la cuenta
    $objAccount->getAccount($intAccId);
     
    Common::printHeader(FALSE,TRUE);
?>
    <BODY ONLOAD="document.editaccount.sel_cliente.focus(););">
        <?php 
            Common::printBodyHeader();
            $objAccount->checkAccountAccess("edit") || die ('<DIV CLASS="error">No tiene permisos para modificar esta cuenta</DIV');
        ?>
        <DIV ID="container" ALIGN="center">
            <H2>Editar Cuenta</H2>
            <DIV CLASS="action midround">
<?php           
                if ( $objAccount->checkAccountAccess("chpass") ){ 
                    echo '<FORM ACTION="account_edit_pass.php" METHOD="post" ID="frmEditPass" >';
                    echo '<INPUT TYPE="hidden" NAME="accountid" VALUE="'.$objAccount->intAccId.'">';
                    echo '<INPUT TYPE="hidden" NAME="decode" VALUE="0">';
                    $objCommon->printBackLinks();
                    echo '</FORM>';
                    echo '<IMG SRC="imgs/key.png" title="Editar clave" class="inputImg" OnClick="$(\'#frmEditPass\').submit();"/>';
                }
                echo '<IMG SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" OnClick="saveAccount(\'frmEditAccount\');">';
                $objCommon->printBackLinks(TRUE);
?>                
                
            </DIV>         
            
            <TABLE CLASS="data">
                <FORM ACTION="" METHOD="post" NAME="editaccount" ID="frmEditAccount">
                <TR>
                    <TD CLASS="descCampo">Cliente</TD>
                    <TD>
                        <SELECT NAME="sel_cliente" SIZE="1">
        <?php
                        foreach ( $objAccount->getClientes() as $cliente ){
                            if ( $cliente == $objAccount->strAccCliente ){
                                echo "<OPTION SELECTED>$cliente</OPTION>";
                            } else {
                                echo "<OPTION>$cliente</OPTION>";
                            }
                        }
        ?>
                        </SELECT>
                        <BR /><BR />
                        <INPUT TYPE="text" NAME="cliente_new" SIZE="50" VALUE="<?php echo $objAccount->strAccCliente;?>" onClick="this.value='';">
                        <INPUT TYPE="hidden" NAME="cliente_old" SIZE="50" VALUE="<?php echo $objAccount->strAccCliente;?>" >
                    </TD>
                </TR>
                <TR >
                    <TD WIDTH="25%" CLASS="descCampo">Categoría</TD>
                    <TD>
                        <SELECT NAME="categoryId" SIZE="1">
        <?php
                            foreach ( $objAccount->getCategorias() as $catName => $catId){
                                $catSelected = ( $objAccount->intAccCategoryId == $catId ) ? "SELECTED" : "";
                                echo "<OPTION VALUE='".$catId."' $catSelected>".$catName."</OPTION>";
                            }       

        ?>
                        </SELECT>
                    </TD>
                </TR>
                <TR>
                    <TD CLASS="descCampo">Servicio / Recurso</TD>
                    <TD><INPUT TYPE="text" NAME="name" SIZE="100" VALUE="<?php echo $objAccount->strAccName; ?>"></TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Login</TD>
                    <TD><INPUT TYPE="text" NAME="login" SIZE="100" VALUE="<?php echo $objAccount->strAccLogin; ?>"></TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">URL / IP</TD>
                    <TD><INPUT TYPE="text" NAME="url" SIZE="100" VALUE="<?php echo $objAccount->strAccUrl; ?>"></TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Notas</TD>
                    <TD><TEXTAREA NAME="notice" COLS="97" ROWS="5"><?php echo $objAccount->strAccNotes; ?></TEXTAREA></TD>
                </TR>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Grupos Secundarios</TD>
                    <TD>
                        <SELECT NAME="ugroups[]" MULTIPLE="multiple" SIZE="5" >                        
        <?php
                        $arrAccountGroups = $objAccount->getGroupsAccount();
                        
                        foreach ( $objAccount->getSecGroups() as $groupName => $groupId ){
                            if ( $groupId != $objAccount->intAccUserGroupId ){
                                if ( is_array($arrAccountGroups) ) $uGroupSelected = ( in_array($groupId, $arrAccountGroups)) ? "SELECTED" : "";
                                echo  "<OPTION VALUE='".$groupId."' $uGroupSelected>".$groupName."</OPTION>";
                            }
                        }
        ?>
                        </SELECT>
                    </TD>
                </TR>
                <INPUT TYPE="hidden" NAME="savetyp" VALUE="2" />
                <INPUT TYPE="hidden" NAME="accountid" VALUE="<?php echo $objAccount->intAccId; ?>" />
            </FORM>
                <TR>
                    <TD WIDTH="25%" CLASS="descCampo">Archivos</TD>
                    <TD>
                        <DIV id="downFiles"></DIV>
                        <SCRIPT>$("#downFiles").load(pms_root + "/ajax_files.php?id=<?echo $intAccId?>&del=1");</SCRIPT>
                        <DIV ID="upldFiles">
                            <DIV CLASS="actionFiles">
                                <IMG ID="btnUpload" SRC="imgs/upload.png" TITLE="Subir archivo (max. 1MB)" CLASS="inputImg" OnClick="upldFile(<? echo $intAccId; ?>)" />
                            </DIV>                            
                            <FORM METHOD="POST" ENCTYPE="multipart/form-data" ACTION="ajax_files.php" NAME="upload_form" ID="upload_form">
                                <INPUT TYPE="file" NAME="file" CLASS="txtFile" />
                                <INPUT TYPE="hidden" NAME="accountId"  ID="account" VALUE="<?echo $intAccId;?>" />
                                <INPUT TYPE="hidden" NAME="action" ID="action" VALUE="upload" />
                            </FORM>
                        </DIV>
                    </TD>
                </TR>
            </TABLE>
            <DIV ID="resAccion"></DIV>
<?php Common::PrintFooter(); ?>