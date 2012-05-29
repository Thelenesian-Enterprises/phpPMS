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
    
    if ( check_session(TRUE) ) return "0";

    $objConfig = new Config;
    $objAccount = new Account;
    $objFiles = new Files;

    $blnAccountLink = $objConfig->getConfigValue("account_link");
    $intAccountCount = $objConfig->getConfigValue("account_count");
    
    unset($objConfig);

    global $strSortKey, $strSortOrder, $strCliente, $strCategory, $strSearch, $intPage;
    
    $strSortKey = $_POST["skey"];
    $strSortOrder = $_POST["sorder"];
    $strCliente = $_POST["cliente"];
    $strCategory = $_POST["categoria"];
    $strSearch = addslashes($_POST["search"]);
    $intPage = $_POST["page"];
    
    $intUGroupFId = $_SESSION["ugroup"];
    $intProfileId = $_SESSION["uprofile"];
    $intUId = $_SESSION["uid"];
    $blnUIsAdmin = $_SESSION["uisadmin"];

    // Variables por defecto del formulario
    if ( $strCliente == "" ) $strCliente = "TODOS";
    if ( $strCategory == "" ) $strCategory = "TODAS";
    if ( $strSortOrder == "" ) $strSortOrder = "ASC";
    if ( $intPage == "" ) $intPage = 1;

    switch ($strSortKey) {
        case 1:
            $strOrder = "vacName $strSortOrder";
            break;
        case 2:
            $strOrder = "vacCategoryName $strSortOrder";
            break;
        case 3:
            $strOrder = "vacLogin $strSortOrder";
            break;
        case 4:
            $strOrder = "vacUrl $strSortOrder";
            break;
        case 5:
            $strOrder = "vacCliente $strSortOrder";
            break;
        default :
            $strOrder = "vacCliente, vacName";
            break;
    }

    $intLimitStart = (($intPage - 1) * $intAccountCount) ;
    $intLimitCount = $intAccountCount;

    function truncate($str, $len) {
        $tail = max(0, $len-10);
        $trunk = substr($str, 0, $tail);
        $trunk .= strrev(preg_replace('~^..+?[\s,:]\b|^...~', '...', strrev(substr($str, $tail, $len-$tail))));
        return $trunk;
    }
    
    function printSearchKeys(){
        global $strSortKey, $strSortOrder, $strCliente, $strCategory, $strSearch, $intPage;
        
        echo '<INPUT TYPE="hidden" NAME="skey" VALUE="'.$strSortKey.'" />';
        echo '<INPUT TYPE="hidden" NAME="sorder" VALUE="'.$strSortOrder.'" />';
        echo '<INPUT TYPE="hidden" NAME="cliente" VALUE="'.$strCliente.'" />';
        echo '<INPUT TYPE="hidden" NAME="categoria" VALUE="'.$strCategory.'" />';
        echo '<INPUT TYPE="hidden" NAME="search" VALUE="'.$strSearch.'" />';
        echo '<INPUT TYPE="hidden" NAME="page" VALUE="'.$intPage.'" />';
    }
?>
<TABLE ID="tblBuscar">
    <THEAD>
        <TR CLASS="headerBlue">
            <TH WIDTH="15%"><A onClick="searchSort(5,<? echo $intPage; ?>);">Cliente</A></TH>
            <TH WIDTH="15%"><A onClick="searchSort(1,<? echo $intPage; ?>);">Servicio / Recurso</A></TH>
            <TH WIDTH="10%"><A onClick="searchSort(2,<? echo $intPage; ?>);">Categoría</A></TH>
            <TH WIDTH="15%"><A onClick="searchSort(3,<? echo $intPage; ?>);">Login</A></TH>
            <TH WIDTH="20%"><A onClick="searchSort(4,<? echo $intPage; ?>);">URL / IP</A></TH>
            <TH WIDTH="20%"><IMG SRC="imgs/notes.png" TITLE="Notas" /></TH>
            <TH WIDTH="5%"><IMG SRC="imgs/group.png" TITLE="Grupo" /></TH>
            <TH WIDTH="5%"><IMG SRC="imgs/attach.png" TITLE="Archivos adjuntos" /></TH>
            <TH WIDTH="20%">Acciones</TH>
        </TR>
    </THEAD>
<?php
    $strQuerySelect = "SELECT DISTINCT acc.intAccountId, acc.vacCliente, g.vacCategoryName, acc.vacName, 
                    acc.vacLogin, acc.vacUrl, acc.txtNotice, acc.intUserFId, acc.intUGroupFId, ug.vacUGroupName
                    FROM accounts acc
                    LEFT JOIN categories g ON acc.intCategoryFid=g.intCategoryId 
                    LEFT JOIN usergroups ug ON acc.intUGroupFId=ug.intUGroupId 
                    LEFT JOIN acc_usergroups aug ON acc.intAccountId=aug.intAccId ";
    
    $strQueryCount = "SELECT COUNT(DISTINCT intAccountId) AS Number FROM accounts 
                    LEFT JOIN acc_usergroups aug ON intAccountId=aug.intAccId ";

    if ( $strSearch != "" ) {
        $strQueryWhere = " WHERE (vacName LIKE '%$strSearch%' OR vacLogin LIKE '%$strSearch%' OR vacUrl LIKE '%$strSearch%' OR txtNotice LIKE '%$strSearch%')";
        
        // Comprobamos el grupo del usuario y si es admin, para acotar la búsqueda a sus grupos y perfil
        if ( $intUGroupId != 1 OR $blnUIsAdmin != 1) {
            $strQueryWhere .= "AND (intUGroupFId = $intUGroupFId OR intUserFId = $intUId OR aug.intUGroupId = $intUGroupFId)";
        }

        if ( $strCategory != "TODAS" ) $strQueryWhere .= " AND intCategoryFId = ".$strCategory;
        if ( $strCliente != "TODOS" ) $strQueryWhere .= " AND vacCliente = '$strCliente'";

    } else {
        if ( $intUGroupFId != 1 AND $blnUIsAdmin == 0 ) {
            $strQueryWhere = " WHERE (intUGroupFId = $intUGroupFId OR intUserFId = $intUId OR aug.intUGroupId = $intUGroupFId) ";

            if ( $strCategory != "TODAS" ) $strQueryWhere .= "AND intCategoryFid = ".$strCategory;
            if ( $strCliente != "TODOS" ) $strQueryWhere .= "AND vacCliente LIKE '$strCliente'";
        } else {
            if ( $strCategory != "TODAS" ) $strQueryWhere .= "WHERE intCategoryFid = ".$strCategory;
            
            if ( $strCliente != "TODOS" AND $strCategory == "TODAS" ){
                $strQueryWhere .= "WHERE vacCliente LIKE '$strCliente'";
            } elseif ( $strCliente != "TODOS" ) {
                $strQueryWhere .= " AND vacCliente LIKE '$strCliente'";
            }
        }
    }

    $strQueryOrder = " ORDER BY ".$strOrder;

    if ($intAccountCount != 99 ) $strQueryLimit = " LIMIT $intLimitStart, $intLimitCount";
    
    $strQuery = $strQuerySelect.$strQueryWhere.$strQueryOrder.$strQueryLimit;
    $strQueryCount = $strQueryCount.$strQueryWhere;
    
    // Consulta para obtener el número de registros de la búsqueda
    $resQuery = $objAccount->dbh->query($strQueryCount);
    
    if ( ! $resQuery ) {
        //echo $strQueryCount;
        Common::wrLogInfo("Search", $objAccount->dbh->error);
        return FALSE;
    }
    
    $resResult = $resQuery->fetch_assoc();
    $resQuery->free();
    
    $intAccountMax = $resResult["Number"];
    $intPageMax = ceil($intAccountMax / $intAccountCount);    

    // Consulta de la búsqueda de cuentas
    $resQuery = $objAccount->dbh->query($strQuery);
    
    if ( ! $resQuery ) {
        //echo $strQuery;
        Common::wrLogInfo("Search", $objAccount->dbh->error);
        return FALSE;
    }

    $strTableClass = "odd";
    
    // Mostrar los resultados de la búsqueda
    while ( $account = $resQuery->fetch_assoc()) {
        $intAccId = $account["intAccountId"];
        $strAccName = $account["vacName"];
        $strAccNotes = $account["txtNotice"];
        $strAccUrl = $account["vacUrl"];
        $intAccUserId = $account["intUserFId"];
        $intAccUserGroupId = $account["intUGroupFId"];
        
        if (strlen($strAccNotes) > 100 ) {
            $strAccNotes = substr($strAccNotes, 0, 97) . "...";
        }
       
        echo '<TR CLASS="'.$strTableClass.'" ondblClick="document.frmView_'.$account["intAccountId"].'.submit();">';
        echo '<TD CLASS="txtCliente">';
        
        if ( $CFG_PMS["wikienabled"] ){
            $wikiLink = $CFG_PMS["wikisearchurl"].$account["vacCliente"];
            echo '<A HREF="'.$wikiLink.'" TARGET="blank" TITLE="Buscar en Wiki">'.$account["vacCliente"].'</A>';
        } else{
            echo $account["vacCliente"];
        }
            
        echo '</TD><TD>';
        
        if ( $blnAccountLink == "TRUE" ) {
            echo '<A onClick="document.frmView_'.$account["intAccountId"].'.submit();" TITLE="Ver cuenta">'.$strAccName.'</A>';
        } else {
            echo $strAccName;
        }
        
        echo '</TD><TD ALIGN="center">'.$account["vacCategoryName"].'</TD><TD>';

        $vacLogin = $account["vacLogin"];
        
        if ( strlen($vacLogin) >= 20 ) $vacLogin = truncate($vacLogin,20);
        
        echo ($vacLogin) ? $vacLogin : '&nbsp;';
        
        echo '</TD><TD>';
        
        if ( strlen($strAccUrl) >= 25 ){
            $strAccUrl_short = truncate($strAccUrl,25);
            $strAccUrl = '<A HREF="http://'.$strAccUrl.'" TARGET="_blank" TITLE="'.$strAccUrl.'">'.$strAccUrl_short.'</A>';
        } else {			
            $strAccUrl = '<A HREF="http://'.$strAccUrl.'" TARGET="_blank" TITLE="'.$strAccUrl.'">'.$strAccUrl.'</A>';
        }

        echo ( $strAccUrl ) ? $strAccUrl : '&nbsp;';
        echo '</TD><TD>';
        echo ($strAccNotes) ? $strAccNotes : '&nbsp;';
        echo '</TD>';
        
        echo'<TD ALIGN="center">'.$account["vacUGroupName"].'</TD>';
        
        echo '<TD ALIGN="center">';

        $intNumFiles = $objFiles->countFiles($account["intAccountId"]); 
        echo ($intNumFiles) ? $intNumFiles : '&nbsp;'; 

        echo '</TD>';
        echo '<TD ALIGN="center"><TABLE CLASS="altTable"><TR>';
        
        // Comprobación de accesos para mostrar enlaces de acciones de cuenta
        if ( $objAccount->checkAccountAccess("view",$intAccUserId, $intAccId, $intAccUserGroupId) ){
            echo '<TD><FORM ACTION="account_view.php" METHOD="post" NAME="frmView_'.$account["intAccountId"].'">';
            echo '<INPUT TYPE="hidden" NAME="accountid" VALUE="'.$account["intAccountId"].'">';
            echo '<INPUT TYPE="hidden" NAME="decode" VALUE="0">';
            printSearchKeys();
            echo '<INPUT TYPE="image" SRC="imgs/view.png" title="Ver cuenta" class="inputImg" />';
            echo '</FORM></TD>';
        }


        if ( $objAccount->checkAccountAccess("viewpass",$intAccUserId, $intAccId, $intAccUserGroupId) ){
            echo '<TD><IMG SRC="imgs/user-pass.png" TITLE="Ver clave" onClick="verClave('.$intAccId.', 1)" CLASS="inputImg" /></TD>';
        } 

        if ( $objAccount->checkAccountAccess("edit",$intAccUserId, $intAccId, $intAccUserGroupId) ){
            echo '<TD><FORM ACTION="account_edit.php" method="post">';
            echo '<INPUT TYPE="hidden" NAME="accountid" VALUE="'.$account["intAccountId"].'" />';
            echo '<INPUT TYPE="hidden" NAME="decode" VALUE="0">';
            printSearchKeys();
            echo '<INPUT TYPE="image" SRC="imgs/edit.png" title="Editar cuenta" class="inputImg" />';
            echo '</FORM></TD>';
        }

        if ( $objAccount->checkAccountAccess("del",$intAccUserId, $intAccId, $intAccUserGroupId) ){
            echo '<TD><FORM ACTION="account_view.php" METHOD="post">';
            echo '<INPUT TYPE="hidden" NAME="accountid" VALUE="'.$account["intAccountId"].'">';
            echo '<INPUT TYPE="hidden" NAME="delete" VALUE="1">';
            printSearchKeys();
            echo '<INPUT TYPE="image" SRC="imgs/delete.png" title="Eliminar" class="inputImg" />';
            echo '</FORM></TD>';
        }

        if ( $intProfileId <= 1 AND $CFG_PMS["wikienabled"] ){
            if ( is_array($CFG_PMS["wikifilter"]) ){
                foreach ( $CFG_PMS["wikifilter"] as $strFilter ){
                    if ( preg_match("/^".$strFilter.".*/i", $strAccName) ){
                        $wikiLink = $CFG_PMS["wikipageurl"].$strAccName;
                        //$nWikiServer =  strtr($strAccName, "-", ".");
                        echo '<TD><A HREF="'.$wikiLink.'" TARGET="_blank" ><IMG SRC="imgs/wiki.png" TITLE="Enlace a Wiki" CLASS="inputImg" /></A></TD>';
                    }
                }
            }
        }
        echo '</TR></TABLE></TD></TR>';

        if ($strTableClass == "odd") {
		$strTableClass = "even";
	} else {
		$strTableClass = "odd";
	}
    // Fin del bucle para obtener los registros
    }
    $resQuery->free();
    
    echo '</TABLE>';
    echo '<TABLE CLASS="altTable" ID="pageNav">';
    echo '<TR><TD ALIGN="LEFT">';
    echo ("Página ".$intPage." / ".$intPageMax." - filtro: ");
        
    if ( $strSearch != "" OR $strCategory != "TODAS" OR $strCliente != "TODOS" ) {
        echo '<B CLASS="altTxtRed">on</B>';
    } else {
        echo '<B CLASS="altTxtGreen">off</B>';
    }

    echo " - cuentas: ".$intAccountMax." / ".$objAccount->getAccountMax();
    echo '</TD><TD ALIGN="right">';

    if ( $intPage > 1 ) {
        echo '<IMG SRC="imgs/aleft.png" onClick="searchSort(\''.$strSortKey.'\',1,1);" TITLE="Primera página" />';
        echo '<IMG SRC="imgs/apleft.png" onClick="searchSort(\''.$strSortKey.'\','.($intPage - 1).',1);" TITLE="Página anterior" />';
    }

    for ( $intCounter = 1; $intCounter <= 10; $intCounter++) {
        if ( $intCounter > $intPageMax ) { break; }

        if ( $intCounter != $intPage ) {
            echo '<A onClick="searchSort(\''.$strSortKey.'\','.$intCounter.',1);" >'.$intCounter.'</A>';
        } else {
            echo '<span class="current">'.$intCounter.'</span>';
        }
    }

    if ( $intPage < $intPageMax ) {
        echo '<IMG SRC="imgs/apright.png" onClick="searchSort(\''.$strSortKey.'\','.($intPage + 1).',1);" TITLE="Siguiente página" />';
        echo '<IMG SRC="imgs/aright.png" onClick="searchSort(\''.$strSortKey.'\','.$intPageMax.',1);" TITLE="Última página" />';		
    }
    
    echo '</TD></TR></TABLE>';
?>