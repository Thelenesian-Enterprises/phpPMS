<?php
//Copyright (c) 2012 Rubén Domínguez
//  
//This file is part of phpPMS.
//
//phpPMS is free software: you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation, either version 3 of the License, or
//(at your option) any later version.
//
//phpPMS is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with Foobar.  If not, see <http://www.gnu.org/licenses/>.


/**
 *
 * @author nuxsmin
 * @version 0.9b
 * @link http://www.cygnux.org/phppms
 * 
 */

    define('PMS_ROOT', '.');
    include_once (PMS_ROOT."/inc/includes.php");
    
    // Comprobamos si la sesión ha caducado
    if ( check_session(TRUE) ) {
        $resXML["status"] = 1;
        $resXML["description"] = "La sesión no se ha iniciado o ha caducado";
        Common::printXML($resXML);
        return;
    }
    
    Users::checkUserAccess("config") || die ('<DIV CLASS="error">No tiene permisos para acceder a esta página.</DIV>');
    
    $strAction = $_POST["action"];
    $objConfig = new Config;

    if ( $strAction == "config" ){
        $blnPasswordShow = $_POST["password_show"];
        $blnAccountLink = $_POST["account_link"];
        $blnMd5Password = $_POST["md5_pass"];
        $blnMd5PasswordOld = $_POST["md5_pass_old"];
        
        if ( $_POST["account_count"] == "all" ) {
            $intAccountCount = 99;
        } else {
            $intAccountCount = $_POST["account_count"];
        }

        $objConfig->arrConfigValue["password_show"] = $blnPasswordShow;
        $objConfig->arrConfigValue["account_link"] = $blnAccountLink;
        $objConfig->arrConfigValue["account_count"] = $intAccountCount;
        $objConfig->arrConfigValue["md5_pass"] = $blnMd5Password;

//        if ($blnMd5Password == "FALSE" AND $blnMd5PasswordOld == "TRUE") {
//            $clsAccount = new Account;
//            $clsAccount->ResetAllAccountMd5Pass();
//        }

        if ( $objConfig->writeConfig() ){
            $resXML["status"] = 0;
            $resXML["description"] = "Configuración guardada correctamente";
            Common::wrLogInfo("Modifcar configuración", "ID: $post_accountid;");
            Common::sendEmail("Configuración modificada");			
        } else {
            $resXML["status"] = 1;
            $resXML["description"] = "Error al guardar la configuración";
        }

    } elseif ( $strAction == "crypt"){
        $strCurMasterPass = $_POST["curMasterPwd"];    
        $strNewMasterPass = $_POST["newMasterPwd"];
        $strNewMasterPassR = $_POST["newMasterPwdR"];
        $intConfirmPassChange = $_POST["confirmPassChange"];
        
        if ( $strNewMasterPass != "" AND $strCurMasterPass != ""){
            if ( $intConfirmPassChange == 1 ){
                if ( $strNewMasterPass == $strNewMasterPassR ){
                    $objCrypt = new Crypt;

                    if ( ! $objCrypt->checkHashPass($strCurMasterPass, $objConfig->getConfigValue("masterPwd")) ){
                        $resXML["status"] = 1;
                        $resXML["description"] = "La clave maestra actual no coincide";
                        Common::printXML($resXML);
                        return;                    
                    }

                    $objAccount = new Account;
                    
                    if ( ! $objAccount->updateAllAccountsMPass($strCurMasterPass,$strNewMasterPass) ){
                        $resXML["status"] = 1;
                        $resXML["description"] = "Errores al actualizar las claves de las cuentas";
                        Common::printXML($resXML);
                        return;
                    }
                    
                    $hashMPass = $objCrypt->mkHashPassword($strNewMasterPass);
                    $objConfig->arrConfigValue["masterPwd"] = $hashMPass;
                    
                    if ( $objConfig->writeConfig() ){
                        $resXML["status"] = 0;
                        $resXML["description"] = "Clave maestra cambiada correctamente";
                    } else {
                        $resXML["status"] = 1;
                        $resXML["description"] = "Error al guardar el hash de la clave maestra";
                    }
                } else {
                    $resXML["status"] = 1;
                    $resXML["description"] = "Las claves maestras no coinciden";
                    Common::printXML($resXML);
                    return;
                }
            } else{
                $resXML["status"] = 1;
                $resXML["description"] = "Se ha de confirmar el cambio de clave";
                Common::printXML($resXML);
                return;
            }
        } else {
            $resXML["status"] = 1;
            $resXML["description"] = "Clave maestra no indicada";
        }
    }
        
    Common::printXML($resXML);
?>