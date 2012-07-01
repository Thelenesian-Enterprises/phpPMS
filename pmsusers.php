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

    $objCommon = new Common;
    
    foreach ($_POST as $varPost => $varPostValue){
        if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
            $objCommon->arrBackLinks["$varPost"] = $varPostValue;
        }
    }


    Common::printHeader(FALSE,TRUE);

    echo '<BODY OnLoad="loadUsrMgmt(1);">';
    
    Common::printBodyHeader();
    Users::checkUserAccess("users") || die ('<DIV CLASS="error"'.$LANG['msg'][34].'</DIV');
    
    echo '<DIV ID="container" ALIGN="center">';
    echo '<H2 ID="usrmgmt_head">'.$LANG['buttons'][13].'</H2>';
    echo '<DIV ID="actionbar" CLASS="action midround">';
    echo '<IMG ID="btnAddUsr" SRC="imgs/add.png" CLASS="inputImg" TITLE="'.$LANG['buttons'][39].'" OnClick="loadUsrMgmt(2);" />';
    echo '<IMG ID="btnAddGrp" SRC="imgs/add.png" CLASS="inputImg" TITLE="'.$LANG['buttons'][37].'" STYLE="display: none" OnClick="loadUsrMgmt(4);" />';
    echo '<IMG ID="btnGroups" SRC="imgs/groups.png" CLASS="inputImg" TITLE="'.$LANG['buttons'][14].'" OnClick="loadUsrMgmt(3);" />';
    echo '<IMG ID="btnUsers" SRC="imgs/users.png" CLASS="inputImg" TITLE="'.$LANG['buttons'][13].'" STYLE="display: none" OnClick="loadUsrMgmt(1);" />';
    echo '<IMG ID="btnUsrCancel" SRC="imgs/delete.png" CLASS="inputImg" TITLE="'.$LANG['buttons'][6].'" STYLE="display: none" OnClick="loadUsrMgmt(1);" />';
    echo '<IMG ID="btnUsrSave" SRC="imgs/check.png" TITLE="'.$LANG['buttons'][2].'" CLASS="inputImg" STYLE="display: none" OnClick="userMgmt(\'add\',0);" />';
    echo '<IMG ID="btnGrpCancel" SRC="imgs/delete.png" CLASS="inputImg" TITLE="'.$LANG['buttons'][6].'" STYLE="display: none" OnClick="loadUsrMgmt(3);" />';
    echo '<IMG ID="btnGrpSave" SRC="imgs/check.png" TITLE="'.$LANG['buttons'][2].'" CLASS="inputImg" STYLE="display: none" OnClick="groupMgmt(\'add\',0);" />';
                
    $objCommon->printBackLinks(TRUE);
    
    echo '</DIV>';
    echo '<DIV ID="usrMgmt"></DIV>';
    echo '<DIV ID="resAccion"></DIV>';
    
    Common::PrintFooter();
?>