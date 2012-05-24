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
    check_session(TRUE);
    
    $strError = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">No tiene permisos para realizar esta operación</span></div>';
    
    Users::checkUserAccess("chpass",$_GET["usrid"]) || die ($strError);
?>
<DIV ID="fancycontainer" ALIGN="center">
    <H2>Cambio de Clave</H2>

    <FORM METHOD="post" NAME="updUsrPass" ID="frmUpdUsrPass">
        <TABLE CLASS="fancydata">
            <TR><TD CLASS="descCampo">Usuario</TD><TD><INPUT TYPE="text" ID="usrpass" NAME="usrlogin" TITLE="Login" CLASS="txtpass" VALUE="<? echo $_GET["usrlogin"]; ?>" readonly/></TD></TR>
            <TR><TD CLASS="descCampo">Clave</TD><TD><INPUT TYPE="password" ID="usrpass" NAME="usrpass" TITLE="Clave" CLASS="txtpass" /></TD></TR>
            <TR><TD CLASS="descCampo">Clave (Repetir)</TD><TD><INPUT TYPE="password" ID="usrpassv" NAME="usrpassv" TITLE="Clave (Repetir)" CLASS="txtpassv" /></TD></TR>
            <INPUT TYPE="hidden" NAME="usrid" VALUE="<? echo $_GET["usrid"]; ?>" />
        </TABLE>
    </FORM>
    <DIV ID="resFancyAccion"></DIV>
    <DIV ID="actionbar" CLASS="action round">
        <IMG SRC="imgs/check.png" TITLE="Guardar" CLASS="inputImg" OnClick="userMgmt('pass',<? echo $_GET["usrid"]; ?>)" />
    </DIV>    
</DIV>
