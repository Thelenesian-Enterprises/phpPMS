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

    $objConfig = new Config;
    $objAccount = new Account;
    $objCommon = new Common;
    
    foreach ($_POST as $varPost => $varPostValue){
        if (array_key_exists($varPost, $objCommon->arrBackLinks)) {
            $objCommon->arrBackLinks["$varPost"] = $varPostValue;
        }
    }

    Common::printHeader(FALSE,TRUE);

    echo '<BODY>';
    
    Common::printBodyHeader(); 
    Users::checkUserAccess("config") || die ('<DIV CLASS="error">'.$LANG['msg'][34].'</DIV');
    
    echo '<DIV ID="container" ALIGN="center">';
    echo '<H2>'.$LANG['buttons'][11].'</H2>';
    echo '<DIV CLASS="action midround">';
    
    $objCommon->printBackLinks(TRUE);
    
    echo '</DIV>';
    echo '<DIV CLASS="section">'.$LANG['config'][0].'</DIV>';
    echo '<DIV CLASS="action midround">';
    echo '<IMG SRC="imgs/check.png" TITLE="'.$LANG['buttons'][2].'" CLASS="inputImg" OnClick="configMgmt(\'saveconfig\');">';
    echo '</DIV>';
    
    $objConfig->getConfigTable();
    
    echo '<DIV CLASS="section">'.$LANG['config'][24].'</DIV>';
    
    echo '<TABLE CLASS="data tblConfig">';
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][25].'</TD>';
    echo '<TD WIDTH="75%">';       
    echo '<FORM OnSubmit="return configMgmt(\'addcat\');" METHOD="post" NAME="frmAddCategory" ID="frmAddCategory">';
    echo '<INPUT TYPE="text" NAME="categoryName" SIZE="28" MAXLENGTH="255">';
    echo '<INPUT TYPE="hidden" NAME="categoryFunction" VALUE="1">';
    echo '<INPUT TYPE="image" SRC="imgs/add.png" TITLE="'.$LANG['config'][25].'" CLASS="inputImg" ID="btnAdd" />';
    echo '</FORM></TD></TR>';
    
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][26].'</TD>';
    echo '<TD WIDTH="75%">';
    echo '<FORM OnSubmit="return configMgmt(\'editcat\');" METHOD="post" NAME="frmEditCategory" ID="frmEditCategory">';
    echo '<SELECT NAME="categoryId" SIZE="1">';
    
    foreach ( $objAccount->getCategorias() as $catName => $catId){
        echo "<OPTION VALUE='".$catId."'>".$catName."</OPTION>";
    }       
    
    echo '</SELECT><BR /><BR />';
    echo '<INPUT TYPE="text" NAME="categoryNameNew" SIZE="15">';
    echo '<INPUT TYPE="hidden" NAME="categoryFunction" VALUE="2">';
    echo '<INPUT TYPE="image" SRC="imgs/save.png" TITLE="'.$LANG['buttons'][2].'" CLASS="inputImg" ID="btnGuardar" />';
    echo '</FORM></TD></TR>';
    
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][27].'</TD>';
    echo '<TD WIDTH="75%">';
    echo '<FORM OnSubmit="return configMgmt(\'delcat\');" METHOD="post" NAME="frmDelCategory" ID="frmDelCategory">';
    echo '<SELECT NAME="categoryId" SIZE="1">';
    
    foreach ( $objAccount->getCategorias() as $catName => $catId){
        echo "<OPTION VALUE='".$catId."'>".$catName."</OPTION>";
    }
    
    echo '</SELECT>';
    echo '<INPUT TYPE="hidden" NAME="categoryFunction" VALUE="3">';
    echo '<INPUT TYPE="image" SRC="imgs/delete.png" title="'.$LANG['config'][27].'" class="inputImg" />';
    echo '</FORM></TD></TR>';
    echo '</TABLE>';
    
    echo '<DIV CLASS="section">'.$LANG['config'][28].'</DIV>';
    echo '<DIV CLASS="action midround">';
    echo '<IMG SRC="imgs/check.png" TITLE="'.$LANG['buttons'][2].'" CLASS="inputImg" OnClick="configMgmt(\'savempwd\');">';
    echo '</DIV>';
    
    echo '<TABLE CLASS="data tblConfig">';
    echo '<FORM METHOD="post" NAME="frmCrypt" ID="frmCrypt">';
    
    $lastUpdateMPass = $objConfig->getConfigValue("lastupdatempass");
    
    if ( $lastUpdateMPass > 0 ){
        echo '<TR><TD CLASS="descCampo">'.$LANG['config'][43].'</TD><TD>'.date("r",$lastUpdateMPass).'</TD></TR>';
    }
    
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][29].'</TD><TD><INPUT TYPE="password" NAME="curMasterPwd" SIZE="20" MAXLENGTH="255"></TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][30].'</TD><TD><INPUT TYPE="password" NAME="newMasterPwd" SIZE="20" MAXLENGTH="255"></TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][31].'</TD><TD><INPUT TYPE="password" NAME="newMasterPwdR" SIZE="20" MAXLENGTH="255"></TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['config'][32].'</TD><TD>';
    echo '<IMG SRC="imgs/warning.png" ALT="'.$LANG['config'][35].'" CLASS="iconMini" />'.$LANG['config'][42];
    echo '<BR /><IMG SRC="imgs/warning.png" ALT="'.$LANG['config'][35].'" CLASS="iconMini" />'.$LANG['config'][33];
    echo '<BR /><IMG SRC="imgs/warning.png" ALT="'.$LANG['config'][35].'" CLASS="iconMini" />'.$LANG['config'][34];
    echo '<BR /><INPUT TYPE="checkbox" CLASS="checkbox" NAME="confirmPassChange" value="1" />';
    echo '</TD></TR>';
    echo '<INPUT TYPE="hidden" NAME="action" VALUE="crypt" />';
    echo '</FORM></TABLE>';
    echo '<DIV ID="resAccion"></DIV>';
    
    Common::PrintFooter();
?>