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

    echo '<BODY ONLOAD="document.addaccount.sel_cliente.focus()">';

    Common::printBodyHeader();

    echo '<DIV ID="container" ALIGN="center">';
    echo '<H2>'.$LANG['buttons'][7].'</H2>';
    echo '<DIV CLASS="action midround">';
    echo '<IMG SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" OnClick="saveAccount(\'frmAddAccount\');" />';

    $objCommon->printBackLinks(TRUE);

    echo '</DIV>';
    echo '<TABLE CLASS="data">';
    echo '<FORM ACTION="" METHOD="post" NAME="addaccount" ID="frmAddAccount">';

    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][17].'</TD>';
    echo '<TD WIDTH="75%"><INPUT NAME="name" TYPE="text" SIZE="30" MAXLENGTH="255"></TD></TR>';

    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][0].'</TD>';
    echo '<TD><SELECT NAME="sel_cliente" SIZE="1">';

    foreach ( $objAccount->getClientes() as $cliente ){
        if ( $cliente == $objAccount->strCliente ){
            echo "<OPTION SELECTED>$cliente</OPTION>";
        } else {
            echo "<OPTION>$cliente</OPTION>";
        }
    }

    echo '</SELECT><BR /><BR />';
    echo '<INPUT TYPE="text" NAME="cliente_new" SIZE="50" VALUE="'.$LANG['accounts'][15].'" onClick="this.value=\'\';"></TD></TR>';
    
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][16].'</TD>';
    echo '<TD WIDTH="75%">';
    echo '<SELECT NAME="categoryId" SIZE="1">';

    foreach ( $objAccount->getCategorias() as $catName => $catId){
        echo "<OPTION VALUE='".$catId."'>".$catName."</OPTION>";
    }
    
    echo '</SELECT></TD></TR>';
    
    echo '<TR><TD WIDTH=25% CLASS="descCampo">'.$LANG['accounts'][18].'</TD>';
    echo '<TD WIDTH=75%><INPUT NAME="url" TYPE="text" SIZE="30" MAXLENGTH="255"></TD></TR>';

    echo '<TR><TD WIDTH=25% CLASS="descCampo">'.$LANG['accounts'][19].'</TD>';
    echo '<TD WIDTH=75%><INPUT NAME="login" TYPE="text" SIZE="30" MAXLENGTH="255"></TD></TR>';

    echo '<TR><TD WIDTH=25% CLASS="descCampo">'.$LANG['accounts'][20].'</TD>';
    echo '<TD WIDTH=75%><INPUT NAME="password" TYPE="password" SIZE="30" MAXLENGTH="255"></TD></TR>';

    echo '<TR><TD WIDTH=25% CLASS="descCampo">'.$LANG['accounts'][22].'</TD>';
    echo '<TD WIDTH=75%><INPUT NAME="password2" TYPE="password" SIZE="30" MAXLENGTH="255"></TD></TR>';

    echo '<TR><TD WIDTH=25% CLASS="descCampo">'.$LANG['accounts'][24].'</TD>';
    echo '<TD WIDTH=75%><TEXTAREA NAME="notice" TYPE="text" COLS="30" ROWS="5" MAXLENGTH="255"></TEXTAREA></TD></TR>';

    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][30].'</TD>';
    echo '<TD><SELECT NAME="ugroups[]" MULTIPLE="multiple" SIZE="5" >';
    
    $arrGroups = $objAccount->getSecGroups();

    foreach ( $arrGroups as $groupName => $groupId ){
        if ( $groupId != $_SESSION["ugroup"] ){
            echo "<OPTION VALUE='$groupId'>$groupName</OPTION>";
        }
    }

    echo '</SELECT></TD></TR>';

    echo '<INPUT TYPE="hidden" NAME="savetyp" VALUE="1">';
    echo '</FORM></TABLE>';
    echo '<DIV ID="resAccion"></DIV>';
    
    Common::PrintFooter();
?>