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

class Account {
    
    public $dbh;

    // Variables de Cuenta
    var $intAccId;	
    var $strAccName;
    var $strAccCliente;
    var $intAccCategoryId;
    var $strAccCategoryName;
    var $strAccLogin;
    var $strAccUrl;
    var $strAccPwd;
    var $strAccMd5Pwd;
    var $strAccIv;
    var $strAccNotes;
    var $intAccNView;
    var $intAccNViewDecrypt;
    var $strAccDatAdded;
    var $strAccDatChanged;

    // Variables de Usuario de la Cuenta
    var $strAccUserName;
    var $intAccUserId;
    var $strAccUserGroupName;
    var $intAccUserGroupId;
    var $strAccUserEditName;
    var $intAccUserEditId;
    var $strAccUserGroupsId;

    // Variables de retorno de funciones
    var $intCategoriaId = 0;

    function __construct() {
        $objConfig = new Config;
        $this->dbh = $objConfig->connectDb();
    }
    
    // Función para obtener los datos de una cuenta
    public function getAccount ($intAccId) {
        $strQuery = "SELECT acc.intAccountId, acc.vacCliente, acc.vacName, acc.intCategoryFid, acc.intUserFId, 
                    acc.intUGroupFId, acc.intUEditFId, c.vacCategoryName, acc.vacLogin, acc.vacUrl, acc.vacPassword, 
                    acc.vacMd5Password, acc.vacInitialValue, acc.txtNotice, acc.intCountView, acc.intCountDecrypt, 
                    acc.datAdded, acc.datChanged, u1.vacUName as vacUName, u2.vacUName as vacUEditName, 
                    ug.vacUGroupName FROM accounts acc 
                    LEFT JOIN categories c ON acc.intCategoryFid=c.intCategoryId 
                    LEFT JOIN usergroups ug ON acc.intUGroupFId=ug.intUGroupId 
                    LEFT JOIN users u1 ON acc.intUserFId=u1.intUserId 
                    LEFT JOIN users u2 ON acc.intUEditFId=u2.intUserId 
                    WHERE intAccountId = ".(int)$intAccId." LIMIT 1";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resResult = $resQuery->fetch_assoc();
        $resQuery->free();

        $this->intAccId = $resResult["intAccountId"];
        $this->strAccCliente = $resResult["vacCliente"];
        $this->intAccCategoryId = $resResult["intCategoryFid"];
        $this->intAccUserId = $resResult["intUserFId"];
        $this->intAccUserGroupId = $resResult["intUGroupFId"];
        $this->intAccUserEditId = $resResult["intUEditFId"];
        $this->strAccName = $resResult["vacName"];
        $this->strAccCategoryName = $resResult["vacCategoryName"];
        $this->strAccLogin = $resResult["vacLogin"];
        $this->strAccUrl = $resResult["vacUrl"];
        $this->strAccPwd = $resResult["vacPassword"];
        $this->strAccMd5Pwd = $resResult["vacMd5Password"];
        $this->strAccIv = $resResult["vacInitialValue"];
        $this->strAccNotes = $resResult["txtNotice"];
        $this->intAccNView = $resResult["intCountView"];
        $this->intAccNViewDecrypt = $resResult["intCountDecrypt"];
        $this->strAccDatAdded = $resResult["datAdded"];
        $this->strAccDatChanged = $resResult["datChanged"];
        $this->strAccUserName = $resResult["vacUName"];
        $this->strAccUserGroupName = $resResult["vacUGroupName"];
        $this->strAccUserEditName = $resResult["vacUEditName"];
    }

    // Función para actualizar una cuenta
    public function updateAccount () {
        global $LANG;
        
        // Guardamos una copia de la cuenta en el histórico
        if ( ! $this->addHistorico($this->intAccId, $this->intAccUserEditId, false) ){
            Common::wrLogInfo(__FUNCTION__,$LANG['eventdesc'][3]);
            return FALSE;            
        }
        
        $this->updateAccGroups() || Common::wrLogInfo(__FUNCTION__,$LANG['eventdesc'][4]);
                
        $strQuery = "UPDATE accounts SET ";
        $strQuery .= "vacCliente = '".$this->dbh->real_escape_string($this->strAccCliente)."', ";
        $strQuery .= "intCategoryFid = ".(int)$this->intAccCategoryId.", ";
        $strQuery .= "vacName = '".$this->dbh->real_escape_string($this->strAccName)."', ";
        $strQuery .= "vacLogin = '".$this->dbh->real_escape_string($this->strAccLogin)."', ";
        $strQuery .= "vacUrl = '".$this->dbh->real_escape_string($this->strAccUrl)."', ";
        $strQuery .= "txtNotice = '".$this->dbh->real_escape_string($this->strAccNotes)."', ";
        $strQuery .= "intUEditFId = ".(int)$this->intAccUserEditId;
        $strQuery .= " WHERE intAccountId = ".(int)$this->intAccId;

        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return $resQuery;
    }

    // Función para actualizar la clave de una cuenta
    public function updateAccountPass () {
        global $LANG;
        
        // Guardamos una copia de la cuenta en el histórico        
        if ( ! $this->addHistorico($this->intAccId, $this->intAccUserEditId, false) ){
            Common::wrLogInfo(__FUNCTION__,$LANG['eventdesc'][3]);
            return FALSE;            
        }

        $clsConfig = new Config;

        $strQuery = "UPDATE accounts SET ";
        $strQuery .= "vacPassword = '$this->strAccPwd', ";
        if ( $clsConfig->getConfigValue("md5_pass") == TRUE ) {
                $strQuery .= "vacMd5Password = '$this->strAccMd5Pwd', ";
        }
        $strQuery .= "vacInitialValue = '$this->strAccIv', ";
        $strQuery .= "intUEditFId = ".(int)$this->intAccUserEditId;
        $strQuery .= " WHERE intAccountId = ".(int)$this->intAccId;
        
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL : ".$strQueryEsc);
            return FALSE;
        }
        
        return $resQuery;
    }

    // Función para crear una cuenta
    public function createAccount () {
        global $LANG;
        
        $clsConfig = new Config;

        $strQuery = "INSERT INTO accounts ";

        if ( $clsConfig->getConfigValue("md5_pass") == TRUE ) {;
                $strQuery .= "(vacCliente, intCategoryFid, vacName, vacLogin, vacUrl, vacPassword, vacMd5Password, vacInitialValue, ";
                $strQuery .= "txtNotice, datAdded, intUserFId, intUGroupFId)";
        } else {
                $strQuery .= "(vacCliente, intCategoryFid, vacName, vacLogin, vacUrl, vacPassword, vacInitialValue, ";
                $strQuery .= "txtNotice, datAdded, intUserFId, intUGroupFId)";
        }

        $strQuery .= " VALUES(";
        $strQuery .= "'".$this->dbh->real_escape_string($this->strAccCliente)."', ";
        $strQuery .= (int)$this->intAccCategoryId.", ";
        $strQuery .= "'".$this->dbh->real_escape_string($this->strAccName)."', ";
        $strQuery .= "'".$this->dbh->real_escape_string($this->strAccLogin)."', ";
        $strQuery .= "'".$this->dbh->real_escape_string($this->strAccUrl)."', ";
        $strQuery .= "'$this->strAccPwd', ";
        if ( $clsConfig->getConfigValue("md5_pass") == TRUE ) {
                $strQuery .= "'$this->strAccMd5Pwd', ";
        }
        $strQuery .= "'$this->strAccIv', ";
        $strQuery .= "'".$this->dbh->real_escape_string($this->strAccNotes)."', ";
        $strQuery .= "NOW(), ";
        $strQuery .= (int)$this->intAccUserId.", ";
        $strQuery .= (int)$this->intAccUserGroupId.")";
        
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $this->intAccId = $this->dbh->insert_id;
        
        $this->updateAccGroups() || Common::wrLogInfo(__FUNCTION__,$LANG['eventdesc'][4]);
        
        return $resQuery;
    }

    // Función para eliminar una cuenta
    public function deleteAccount ($intAccId, $intUId) {
        global $LANG;
        
        // Guardamos una copia de la cuenta en el histórico
        $doHistorico = $this->addHistorico($intAccId, $intUId, true) or die ($LANG['msg'][76]);

        $strQuery = "DELETE FROM accounts WHERE intAccountId = ".(int)$intAccId;
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $strQuery = "DELETE FROM acc_usergroups WHERE intAccId = ".(int)$intAccId;
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }        
        
        return $resQuery;
    }
    
    // Función para actualizar los grupos secundarios de las cuentas
    private function updateAccGroups(){
        
        $accOldUGroups = $this->getGroupsAccount($this->intAccId);
        $accNewUGroups = $this->strAccUserGroupsId;
        
        if ( is_array($accOldUGroups) AND ! is_array($accNewUGroups) ){
            $strQueryDel = "DELETE FROM acc_usergroups WHERE intAccId = ".(int)$this->intAccId;
        } else if ( is_array($accNewUGroups) ){
            if ( ! is_array($accOldUGroups) ){
                // Obtenemos los grupos a añadir
                foreach ( $accNewUGroups as $userNewGroupId ){
                    $valuesNew .= "(".(int)$this->intAccId.",".$userNewGroupId."),";
                }
            } else {
                // Obtenemos los grupos a añadir a partir de los existentes
                foreach ( $accNewUGroups as $userNewGroupId ){
                    if ( ! in_array($userNewGroupId, $accOldUGroups)){
                        $valuesNew .= "(".(int)$this->intAccId.",".$userNewGroupId."),";
                    }
                }

                // Obtenemos los grupos a eliminar
                foreach ( $accOldUGroups as $userOldGroupId ){
                    if ( ! in_array($userOldGroupId, $accNewUGroups)){
                        $valuesDel[] = $userOldGroupId;
                    }
                }

                if ( is_array($valuesDel) ){
                    $strQueryDel = "DELETE FROM acc_usergroups WHERE intAccId = ".(int)$this->intAccId." AND (";
                    $numValues = count($valuesDel);
                    $i = 0;

                    foreach ($valuesDel as $value){
                        if ( $i == $numValues - 1 ){
                            $strQueryDel .= "intUGroupId = $value";
                        } else {
                            $strQueryDel .= "intUGroupId = $value OR ";
                        }
                        $i++;
                    }
                    $strQueryDel .= ")";
                }
            }
            
            if ( $valuesNew ){
                $strQuery = "INSERT INTO acc_usergroups (intAccId, intUGroupid) VALUES ".rtrim($valuesNew, ",");
            }
        }
                
        if ( $strQueryDel ){
            $resQuery = $this->dbh->query($strQueryDel);
        
            if ( ! $resQuery ) {
                $strQueryEsc = $this->dbh->real_escape_string($strQueryDel);
                Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
                return FALSE;
            }
        }
        
        if ( $strQuery ){
            $resQuery = $this->dbh->query($strQuery);

            if ( ! $resQuery ) {
                $strQueryEsc = $this->dbh->real_escape_string($strQuery);
                Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
                return FALSE;
            }
        }
        
        if ( $strQuery OR $strQueryDel ) return $resQuery;
        
        return TRUE;
    }

    // Función para guardar el estado anterior de una cuenta
    private function addHistorico ($intAccId, $blnEliminada) {
        $objAccountHist = new Account;

        $blnModificada = 0;

        $objAccountHist->getAccount($intAccId);

        if ( $blnEliminada == false ){
                $blnModificada = 1;
                $blnEliminada = 0;
        } else {
                $blnEliminada = 1;
        }

        $strQuery = "INSERT INTO acc_history ";
        $strQuery .= "(intAccountId, vacCliente, intCategoryFid, vacName, vacLogin, vacUrl, vacPassword, vacMd5Password, vacInitialValue, ";
        $strQuery .= "txtNotice, intCountView, intCountDecrypt, datAdded, datChanged, intUserFId, intUGroupFId, intUEditFId, blnModificada, blnEliminada) ";
        $strQuery .= "VALUES(";
        $strQuery .= "$objAccountHist->intAccId, ";
        $strQuery .= "'$objAccountHist->strAccCliente', ";
        $strQuery .= "$objAccountHist->intAccCategoryId, ";
        $strQuery .= "'$objAccountHist->strAccName', ";
        $strQuery .= "'$objAccountHist->strAccLogin', ";
        $strQuery .= "'$objAccountHist->strAccUrl', ";
        $strQuery .= "'$objAccountHist->strAccPwd', ";
        $strQuery .= "'$objAccountHist->strAccMd5Pwd', ";
        $strQuery .= "'$objAccountHist->strAccIv', ";
        $strQuery .= "'$objAccountHist->strAccNotes', ";
        $strQuery .= "$objAccountHist->intAccNView, ";
        $strQuery .= "$objAccountHist->intAccNViewDecrypt, ";
        $strQuery .= "'$objAccountHist->strAccDatAdded', ";
        $strQuery .= "'$objAccountHist->strAccDatChanged', ";
        $strQuery .= "$objAccountHist->intAccUserId, ";
        $strQuery .= "$objAccountHist->intAccUserGroupId, ";
        $strQuery .= "$objAccountHist->intAccUserEditId, ";
        $strQuery .= "$blnModificada, ";
        $strQuery .= "$blnEliminada)";

        $resQuery = $objAccountHist->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return $resQuery;
    }

    // Función para obtener los grupos secundarios de la cuenta
    public function getGroupsAccount ($intAccId = "") {
        if ( $intAccId ){
            $strQuery = "SELECT intUGroupId FROM acc_usergroups WHERE intAccId = ".(int)$intAccId;
        } else {
            $strQuery = "SELECT intUGroupId FROM acc_usergroups WHERE intAccId = ".(int)$this->intAccId;
        }
        
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
                
        while ( $row = $resQuery->fetch_array(MYSQLI_NUM) ){
            $resResult[] = $row[0];
        }
        
        return $resResult;
    }

    // Función para obtener los grupos secundarios
    public function getSecGroups () {
        $strQuery = "SELECT intUGroupId,vacUGroupName FROM usergroups";		
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL :".$strQueryEsc);
            return FALSE;
        }
        
        $arrGroups = array();

        while($group = $resQuery->fetch_assoc()) {
                $arrGroups[$group["vacUGroupName"]] = $group["intUGroupId"];
        }

        return $arrGroups;
    }

    // Función para obtener los clientes
    public function getClientes () {
        $strQuery = "SELECT vacCliente FROM accounts GROUP BY vacCliente ORDER BY vacCliente";		
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        while ( $row = $resQuery->fetch_row()){
            $resResult[] = $row[0];
        }
        
        return $resResult;
    }	

    // Función para modificar el contador de vistas de la cuenta
    public function incrementViewCounter ($intAccId) {
        $strQuery = "SELECT intCountView FROM accounts WHERE intAccountId = ".(int)$intAccId." LIMIT 1";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resResult = $resQuery->fetch_assoc();

        $intCounter = $resResult["intCountView"];
        $intCounter++;

        $strQuery = "UPDATE accounts SET intCountView = $intCounter WHERE intAccountId = ".(int)$intAccId;
        $resQuery = $this->dbh->query($strQuery);

        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return $resQuery;
    }

    // Función para modificar el contador de vistas de clave de la cuenta
    public function incrementDecryptCounter ($intAccId) {
        $strQuery = "SELECT intCountDecrypt FROM accounts WHERE intAccountId = ".(int)$intAccId." LIMIT 1";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resResult = $resQuery->fetch_assoc();

        $intCounter = $resResult["intCountDecrypt"];
        $intCounter++;

        $strQuery = "UPDATE accounts SET intCountDecrypt = $intCounter WHERE intAccountId = ".(int)$intAccId;
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return $resQuery;
    }

    // Función para obtener el número de cuentas a las que tiene acceso el usuario
    public function getAccountMax () {
        $intUGroupFId = $_SESSION["ugroup"];
        $intUId = $_SESSION["uid"];
        $blnUIsAdmin = $_SESSION['uisadmin'];

        if ( ! $blnUIsAdmin ){
            $strQuery = "SELECT COUNT(DISTINCT intAccountId) FROM accounts acc
                        LEFT JOIN acc_usergroups aug ON acc.intAccountId=aug.intAccId 
                        WHERE acc.intUGroupFId = ".(int)$intUGroupFId." 
                        OR acc.intUserFId = ".(int)$intUId."
                        OR aug.intUGroupId = ".(int)$intUGroupFId;
        } else {
            $strQuery = "SELECT COUNT(intAccountId) FROM accounts";
        }
        
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $intAccountMax = $resQuery->fetch_array(MYSQLI_NUM);
        $resQuery->free();
        
        return $intAccountMax[0];
    }

    // Función para resetear la clave MD5 de las cuentas
    public function ResetAllAccountMd5Pass () {
        $strQuery = "UPDATE accounts SET vacMd5Password = '0'";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return $resQuery;
    }

    // Función para modificar la clave MD5 de la cuenta
    public function writeAccountMd5Pass ($strAccMd5Pwd, $intAccId) {
        $strQuery = "UPDATE accounts SET vacMd5Password = '$strAccMd5Pwd' WHERE intAccountId = ".(int)$intAccId;
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return $resQuery;
    }

    // Función para obtener las categorías disponibles
    public function getCategorias(){
        $strQuery = "SELECT intCategoryId, vacCategoryName FROM categories ORDER BY vacCategoryName";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resCategorias = array();

        while ($resCampo = $resQuery->fetch_assoc()){
            $resCategorias[$resCampo["vacCategoryName"]] = $resCampo["intCategoryId"];
        }
        
        $resQuery->free();
        return $resCategorias;
    }
 
    // Función para comprobar los permisos de acceso a una cuenta
    public function checkAccountAccess($strAction, $intAccUserId = "", $intAccId = "", $intAccUserGroupId = ""){
        $userGroupId = $_SESSION["ugroup"];
        $userProfileId = $_SESSION["uprofile"];
        $userId = $_SESSION["uid"];
        $blnUIsAdmin = $_SESSION["uisadmin"];
        
        // Convertimos en array la lista de grupos de la cuenta
        if ( $this->intAccId AND $intAccId == "" ){
            $arrAccUGroups = $this->getGroupsAccount($this->intAccId);
        } else if ( $intAccId ) {
            $arrAccUGroups = $this->getGroupsAccount($intAccId);
        } else {
            return FALSE;
        }
        
        //( $vacAccGroups ) ? $arrAccGroups = explode(",", $vacAccGroups) : $arrAccGroups = explode(",", $this->strAccUserGroupsId);
        ( $intAccUserGroupId )  ? $intAccUserGroupId = $intAccUserGroupId: $intAccUserGroupId = $this->intAccUserGroupId;
        
        if ( ! $intAccUserId ) $intAccUserId = $this->intAccUserId;
            
        switch ($strAction){
            case "view":
                if ( ($userId == $intAccUserId OR $userGroupId = $intAccUserGroupId OR $blnUIsAdmin == 1 OR in_array($userGroupId, $arrAccUGroups)) AND $userProfileId <= 4 ){
                    return TRUE;
                }
                break;
            case "viewpass":
                if ( ($userId == $intAccUserId OR $userGroupId = $intAccUserGroupId OR $blnUIsAdmin == 1 OR in_array($userGroupId, $arrAccUGroups)) AND $userProfileId <= 3 ){
                    return TRUE;
                }
                break;         
            case "edit":
                if ( ($userId == $intAccUserId OR $userGroupId = $intAccUserGroupId OR $blnUIsAdmin == 1 OR in_array($userGroupId, $arrAccUGroups)) AND $userProfileId <= 2 ){
                    return TRUE;
                }
                break;
            case "del":
                if ( ($userId == $intAccUserId OR $userGroupId = $intAccUserGroupId OR $blnUIsAdmin == 1 OR in_array($userGroupId, $arrAccUGroups)) AND $userProfileId <= 1 ){
                    return TRUE;
                }
                break;
            case "chpass":
                if ( ($userId == $intAccUserId OR $userGroupId = $intAccUserGroupId OR $blnUIsAdmin == 1) AND $userProfileId <= 2 ){
                    return TRUE;
                }
                break;
        }
        return FALSE;
    }

    function updateAllAccountsMPass($strCurMasterPass, $strNewMasterPwd){
        global $LANG;
        
        Common::wrLogInfo($LANG['event'][17],$LANG['eventdesc'][6]);
         
        $intUId = $_SESSION["uid"];
        $intErrCount = 0;
        
        $strQuery = "SELECT intAccountId, vacPassword, vacInitialValue FROM accounts";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $objCrypt = new Crypt();
        $blnCryptModule = $objCrypt->checkCryptModule();
        
        if ( ! $blnCryptModule ) {
            Common::wrLogInfo($LANG['event'][17],$LANG['eventdesc'][7]);
            return FALSE;
        }
        
        while ( $row = $resQuery->fetch_assoc() ){
            $strDecrypted = $objCrypt->decrypt($row["vacPassword"], $strCurMasterPass, $row["vacInitialValue"]);
            if ( $objCrypt->mkPassEncrypt($strDecrypted,$strNewMasterPwd) ){
                $this->strAccPwd = $objCrypt->pwdCrypt;
                $this->strAccIv = $objCrypt->strInitialVector;
                $this->intAccId = $row["intAccountId"];
                $this->strAccMd5Pwd = md5($strDecrypted);
                $this->intAccUserEditId = $intUId;                
                if ( ! $this->updateAccountPass() ){
                    $intErrCount++;
                    Common::wrLogInfo($LANG['event'][17],$LANG['eventdesc'][8]."'".$this->intAccId."'");
                }
                $accOkIds .= $this->intAccId.",";
            } else {
                $intErrCount++;
                continue;
            }
        }
        
        $accOkIds = trim($accOkIds,",");
        
        if ( $accOkIds ) Common::wrLogInfo($LANG['event'][17],$LANG['eventdesc'][9].": ".$accOkIds);
        
        Common::wrLogInfo($LANG['event'][17], "Fin");
        
        if ( $intErrCount > 0 ) return FALSE;
        
        return TRUE;
    }
}
?>