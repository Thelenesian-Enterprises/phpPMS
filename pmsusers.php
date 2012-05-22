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

    $objCommon = new Common;
    
    foreach ($_POST as $varPost => $varPostValue){
        if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
            $objCommon->arrBackLinks["$varPost"] = $varPostValue;
        }
    }


    Common::printHeader(FALSE,TRUE);
?>
    <BODY OnLoad="loadUsrMgmt(1);">
        <?php 
            Common::printBodyHeader();
            Users::checkUserAccess("users") || die ('<DIV CLASS="error">No tiene permisos para acceder a esta página.</DIV>');
        ?>
        <DIV ID="container" ALIGN="center">
            <H2 ID="usrmgmt_head">Gestión de Usuarios</H2>
            <DIV ID="actionbar" CLASS="action midround">
                <IMG ID="btnAddUsr" SRC="imgs/add.png" CLASS="inputImg" TITLE="Nuevo Usuario" OnClick="loadUsrMgmt(2);" />
                <IMG ID="btnAddGrp" SRC="imgs/add.png" CLASS="inputImg" TITLE="Nuevo Grupo" STYLE="display: none" OnClick="loadUsrMgmt(4);" />
                <IMG ID="btnGroups" SRC="imgs/groups.png" CLASS="inputImg" TITLE="Gestión de Grupos" OnClick="loadUsrMgmt(3);" />
                <IMG ID="btnUsers" SRC="imgs/users.png" CLASS="inputImg" TITLE="Gestión de Usuarios" STYLE="display: none" OnClick="loadUsrMgmt(1);" />
                <IMG ID="btnUsrCancel" SRC="imgs/delete.png" CLASS="inputImg" TITLE="Cancelar" STYLE="display: none" OnClick="loadUsrMgmt(1);" />
                <IMG ID="btnUsrSave" SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" STYLE="display: none" OnClick="userMgmt('add',0);" />
                <IMG ID="btnGrpCancel" SRC="imgs/delete.png" CLASS="inputImg" TITLE="Cancelar" STYLE="display: none" OnClick="loadUsrMgmt(3);" />
                <IMG ID="btnGrpSave" SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" STYLE="display: none" OnClick="groupMgmt('add',0);" />
                
                <?php $objCommon->printBackLinks(TRUE); ?>
            </DIV>
            <DIV ID="usrMgmt"></DIV>
            <DIV ID="resAccion"></DIV>
<?php Common::PrintFooter(); ?>
        </DIV>