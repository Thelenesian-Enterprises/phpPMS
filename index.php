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
    check_session();

    $userGroupId = $_SESSION["ugroup"];
    $userProfileId = $_SESSION["uprofile"];
    $userIsAdmin = $_SESSION["uisadmin"];

    Common::printHeader(FALSE,TRUE);
?>
    <BODY ONLOAD="document.frmSearch.search.focus(); Buscar(0);">
        <?php 
            Common::printBodyHeader(); 

            if ( $userGroupId == 99 ) {
                Common::PrintFooter();
                return;
            }

            $clsAccount = new Account;
        ?>
		
        <DIV ALIGN="center" id="container">
        <TABLE ID="tblTools" CLASS="round">
            <TR WIDTH="60%">
                <FORM METHOD="post" NAME="frmSearch" ID="frmSearch" OnSubmit="return Buscar(0);">
                <TD WIDTH="60%"  ALIGN="left">
                    Buscar
                    <INPUT TYPE="text" NAME="search" ID="txtSearch" onKeyUp="Buscar(1)" VALUE="<?php echo $_POST["search"]; ?>"/>
                    <INPUT TYPE="hidden" NAME="page" VALUE="1">
                    <INPUT TYPE="hidden" NAME="skey" />
                    <INPUT TYPE="hidden" NAME="sorder" />					
                    <IMG SRC="imgs/search.png" title="Buscar" class="inputImg" id="btnBuscar" onClick="Buscar(0);" />
                    <IMG SRC="imgs/clear.png" title="Limpiar" class="inputImg" id="btnLimpiar" onClick="Clear('frmSearch',1); Buscar(0);" />
                </TD>
                <TD ALIGN="right" ROWSPAN="2">
<?php               
                    if ( ($userGroupId == 1 OR $userProfileId <= 2 OR $userIsAdmin == 1) AND $userGroupId != 99){
                        echo '<A HREF="account_add.php"><IMG SRC="imgs/add.png" title="Nueva cuenta" class="inputImg" /></A>';
                    }
                    if ( $userGroupId == 1 OR $userIsAdmin == 1 ){ 
                        echo '<A HREF="pmsconfig.php"><IMG SRC="imgs/config.png" title="Configuración" class="inputImg" /></A>';
                        echo '<A HREF="pmsbackup.php"><IMG SRC="imgs/backup.png" title="Realizar Backup" class="inputImg" /></A>';
                    }
                    if ( $userIsAdmin == 1 ){ 
                        echo '<A HREF="pmsusers.php"><IMG SRC="imgs/users.png" title="Gestión de Usuarios" class="inputImg" /></A>';
                        echo '<A HREF="pmslog.php"><IMG SRC="imgs/log.png" title="Ver Log" class="inputImg" /></A>';
                    }
?>	
                </TD>
            </TR>	
                <TR WIDTH="60%">
                <TD WIDTH="60%" ALIGN="left">
                    Cliente
                    <SELECT NAME="cliente" SIZE="1" OnChange="Buscar(0)">
                        <OPTION>TODOS</OPTION>
<?php
                        $strCliente = $_POST["cliente"];
                        $arrClientes = $clsAccount->getClientes();

                        foreach ( $arrClientes as $cliente ){
                            if ( $cliente == $strCliente ){
                                echo "<OPTION SELECTED>$cliente</OPTION>\n";
                            } else {
                                echo "<OPTION>$cliente</OPTION>\n";
                            }
                        }
?>
                    </SELECT>
                    Categoría
                    <SELECT NAME="categoria" SIZE="1" OnChange="Buscar(0)">
                        <OPTION>TODAS</OPTION>
<?php
                        $strCategoria = $_POST["categoria"];

                        foreach ( $clsAccount->getCategorias() as $catName => $catId){
                            if ( $strCategoria == $catId ){
                                echo "<OPTION VALUE='".$catId."' SELECTED>".$catName."</OPTION>\n";
                            } else {
                                echo "<OPTION VALUE='".$catId."'>".$catName."</OPTION>\n";
                            }                                    
                        }                          
?>
                    </SELECT>
                </TD>
                </FORM>
            </TR>
        </TABLE>
        <DIV ID="resBuscar"></DIV>
<?php Common::PrintFooter(); ?>