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

    echo '<BODY>';
    
    Common::printBodyHeader();
    
    $objAccount->checkAccountAccess("view") || die ('<DIV CLASS="error"'.$LANG['msg'][91].'</DIV');
        
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
    
    echo '<TABLE class="data">';
    echo '<TR><TD WIDTH=25% CLASS="descCampo">'.$LANG['accounts'][17].'</TD><TD>'.$objAccount->strAccName.'</TD></TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][0].'</TD><TD>'.$objAccount->strAccCliente.'</TD></TR>';
    echo '<TR><TD WIDTH=25% CLASS="descCampo">'.$LANG['accounts'][16].'</TD><TD>'.$objAccount->strAccCategoryName.'</TD></TR>';
    echo '<TR><TD WIDTH=25% CLASS="descCampo">'.$LANG['accounts'][19].'</TD><TD>'.$objAccount->strAccLogin.'</TD></TR>';
    echo '<TR><TD WIDTH=25% CLASS="descCampo">'.$LANG['accounts'][18].'</TD><TD><A HREF="'. $objAccount->strAccUrl.'" TARGET="_blank">'.$objAccount->strAccUrl.'</A></TD></TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][20].'</TD><TD ID="clave" CLASS="altTextRed">'.$LANG['accounts'][21].'</TD></TR>';
    echo '<TR><TD WIDTH=25% CLASS="descCampo">'.$LANG['accounts'][24].'</TD><TD><TEXTAREA NAME="notice" COLS="97" ROWS="5" READONLY="readonly">'.$objAccount->strAccNotes.'</TEXTAREA></TD></TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][23].'</TD>';
    echo '<TD><DIV ID="downFiles"></DIV></TD>';
    echo '<SCRIPT> $("#downFiles").load(pms_root + "/ajax_files.php?id='.$intAccountId.'&del=0");</SCRIPT></TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][25].'</TD><TD>'.$objAccount->intAccNView.'('.$objAccount->intAccNViewDecrypt.')</TD> </TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][26].'</TD><TD>'.$objAccount->strAccDatAdded.'</TD></TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][27].'</TD><TD>'.$objAccount->strAccDatChanged.'</TD></TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][28].'</TD><TD>'.$objAccount->strAccUserName.'</TD></TR>';
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][29].'</TD><TD>'.$objAccount->strAccUserGroupName.'</TD></TR>';
    
    if ( $intUGroupId == $objAccount->intAccUserGroupId OR $blnUIsAdmin == 1 ){
        echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][30].'</TD>';

        $arrAccountGroups = $objAccount->getGroupsAccount($intAccountId);

        if ( is_array($arrAccountGroups) ){
            foreach ( $objAccount->getSecGroups() as $groupName => $groupId ){
                if ( $groupId != $objAccount->intAccUserGroupId ){
                    if ( in_array($groupId, $arrAccountGroups)){
                        $accUGroups[] = $groupName;
                    }
                }
            }
            echo '<TD>'.implode(" | ",$accUGroups).'</TD></TR>';
        } else {
            echo '<TD>&nbsp;</TD></TR>';
        }
    }
    
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['accounts'][31].'</TD><TD>'.$objAccount->strAccUserEditName.'</TD></TR>';
    echo '</TABLE>';
    
    echo '<DIV ID="resAccion"></DIV>';
    
    Common::PrintFooter();
?>