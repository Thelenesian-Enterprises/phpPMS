<?
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
    include_once (PMS_ROOT . "/inc/includes.php");
    check_session(FALSE,TRUE);
    
    Common::printHeader(TRUE,TRUE);
?>
    <body onload="document.frmLogin.user.focus(); checkLogout();">
        <DIV ALIGN="center" id="container">
            <div id='boxLogin'>
                <div id="logo" class="round"><img src="imgs/logo.png" /><?php echo "Acceso ".$CFG_PMS["siteshortname"]; ?></div>
                <div id='login' class="round">
                    <form method='POST' name="frmLogin" id='frmLogin' OnSubmit="return doLogin();">
                        <label for="user">Usuario</label><input type='text' name='user' id='user' /><br />
                        <label for="pass">Clave</label><input type='password' name='pass' id='pass' /><br />
                        <span id="smpass" style="display: none;"><label for="mpass">Clave Maestra</label><input type="password" name="mpass" id="mpass" /><br /></span>
                        <input id="btnLogin" type='image' src="imgs/login.png" name='login' title='Acceder' />
                    </form>
                </div>
                <div id="loading"></div>
            </div>
<?php Common::PrintFooter(); ?>
        </DIV>