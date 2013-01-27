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
include_once (PMS_ROOT . "/inc/includes.php");
check_session(FALSE,TRUE);

Common::printHeader(TRUE,TRUE);

echo '<body onload="document.frmLogin.user.focus(); checkLogout();">';
echo '<noscript><DIV ID="nojs">'.$LANG["common"][2].'</DIV></noscript>';
echo '<DIV ALIGN="center" id="container">';
echo '<div id="boxLogin">';
//echo '<div id="logo" class="round"><img src="imgs/logo.png" />'.$LANG["login"][0].' '.$CFG_PMS["siteshortname"].'</div>';
echo '<img id="imgLogo" src="imgs/logo.png" />';
echo '<div id="login">';
echo '<form method="POST" name="frmLogin" id="frmLogin" OnSubmit="return doLogin();">';
echo '<label for="user">'.$LANG["login"][1].'</label><input type="text" name="user" id="user" /><br />';
echo '<label for="pass">'.$LANG["login"][2].'</label><input type="password" name="pass" id="pass" /><br />';
echo '<span id="smpass" style="display: none;"><label for="mpass">'.$LANG["login"][3].'</label><input type="password" name="mpass" id="mpass" /><br /></span>';
echo '<input id="btnLogin" type="image" src="imgs/login.png" name="login" title="'.$LANG['buttons'][40].'" />';
echo '</form>';
echo '</div>';
echo '<div id="loading"></div>';
echo '</div>';

Common::PrintFooter();
?>