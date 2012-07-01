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

    echo '<BODY ONLOAD="document.editpass.password.focus()">';

    $objCommon->printBodyHeader();
    
    $objAccount->checkAccountAccess("chpass") || die ('<DIV CLASS="error"'.$LANG['msg'][91].'</DIV');
    
    echo '<DIV ID="container" ALIGN="center">';
    echo '<H2>'.$LANG['buttons'][10].'</H2>';
    echo '<DIV CLASS="action midround">';
    echo '<IMG SRC="imgs/check.png" TITLE="'.$LANG['buttons'][2].'" CLASS="inputImg" ID="btnGuardar" OnClick="saveAccount(\'frmEditPass\');">';
    
    $objCommon->printBackLinks(TRUE);
    
    echo '</DIV>';
    
    echo '<FORM ACTION="" METHOD="post" NAME="editpass" ID="frmEditPass" >';
    echo '<TABLE CLASS="data">';
    echo '<TR><TD CLASS="descCampo">'.$LANG['accounts'][17].'</TD><TD>'.$objAccount->strAccName.'</TD></TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][16].'</TD><TD>'.$objAccount->strAccCategoryName.'</TD></TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][19].'</TD><TD>'.$objAccount->strAccLogin.'</TD></TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][18].'</TD><TD><A HREF="'.$objAccount->strAccUrl.'" TARGET="_blank">'.$objAccount->strAccUrl.'</TD></TR>';
    echo '<TR><TD width=25% CLASS="descCampo">'.$LANG['accounts'][20].'</TD><TD><INPUT TYPE="password" SIZE="100" NAME="password"></TD></TR>';
    echo '<TR><TD width=25% CLASS="descCampo">'.$LANG['accounts'][22].'</TD><TD><INPUT TYPE="password" SIZE="100" NAME="password2"></TD></TR>';
    echo '</TABLE>';
    echo '<INPUT TYPE="hidden" NAME="savetyp" VALUE="4" />';
    echo '<INPUT TYPE="hidden" NAME="accountid" VALUE="'.$objAccount->intAccId.'" />';
    echo '</FORM>';
    echo '<DIV ID="resAccion"></DIV>';
    
    Common::PrintFooter();
?>