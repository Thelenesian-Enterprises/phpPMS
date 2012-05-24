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

    $intAccountId = $_POST["accountid"];
    $intDecode = $_POST["decode"];
    $intDelete = $_POST["delete"];
    $strMasterpass = $_POST["masterpass"];
        
    foreach ($_POST as $varPost => $varPostValue){
        if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
            $objCommon->arrBackLinks["$varPost"] = $varPostValue;
        }
    }
    
    $intUGroupId = $_SESSION["ugroup"];
    $intProfileId = $_SESSION["uprofile"];
    $blnUIsAdmin = $_SESSION["uisadmin"];

    $objAccount->incrementViewCounter($intAccountId);
    $objAccount->getAccount($intAccountId);
   
    Common::printHeader(FALSE,TRUE);
?>
    <BODY>
    <?php 
        Common::printBodyHeader(); 
        if ( ! $objAccount->checkAccountAccess("view") ) die ('<DIV CLASS="error">No tiene permisos para ver esta cuenta</DIV>');
    ?>
        
    <DIV ID="container" ALIGN="center">
        <?php echo ( $intDelete == 1 ) ? "<h2>Borrar Cuenta</h2>" : "<h2>Mostrar Cuenta</h2>"; ?>
        <DIV CLASS="action midround">
<?php 
            if ( $intDelete == 0 ) { 
                if ( $objAccount->checkAccountAccess("edit") ){
                    echo '<FORM ACTION="account_edit.php" METHOD="post" ID="frmEditAccount" >';
                    echo '<INPUT TYPE="hidden" NAME="accountid" VALUE="'.$objAccount->intAccId.'">';
                    $objCommon->printBackLinks();
                    echo '</FORM>';
                    echo '<IMG SRC="imgs/edit.png" title="Editar" class="inputImg" OnClick="$(\'#frmEditAccount\').submit();"/>';
                }
                if ( $objAccount->checkAccountAccess("viewpass") ){
                    echo '<IMG SRC="imgs/key.png" TITLE="Ver clave" onClick="verClave('.$objAccount->intAccId.',0);" CLASS="inputImg" ALT ="Ver Clave"/>';
                }                 
            } else {
                if ( $objAccount->checkAccountAccess("del") ){
                    echo '<IMG SRC="imgs/delete.png" title="Eliminar" class="inputImg" OnClick="delAccount('.$objAccount->intAccId.',3);" />';
                }
            }
            
            $objCommon->printBackLinks(TRUE);
?>
        </DIV>          
        <TABLE class="data">
            <TR>
		<TD CLASS="descCampo">Cliente</TD>
		<TD><?php echo $objAccount->strAccCliente; ?></TD>
            </TR>
            <TR>
                <TD WIDTH="25%" CLASS="descCampo">Categoría</TD>
		<TD><?php echo $objAccount->strAccCategoryName; ?></TD>
            </TR>
            <TR>
		<TD CLASS="descCampo">Servicio / Recurso</TD>
		<TD><?php echo $objAccount->strAccName; ?></TD>
            </TR>
            <TR>
		<TD WIDTH="25%" CLASS="descCampo">Login</TD>
		<TD><?php echo $objAccount->strAccLogin; ?></TD>
            </TR>
            <TR>
                <TD WIDTH="25%" CLASS="descCampo">URL / IP</TD>
                <TD><?php echo "<A HREF=\"". $objAccount->strAccUrl . "\" target=\"_blank\">" . $objAccount->strAccUrl . "</A>"; ?></TD>
            </TR>
            <TR>
		<TD WIDTH="25%" CLASS="descCampo">Clave</TD>
		<TD ID="clave" CLASS="altTextRed">NO VISIBLE</TD>
            </TR>
            <TR>
		<TD WIDTH="25%" CLASS="descCampo">Notas</TD>
		<TD><TEXTAREA NAME="notice" COLS="97" ROWS="5" READONLY="readonly"><?php echo $objAccount->strAccNotes; ?></TEXTAREA></TD>
            </TR>
            <TR>
		<TD WIDTH="25%" CLASS="descCampo">Archivos</TD>
		<TD><DIV ID="downFiles"></DIV></TD>
                <SCRIPT> $("#downFiles").load(pms_root + "/ajax_files.php?id=<? echo $intAccountId ?>&del=0");</SCRIPT>
            </TR>
            <TR>
                <TD WIDTH="25%" CLASS="descCampo">Visitas</TD>
                <TD><?php echo $objAccount->intAccNView."(".$objAccount->intAccNViewDecrypt.")"; ?></TD>
            </TR>
            <TR>
                <TD WIDTH="25%" CLASS="descCampo">Fecha Alta</TD>
                <TD><?php echo $objAccount->strAccDatAdded; ?></TD>
            </TR>
            <TR>
                <TD WIDTH="25%" CLASS="descCampo">Fecha Edición</TD>
                <TD><?php echo $objAccount->strAccDatChanged; ?></TD>
            </TR>
            <TR>
                <TD WIDTH="25%" CLASS="descCampo">Creador</TD>
                <TD><?php echo $objAccount->strAccUserName; ?></TD>
            </TR>
            <TR>
                <TD WIDTH="25%" CLASS="descCampo">Grupo Principal</TD>
                <TD><?php echo $objAccount->strAccUserGroupName; ?></TD>
            </TR>
<?php
            if ( $intUGroupId == $objAccount->intAccUserGroupId OR $blnUIsAdmin == 1 ){
                echo '<TR><TD WIDTH="25%" CLASS="descCampo">Grupos Secundarios</TD>';
                
                $arrAccountGroups = $objAccount->getGroupsAccount($intAccountId);
                
                foreach ( $objAccount->getSecGroups() as $groupName => $groupId ){
                    if ( $groupId != $objAccount->intAccUserGroupId ){
                        if ( in_array($groupId, $arrAccountGroups)){
                            $accUGroups[] = $groupName;
                        }
                    }
                }
                
                echo '<TD>'.implode(" | ",$accUGroups).'</TD></TR>';
            }
?>      
            <TR>
                <TD WIDTH="25%" CLASS="descCampo">Editor</TD>
                <TD><?php echo $objAccount->strAccUserEditName; ?></TD>
            </TR>
        </TABLE>
        <?php if ( $blnNoMd5 == TRUE ) {?>
	<TABLE WIDTH="80%" CLASS="altTable">
            <TR>
                <TD ALIGN="right"><B CLASS="altTxtRed">** no se puede verificar la clave porque el checksum interno no está disponible.<BR />Para escribir el checksum pulsa el botón.</B></TD>
                <TD>
                    <FORM ACTION="pms_account_md5_save.php" METHOD="post">
                        <INPUT TYPE="hidden" NAME="accountid" VALUE="<?php echo $intAccountId; ?>">
                        <INPUT TYPE="hidden" NAME="md5sum" VALUE="<?php echo md5($strDecrypted); ?>">
                        <INPUT TYPE="submit" VALUE="Guardar Checksum" CLASS="altButtonFormat" ACCESSKEY="d">
                    </FORM>
                </TD>
            </TR>
	</TABLE>
        <?php } ?>
        <DIV ID="resAccion"></DIV>
        
<?php Common::PrintFooter(); ?>
    </DIV>