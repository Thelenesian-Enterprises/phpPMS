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
    
    if ( check_session(TRUE) == 1 ){
        echo '<span class="altTxtRed">'.$LANG['msg'][35].'</span>';
        return;
    }

    $objAccount = new Account;

    $intAccId = $_POST["accountid"];
    $fullTxt = $_POST["full"];

    $objAccount->getAccount($intAccId);

    // Comprobamos si el usuario tiene acceso a esta cuenta
    if ( ! $objAccount->checkAccountAccess("viewpass") ){
        echo '<span class="altTxtRed">'.$LANG['msg'][91].'</span>';
        return;
    }

    $objCrypt = new Crypt;
    $objConfig = new Config;    
    $objCommon = new Common;
    
    $strMasterPwd = $_SESSION["mPass"];
    $strDecrypted = $objCrypt->decrypt($objAccount->strAccPwd, $strMasterPwd, $objAccount->strAccIv);

    $blnNoMd5 = FALSE;
    
    //Comprobamos si se utiliza MD5 para la clave
    if ( $objConfig->getConfigValue("md5_pass") == "TRUE" ) {
        if ( $objAccount->strAccMd5Pwd != "0" ) {
            // Comprobamos si coincide el hash MD5 guardado con el de la clave desencriptada
            if ( md5($strDecrypted) == $objAccount->strAccMd5Pwd ) {	
                $blnDecryptCheck = TRUE;
                if ( $objConfig->getConfigValue("password_show") == "FALSE" ) {
                    $strPasswordOutput = "clave no mostrada - solo se puede copiar al portapapeles";
                } else {
                    $strPasswordOutput = $strDecrypted;
                }
            } else {
                $strPasswordOutput = '<SPAN CLASS="altTxtRed">'.$LANG['msg'][3].'</SPAN>';
                $blnDecryptCheck = FALSE;
            }
        } else {
            $blnDecryptCheck = TRUE;
            $blnNoMd5 = TRUE;
            $strPasswordOutput = $strDecrypted . ' <B CLASS="altTxtRed">**</B>';
        }
    } else {
        if ( $objConfig->getConfigValue("password_show") == "FALSE" ) {
            $blnDecryptCheck = TRUE;
            $strPasswordOutput = "clave no mostrada - solo se puede copiar al portapapeles";
        } else {
            $blnDecryptCheck = TRUE;
            $strPasswordOutput = $strDecrypted;
        }
    }

    if ( $blnDecryptCheck == TRUE ) {
        $objAccount->incrementDecryptCounter($intAccId);
        $objCommon->wrLogInfo($LANG['event'][22], "ID=$intAccId;".$LANG['eventdesc'][17].":".$objAccount->strAccCliente."/".$objAccount->strAccName.";IP:".$_SERVER['REMOTE_ADDR']);
    }

    if ( $fullTxt ){
        echo '<table>
            <tr>
                <td><span class="altTxtBlue">'.$LANG['accounts'][6].'</span></td>
                <td>'.$objAccount->strAccLogin.'</td>
            </tr>
            <tr>
                <td><span class="altTxtBlue">'.$LANG['accounts'][20].'</span></td>
                <td>'.trim($strPasswordOutput).'</td>
            </tr>
            </table>';
    } else {
        echo trim($strPasswordOutput);
    }
?>