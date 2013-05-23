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
 * @link http://www.cygnux.org/phppms
 * 
 */

define('PMS_ROOT', '.');
include_once (PMS_ROOT."/inc/includes.php");
check_session();

$objAccount = new Account;
$objCommon = new Common;
$objConfig = new Config;

// Variables POST
$intAccId = (int)$_POST["accountid"];

foreach ($_POST as $varPost => $varPostValue){
    if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
        $objCommon->arrBackLinks["$varPost"] = $varPostValue;
    }
}

// Obtenemos los datos de la cuenta
$objAccount->getAccount($intAccId);

Common::printHeader(FALSE,TRUE);

echo '<BODY ONLOAD="document.editaccount.name.focus(););">';

Common::printBodyHeader();

$objAccount->checkAccountAccess("edit") || die ('<DIV CLASS="error">'.$LANG['msg'][91].'</DIV');

echo '<DIV ID="container" ALIGN="center">';
echo '<H2>'.$LANG['buttons'][9].'</H2>';

echo '<DIV CLASS="action midround">';

if ( $objAccount->checkAccountAccess("chpass") ){ 
    echo '<FORM ACTION="account_edit_pass.php" METHOD="post" ID="frmEditPass" >';
    echo '<INPUT TYPE="hidden" NAME="accountid" VALUE="'.$objAccount->intAccId.'">';
    echo '<INPUT TYPE="hidden" NAME="decode" VALUE="0">';
    $objCommon->printBackLinks();
    echo '</FORM>';
    echo '<IMG SRC="imgs/key.png" title="'.$LANG['buttons'][10].'" class="inputImg" OnClick="$(\'#frmEditPass\').submit();"/>';
}
echo '<IMG SRC="imgs/check.png" TITLE="'.$LANG['buttons'][2].'" CLASS="inputImg" OnClick="saveAccount(\'frmEditAccount\');">';

$objCommon->printBackLinks(TRUE);

echo '</DIV>';

echo '<TABLE CLASS="data round">';
echo '<FORM ACTION="" METHOD="post" NAME="editaccount" ID="frmEditAccount">';

echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][17].'</TD>';
echo '<TD CLASS="valueField"><INPUT TYPE="text" NAME="name" MAXLENGTH="50" VALUE="'.$objAccount->strAccName.'"></TD></TR>';

echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][0].'</TD>';
echo '<TD CLASS="valueField"><SELECT NAME="sel_cliente" SIZE="1">';

foreach ( $objAccount->getClientes() as $cliente ){
    if ( $cliente == $objAccount->strAccCliente ){
        echo "<OPTION SELECTED>$cliente</OPTION>";
    } else {
        echo "<OPTION>$cliente</OPTION>";
    }
}
echo '</SELECT><BR /><BR />';
echo '<INPUT TYPE="text" NAME="cliente_new" MAXLENGTH="50" VALUE="'.$objAccount->strAccCliente.'" onClick="this.value=\'\';" />';
echo '<INPUT TYPE="hidden" NAME="cliente_old" VALUE="'.$objAccount->strAccCliente.'" />';
echo '</TD></TR>';

echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][16].'</TD>';
echo '<TD CLASS="valueField"><SELECT NAME="categoryId" SIZE="1">';
echo '<OPTION></OPTION>';

foreach ( $objAccount->getCategorias() as $catName => $catId){
    $catSelected = ( $objAccount->intAccCategoryId == $catId ) ? "SELECTED" : "";
    echo "<OPTION VALUE='".$catId."' $catSelected>".$catName."</OPTION>";
}       

echo '</SELECT></TD></TR>';

echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][19].'</TD>';
echo '<TD CLASS="valueField"><INPUT TYPE="text" NAME="login" MAXLENGTH="50" VALUE="'.$objAccount->strAccLogin.'"></TD></TR>';

echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][18].'</TD>';
echo '<TD CLASS="valueField"><INPUT TYPE="text" NAME="url" MAXLENGTH="255" VALUE="'.$objAccount->strAccUrl.'"></TD></TR>';

echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][24].'</TD>';
echo '<TD CLASS="valueField"><TEXTAREA NAME="notice" COLS="97" ROWS="5" MAXLENGTH="1000">'.$objAccount->strAccNotes.'</TEXTAREA></TD></TR>';

echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][30].'</TD>';
echo '<TD CLASS="valueField"><SELECT NAME="ugroups[]" MULTIPLE="multiple" SIZE="5" >';

$arrAccountGroups = $objAccount->getGroupsAccount();

foreach ( $objAccount->getSecGroups() as $groupName => $groupId ){
    if ( $groupId != $objAccount->intAccUserGroupId ){
        if ( is_array($arrAccountGroups) ) $uGroupSelected = ( in_array($groupId, $arrAccountGroups)) ? "SELECTED" : "";
        echo  "<OPTION VALUE='".$groupId."' $uGroupSelected>".$groupName."</OPTION>";
    }
}
echo '</SELECT></TD></TR>';

echo '<INPUT TYPE="hidden" NAME="savetyp" VALUE="2" />';
echo '<INPUT TYPE="hidden" NAME="accountid" VALUE="'.$objAccount->intAccId.'" />';
echo '</FORM>';

if ( $objConfig->getConfigValue("filesenabled") == 1 ){
    echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][23].'</TD>';
    echo '<TD CLASS="valueField"><DIV id="downFiles"></DIV>';
    echo '<SCRIPT>$("#downFiles").load(pms_root + "/ajax/ajax_files.php?id='.$intAccId.'&del=1");</SCRIPT>';
    echo '<DIV ID="upldFiles">';
    echo '<DIV CLASS="actionFiles"><IMG ID="btnUpload" SRC="imgs/upload.png" TITLE="'.$LANG['accounts'][32].'" CLASS="inputImg" OnClick="upldFile('.$intAccId.')" /></DIV>';
    echo '<FORM METHOD="POST" ENCTYPE="multipart/form-data" ACTION="ajax/ajax_files.php" NAME="upload_form" ID="upload_form">';
    echo '<INPUT TYPE="file" NAME="file" CLASS="txtFile" />';
    echo '<INPUT TYPE="hidden" NAME="accountId"  ID="account" VALUE="'.$intAccId.'" />';
    echo '<INPUT TYPE="hidden" NAME="action" ID="action" VALUE="upload" />';
    echo '</FORM>';
}

echo '</DIV></TD></TR></TABLE>';

echo '<DIV ID="resAccion"></DIV>';

Common::PrintFooter();
?>