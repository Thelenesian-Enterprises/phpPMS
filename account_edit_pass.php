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
$objUser = new Users;    

$intAccId = (int)$_POST["accountid"];

foreach ($_POST as $varPost => $varPostValue){
    if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
        $objCommon->arrBackLinks["$varPost"] = $varPostValue;
    }
}    

$objAccount->getAccount($intAccId);

Common::printHeader(FALSE,TRUE);

echo '<BODY ONLOAD="document.editpass.password.focus()">';

$objCommon->printBodyHeader();

$objAccount->checkAccountAccess("chpass") || die ('<DIV CLASS="error"'.$LANG['msg'][91].'</DIV');
$objUser->checkUserUpdateMPass() || die ('<DIV CLASS="error">'.$LANG['msg'][100].'</DIV');

echo '<DIV ID="container" ALIGN="center">';
echo '<H2>'.$LANG['buttons'][10].'</H2>';
echo '<DIV CLASS="action midround">';
echo '<IMG SRC="imgs/check.png" TITLE="'.$LANG['buttons'][2].'" CLASS="inputImg" ID="btnGuardar" OnClick="saveAccount(\'frmEditPass\');">';

$objCommon->printBackLinks(TRUE);

echo '</DIV>';

echo '<FORM ACTION="" METHOD="post" NAME="editpass" ID="frmEditPass" >';
echo '<TABLE CLASS="data round">';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][17].'</TD><TD CLASS="valueField">'.$objAccount->strAccName.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][16].'</TD><TD CLASS="valueField">'.$objAccount->strAccCategoryName.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][19].'</TD><TD CLASS="valueField">'.$objAccount->strAccLogin.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][18].'</TD><TD CLASS="valueField"><A HREF="'.$objAccount->strAccUrl.'" TARGET="_blank">'.$objAccount->strAccUrl.'</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][20].'</TD><TD CLASS="valueField"><INPUT TYPE="password" MAXLENGTH="255" NAME="password" onKeyUp="checkPassLevel(this.value)">';
echo '<IMG SRC="imgs/genpass.png" TITLE="'.$LANG['buttons'][50].'" CLASS="inputImg" OnClick="password(11,true,true);" />';
echo '</TD></TR>';
echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][22].'</TD><TD CLASS="valueField"><INPUT TYPE="password" MAXLENGTH="255" NAME="password2">';
echo '<SPAN ID="passLevel" TITLE="'.$LANG['buttons'][51].'" ></SPAN>';
echo '</TD></TR>';
echo '</TABLE>';
echo '<INPUT TYPE="hidden" NAME="savetyp" VALUE="4" />';
echo '<INPUT TYPE="hidden" NAME="accountid" VALUE="'.$objAccount->intAccId.'" />';
echo '</FORM>';
echo '<DIV ID="resAccion"></DIV>';

Common::PrintFooter();
?>