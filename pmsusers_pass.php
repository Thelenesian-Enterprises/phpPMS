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
    check_session(TRUE);
    
    $strError = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">No tiene permisos para realizar esta operación</span></div>';
    
    Users::checkUserAccess("chpass",$_GET["usrid"]) || die ($strError);
    
    echo '<DIV ID="fancycontainer" ALIGN="center">';
    echo '<H2>'.$LANG['buttons'][32].'</H2>';
    echo '<FORM METHOD="post" NAME="updUsrPass" ID="frmUpdUsrPass">';
    echo '<TABLE CLASS="fancydata">';
    echo '<TR><TD CLASS="descCampo">'.$LANG['users'][0].'</TD><TD><INPUT TYPE="text" ID="usrpass" NAME="usrlogin" TITLE="Login" CLASS="txtpass" VALUE="'.$_GET["usrlogin"].'" readonly /></TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['users'][9].'</TD><TD><INPUT TYPE="password" ID="usrpass" NAME="usrpass" TITLE="Clave" CLASS="txtpass" /></TD></TR>';
    echo '<TR><TD CLASS="descCampo">'.$LANG['users'][10].'</TD><TD><INPUT TYPE="password" ID="usrpassv" NAME="usrpassv" TITLE="Clave (Repetir)" CLASS="txtpassv" /></TD></TR>';
    echo '<INPUT TYPE="hidden" NAME="usrid" VALUE="'.$_GET["usrid"].'" />';
    echo '</TABLE></FORM>';
    
    echo '<DIV ID="resFancyAccion"></DIV>';
    echo '<DIV ID="actionbar" CLASS="action round">';
    echo '<IMG SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" OnClick="userMgmt(\'pass\','.$_GET["usrid"].')" />';
    echo '</DIV></DIV>';
?>