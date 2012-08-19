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
    if ( check_session(TRUE) ) return "0";
    
    $strError = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">No tiene permisos para realizar esta operación</span></div>';
    
    Users::checkUserAccess("chpass",$_GET["usrid"]) || die ($strError);
    
    echo '<DIV ID="fancycontainer" ALIGN="center">';
    echo '<H2>'.$LANG['buttons'][32].'</H2>';
    echo '<FORM METHOD="post" NAME="updUsrPass" ID="frmUpdUsrPass">';
    echo '<TABLE CLASS="fancydata">';
    echo '<TR><TD CLASS="descCampo">'.$LANG['users'][0].'</TD><TD><INPUT TYPE="text" ID="usrlogin" NAME="usrlogin" TITLE="'.$LANG['users'][0].'" CLASS="txtpass" VALUE="'.$_GET["usrlogin"].'" readonly /></TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['users'][9].'</TD><TD><INPUT TYPE="password" ID="usrpass" NAME="usrpass" TITLE="'.$LANG['users'][9].'" CLASS="txtpass" OnFocus="$(\'#passLevel\').show(); $(\'#resFancyAccion\').hide();" OnKeyUp="checkPassLevel(this.value)" />';
    echo '<IMG SRC="imgs/genpass.png" TITLE="'.$LANG['buttons'][50].'" CLASS="inputImg" OnClick="$(\'#resFancyAccion\').hide(); password(11,true);" />';
    echo '</TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['users'][10].'</TD><TD><INPUT TYPE="password" ID="usrpassv" NAME="usrpassv" TITLE="'.$LANG['users'][10].'" CLASS="txtpassv" /></TD></TR>';
    echo '<INPUT TYPE="hidden" NAME="usrid" VALUE="'.$_GET["usrid"].'" />';
    echo '</TABLE></FORM>';
    
    echo '<DIV ID="resCheck">';
    echo '<SPAN ID="resFancyAccion"></SPAN>';
    echo '<SPAN ID="passLevel" TITLE="'.$LANG['buttons'][51].'" ></SPAN>';
    echo '</DIV>';
    echo '<DIV ID="actionbar" CLASS="action round">';
    echo '<IMG SRC="imgs/check.png" TITLE="'.$LANG['buttons'][2].'" CLASS="inputImg" OnClick="userMgmt(\'pass\','.$_GET["usrid"].')" />';
    echo '</DIV></DIV>';
?>