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

// TODO: Pasar a AJAX

    define('PMS_ROOT', '.');
    include_once (PMS_ROOT."/inc/includes.php");
    check_session();

    $objAccount = new Account;
    $objCommon = new Common;

    $intAccountId = $_POST["accountid"];
    $strMd5Sum = $_POST["md5sum"];
    $strSortKey = $_POST["skey"];
    $strSortOrder = $_POST["sorder"];
    $strGroup = $_POST["group"];
    $strSearch = $_POST["search"];
    $intPage = $_POST["page"];

    $objAccount->writeAccountMd5Pass($strMd5Sum,$intAccountId);
    
    Common::printHeader(FALSE,TRUE);
?>
    <BODY>
    <?php Common::printBodyHeader(); ?>

    <DIV ALIGN="center">
        <H2>Guardar Checksum de Clave</H2>

        <TABLE WIDTH="80%">
            <TR BGCOLOR="#DDDDDD">
                <TD ALIGN="center"><B>Checksum de la clave guardado</B></TD>
            </TR>
        </TABLE>
        <BR /><BR />
        <TABLE WIDTH="80%">
            <TR>
                <TD ALIGN="center"><?php $objCommon->PrintBackForm($strSortKey, $strSortOrder, $strGroup, $strCliente, $strSearch, $intPage); ?></TD>
            </TR>
        </TABLE>
        <BR /><BR />
    </DIV>
<?php Common::PrintFooter(); ?>