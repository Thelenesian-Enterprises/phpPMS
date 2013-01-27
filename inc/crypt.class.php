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

if ( ! defined('PMS_ROOT') ) die("No es posible acceder directamente a este archivo<br />You can't access directly to this file");

class Crypt {

    public $pwdCrypt;
    public $strInitialVector;
    private $dbh;
    
//    function __construct() {
//        if ( ! $this->checkCryptModule() ) return FALSE;
//    }

    // Función para crear el Vector de Inicialización
    private function createIV() {
        $resEncDes = mcrypt_module_open('rijndael-256', '', 'cbc', '');
        $strInitialVector = mcrypt_create_iv(mcrypt_enc_get_iv_size($resEncDes), MCRYPT_DEV_URANDOM);
        mcrypt_module_close($resEncDes);

        return $strInitialVector;
    }

    // Función para comprobar el Vector de Inicialización
    private function CheckIV ($strInitialVector) {
        $strEscapeInitialVector = $this->dbh->real_escape_string($strInitialVector);

        if (strlen($strEscapeInitialVector) != 32 ) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // Función para encriptar una clave dada
    private function encrypt ($strValue, $strPassword, $strInitialVector) {
        $resEncDes = mcrypt_module_open('rijndael-256', '', 'cbc', '');
        mcrypt_generic_init($resEncDes, $strPassword, $strInitialVector);
        $strEncrypted = mcrypt_generic($resEncDes, $strValue);
        mcrypt_generic_deinit($resEncDes);

        return $strEncrypted;
    }

    // Función para desencriptar una clave dada
    public function decrypt($strEncrypted, $strPassword, $strInitialVector) {
        $resEncDes = mcrypt_module_open('rijndael-256', '', 'cbc', '');
        mcrypt_generic_init($resEncDes, $strPassword, $strInitialVector);
        $strDecrypted = mdecrypt_generic($resEncDes, $strEncrypted);
        mcrypt_generic_deinit($resEncDes);
        mcrypt_module_close($resEncDes);
        $strDecrypted = trim($strDecrypted);

        return $strDecrypted;
    }

    // Función para comprobar el módulo de encriptación
    public function checkCryptModule () {
        $resEncDes = mcrypt_module_open('rijndael-256', '', 'cbc', '');

        if ($resEncDes == FALSE )  {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // Función para comprobar una clave encriptada
    private function checkEncryptedPass ($strEncryptedPass) {
        $strEscapedEncryptedPass = $this->dbh->real_escape_string($strEncryptedPass);
        
        if (strlen($strEscapedEncryptedPass) != strlen($strEncryptedPass) ) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // Función para generar una clave encriptada
    public function mkPassEncrypt($pwd,$masterPwd = ""){
        //$intCounter == 0;
        if ( ! $masterPwd ) $masterPwd = $_SESSION["mPass"];
        
        $objConfig = new Config;
        $this->dbh = $objConfig->connectDb();
        
        if ( !is_object($this->dbh) ) return FALSE;
        
        do {
            do {
                $strInitialVector = $this->createIV();
                $blnCheckIv = $this->checkIV($strInitialVector);
            } while ($blnCheckIv == FALSE);
            
            $pwdCrypt = $this->encrypt($pwd, $masterPwd, $strInitialVector);
            $blnCheckEncrypted = $this->checkEncryptedPass($pwdCrypt);
        } while ($blnCheckEncrypted == FALSE );
        
        $this->pwdCrypt = $pwdCrypt;
        $this->strInitialVector = $strInitialVector;
        return TRUE;
    }
    
    // Función para generar la clave maestra encriptada con la clave del usuario
    public function mkUserMPassEncrypt($userPwd,$masterPwd){
        $objConfig = new Config;
        $this->dbh = $objConfig->connectDb();
        
        if ( !is_object($this->dbh) ) return FALSE;
        
        do {
            do {
                $strInitialVector = $this->createIV();
                $blnCheckIv = $this->checkIV($strInitialVector);
            } while ($blnCheckIv == FALSE);
            
            $pwdCrypt = $this->encrypt($masterPwd, $userPwd, $strInitialVector);
            $blnCheckEncrypted = $this->checkEncryptedPass($pwdCrypt);
        } while ($blnCheckEncrypted == FALSE );
        
        $dataCrypt = array($pwdCrypt, $strInitialVector);
        
        return $dataCrypt;
    }

    // Función para generar un hash para guardarlo
    public function mkHashPassword($pwd){
        $salt = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM)); // Obtenemos 256 bits aleatorios en hexadecimal
        $hash = hash("sha256", $salt.$pwd); // Anteponemos el salt a la clave y rehacemos el hash
        $hashPwd = $salt.$hash;
        return $hashPwd;
    }

    // Función para comprobar el hash de una clave
    public function checkHashPass($pwd, $correctHash){
        $salt = substr($correctHash, 0, 64); // Obtenemos el salt de la clave
        $validHash = substr($correctHash, 64, 64); // SHA256

        $testHash = hash("sha256", $salt . $pwd); // Re-hash de la clave a comprobar

        // Si los hashes son idénticos, la clave es válida
        //return $testHash === $validHash;
        if ( $testHash === $validHash ) return TRUE;
        
        return FALSE;
    } 
}
?>