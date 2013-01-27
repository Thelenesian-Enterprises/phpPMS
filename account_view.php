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

$intAccountId = $_POST["accountid"];
$intDelete = ( isset($_POST["delete"]) ) ? $_POST["delete"] : "";
$blnUIsAdminApp = $_SESSION["uisadminapp"];
$blnUIsAdminAcc = $_SESSION["uisadminacc"];

foreach ($_POST as $varPost => $varPostValue){
    if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
        $objCommon->arrBackLinks["$varPost"] = $varPostValue;
    }
}

$intUGroupId = $_SESSION["ugroup"];

$objAccount->incrementViewCounter($intAccountId);
$objAccount->getAccount($intAccountId);

Common::printHeader(FALSE,TRUE);

echo '<BODY>';

Common::printBodyHeader();

$objAccount->checkAccountAccess("view") || die ('<DIV CLASS="error">'.$LANG['msg'][91].'</DIV');

echo '<DIV ID="container" ALIGN="center">';
echo ( $intDelete == 1 ) ? '<H2>'.$LANG['buttons'][42].'</H2>' : '<H2>'.$LANG['buttons'][41].'</H2>';

echo '<DIV CLASS="action midround">';

if ( $intDelete == 0 ) { 
    if ( $objAccount->checkAccountAccess("edit") ){
        echo '<FORM ACTION="account_edit.php" METHOD="post" ID="frmEditAccount" >';
        echo '<INPUT TYPE="hidden" NAME="accountid" VALUE="'.$objAccount->intAccId.'">';
        $objCommon->printBackLinks();
        echo '</FORM>';
        echo '<IMG SRC="imgs/edit.png" title="'.$LANG['buttons'][9].'" class="inputImg" OnClick="$(\'#frmEditAccount\').submit();"/>';
    }
    if ( $objAccount->checkAccountAccess("viewpass") ){
        echo '<IMG SRC="imgs/key.png" TITLE="'.$LANG['buttons'][4].'" onClick="verClave('.$objAccount->intAccId.',0);" CLASS="inputImg" ALT ="'.$LANG['buttons'][4].'"/>';
    }                 
} else {
    if ( $objAccount->checkAccountAccess("del") ){
        echo '<IMG SRC="imgs/delete.png" title="'.$LANG['buttons'][42].'" class="inputImg" OnClick="delAccount('.$objAccount->intAccId.',3);" />';
    }
}

$objCommon->printBackLinks(TRUE);

echo '</DIV>';

echo '<TABLE class="data round">';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][17].'</TD><TD CLASS="valueField">'.$objAccount->strAccName.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][0].'</TD><TD CLASS="valueField">'.$objAccount->strAccCliente.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][16].'</TD><TD CLASS="valueField">'.$objAccount->strAccCategoryName.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][19].'</TD><TD CLASS="valueField">'.$objAccount->strAccLogin.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][18].'</TD><TD CLASS="valueField"><A HREF="'. $objAccount->strAccUrl.'" TARGET="_blank">'.$objAccount->strAccUrl.'</A></TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][20].'</TD><TD ID="clave" CLASS="valueField altTextRed">'.$LANG['accounts'][21].'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][24].'</TD><TD CLASS="valueField"><TEXTAREA NAME="notice" COLS="97" ROWS="5" READONLY="readonly">'.$objAccount->strAccNotes.'</TEXTAREA></TD></TR>';

if ( $objConfig->getConfigValue("filesenabled") == 1 ){
    echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][23].'</TD>';
    echo '<TD><DIV ID="downFiles"></DIV></TD>';
    echo '<SCRIPT> $("#downFiles").load(pms_root + "/ajax/ajax_files.php?id='.$intAccountId.'&del=0");</SCRIPT></TR>';
}

echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][25].'</TD><TD CLASS="valueField">'.$objAccount->intAccNView.'('.$objAccount->intAccNViewDecrypt.')</TD> </TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][26].'</TD><TD CLASS="valueField">'.$objAccount->strAccDatAdded.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][27].'</TD><TD CLASS="valueField">'.$objAccount->strAccDatChanged.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][28].'</TD><TD CLASS="valueField">'.$objAccount->strAccUserName.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][29].'</TD><TD CLASS="valueField">'.$objAccount->strAccUserGroupName.'</TD></TR>';

if ( $intUGroupId == $objAccount->intAccUserGroupId || $blnUIsAdminApp || $blnUIsAdminAcc ){
    echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][30].'</TD>';

    $arrAccountGroups = $objAccount->getGroupsAccount($intAccountId);

    if ( is_array($arrAccountGroups) ){
        foreach ( $objAccount->getSecGroups() as $groupName => $groupId ){
            if ( $groupId != $objAccount->intAccUserGroupId ){
                if ( in_array($groupId, $arrAccountGroups)){
                    $accUGroups[] = $groupName;
                }
            }
        }
        echo '<TD CLASS="valueField">'.implode(" | ",$accUGroups).'</TD></TR>';
    } else {
        echo '<TD CLASS="valueField">&nbsp;</TD></TR>';
    }
}

echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][31].'</TD><TD CLASS="valueField">'.$objAccount->strAccUserEditName.'</TD></TR>';
echo '</TABLE>';

echo '<DIV ID="resAccion"></DIV>';

Common::PrintFooter();
?>