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

$userGroupId = $_SESSION["ugroup"];
$userProfileId = $_SESSION["uprofile"];
$userIsAdminApp = $_SESSION["uisadminapp"];
$strSearch = ( isset($_POST["search"]) ) ? Common::sanitize($_POST["search"]) : "";

Common::printHeader(FALSE,TRUE);

echo '<BODY ONLOAD="document.frmSearch.search.focus(); Buscar(0); checkUpds();">';

Common::printBodyHeader(); 

if ( $userGroupId == 99 ) {
    Common::PrintFooter();
    return;
}

$clsAccount = new Account;

echo '<DIV ALIGN="center" id="container">';
echo '<TABLE ID="tblTools" CLASS="round">';
echo '<TR>';
echo '<FORM METHOD="post" NAME="frmSearch" ID="frmSearch" OnSubmit="return Buscar(0);">';
echo '<TD WIDTH="70%" ALIGN="left">';
echo '<LABEL FOR="txtSearch">'.$LANG["buttons"][16].'</LABEL><INPUT TYPE="text" NAME="search" ID="txtSearch" onKeyUp="Buscar(1)" VALUE="'.$strSearch.'"/>';
echo '<INPUT TYPE="hidden" NAME="page" VALUE="1">';
echo '<INPUT TYPE="hidden" NAME="skey" />';
echo '<INPUT TYPE="hidden" NAME="sorder" />';
echo '<IMG SRC="imgs/search.png" title="'.$LANG["buttons"][16].'" class="inputImg" id="btnBuscar" onClick="Buscar(0);" />';
echo '<IMG SRC="imgs/clear.png" title="'.$LANG["buttons"][17].'" class="inputImg" id="btnLimpiar" onClick="Clear(\'frmSearch\',1); Buscar(0);" />';
echo '</TD>';
echo '<TD ALIGN="right" ROWSPAN="2">';

if ( ($userProfileId <= 2 OR $userIsAdminApp == 1) AND $userGroupId != 99){
    echo '<A HREF="account_add.php"><IMG SRC="imgs/add.png" title="'.$LANG['buttons'][7].'" class="inputImg" /></A>';
}

if ( $userIsAdminApp == 1 ){ 
    echo '<A HREF="pmsconfig.php"><IMG SRC="imgs/config.png" title="'.$LANG['buttons'][11].'" class="inputImg" /></A>';
    echo '<A HREF="pmsbackup.php"><IMG SRC="imgs/backup.png" title="'.$LANG['buttons'][19].'" class="inputImg" /></A>';
    echo '<A HREF="pmsusers.php"><IMG SRC="imgs/users.png" title="'.$LANG['buttons'][20].'" class="inputImg" /></A>';
    echo '<A HREF="pmslog.php"><IMG SRC="imgs/log.png" title="'.$LANG['buttons'][21].'" class="inputImg" /></A>';
}

echo '</TD></TR>';
echo '<TR WIDTH="60%"><TD WIDTH="60%" ALIGN="left">';
echo '<LABEL FOR="selCLiente">'.$LANG["accounts"][0].'</LABEL><SELECT NAME="cliente" ID="selCLiente" SIZE="1" OnChange="Buscar(0)">';
echo '<OPTION>'.$LANG["accounts"][1].'</OPTION>';

$strCliente = ( isset($_POST["cliente"]) ) ? $_POST["cliente"] : "";
$arrClientes = $clsAccount->getClientes();

foreach ( $arrClientes as $cliente ){
    if ( $cliente == $strCliente ){
        echo "<OPTION SELECTED>$cliente</OPTION>\n";
    } else {
        echo "<OPTION>$cliente</OPTION>\n";
    }
}

echo '</SELECT>';
echo '<LABEL FOR="selCategoria">'.$LANG['accounts'][2].'</LABEL><SELECT NAME="categoria" ID="selCategoria" SIZE="1" OnChange="Buscar(0)">';
echo '<OPTION>'.$LANG['accounts'][3].'</OPTION>';

$strCategoria = ( isset($_POST["categoria"]) ) ? $_POST["categoria"] : "";

foreach ( $clsAccount->getCategorias() as $catName => $catId){
    if ( $strCategoria == $catId ){
        echo "<OPTION VALUE='".$catId."' SELECTED>".$catName."</OPTION>\n";
    } else {
        echo "<OPTION VALUE='".$catId."'>".$catName."</OPTION>\n";
    }                                    
}

echo '</SELECT></TD></FORM></TR></TABLE>';
echo '<DIV ID="resBuscar"></DIV>';

Common::PrintFooter();
?>