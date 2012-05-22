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
 * Clase para la gestión de usuarios
 *
 * @author nuxsmin
 * @version 0.9b
 * @link http://www.cygnux.org/phppms
 */

class Users {

    private $dbh;
    
    var $intUserId;
    var $strName;
    var $intGroupId;
    var $strLogin;
    var $strPwd;
    var $strEmail;
    var $strNotes;
    var $intCount;
    var $intProfile;
    var $blnAdmin;
    var $blnDisabled;
    
    var $arrUserInfo;
    
    // Variables para grupos de usuarios
    var $intUGroupId;
    var $strUGroupName;
    var $strUGroupDesc;
    

    function __construct() {
        $objConfig = new Config;
        $this->dbh = $objConfig->connectDb();
    }

    // Función para obtener los datos del usuario en MySQL
    public function getUserInfo($strLogin) {
        $strQuery = "SELECT intUserId, vacUName, intUGroupFid, vacULogin, vacUEmail, txtUNotes, 
                    intUCount, intUProfile, vacUGroupName, blnIsAdmin, blnFromLdap, blnDisabled 
                    FROM users LEFT JOIN usergroups ON users.intUGroupFid=usergroups.intUGroupId 
                    WHERE vacULogin = '$strLogin' LIMIT 1";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $this->arrUserInfo = $resQuery->fetch_assoc();
        $resQuery->free();
		return TRUE;
    }
    
    // Función para mostrar la tabla de gestión de usuarios
    public function getUsersTable(){
        $strQuery = "SELECT intUserId, vacUName, intUGroupFid, vacULogin, vacUEmail, txtUNotes, intUProfile, 
                    blnIsAdmin, blnFromLdap, blnDisabled FROM users ORDER BY vacUName";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $objAccount = new Account;
        $usersgroups = $objAccount->getSecGroups();
        unset($objAccount);
        
        $usersprofiles = array("Acceso total","Editar&Borrar","Editar","Ver&Clave","Ver");
        
        echo '<FORM NAME="frm_tblusers" ID="frm_tblusers" OnSubmit="return false;" >';
        echo '<TABLE ID="tblUsers"><THEAD><TR CLASS="header">';
//        $headers = array("Nombre", "Login", "Perfil", "Grupo", "Admin", "Email", "Notas", "Acciones");
//        
//        foreach ($headers as $header){
//            echo '<TD COLSPAN="2">'.$header.'</TD>';
//        }
        
        echo '</TR></THEAD><TBODY>';
        
        while ( $user = $resQuery->fetch_assoc() ){
            
            $intUsrId = $user["intUserId"];
            $username = $user["vacUName"];
            $userlogin = $user["vacULogin"];
            $userprofile = $user["intUProfile"];
            $usergroup = $user["intUGroupFid"];
            $useremail = $user["vacUEmail"];
            $usernotes = $user["txtUNotes"];
                   
            $chkadmin = ( $user["blnIsAdmin"] ) ? 'checked="checked"' : '';
            $chkdisabled = ( $user["blnDisabled"] ) ? 'checked="checked"' : '';
            
            if ( $user["blnFromLdap"] ){
                $chkldap = 'checked="checked"';
                $clsdisabled = 'txtdisabled';
                $lnkPass = '';
            } else {
                $chkldap = '';
                $clsdisabled = '';
                $lnkPass = '<TD><INPUT TYPE="image" SRC="imgs/key.png" TITLE="Cambiar clave" CLASS="inputImg" Onclick="return usrUpdPass('.$intUsrId.',\''.$userlogin.'\');" /></TD>';
            }

            $lnkEdit = '<TD><INPUT TYPE="image" SRC="imgs/edit.png" TITLE="Editar usuario" CLASS="inputImg" Onclick="return userMgmt(\'edit\','.$intUsrId.');" /></TD>';
            $lnkDel = '<TD><INPUT TYPE="image" SRC="imgs/delete.png" TITLE="Eliminar usuario" CLASS="inputImg" Onclick="return userMgmt(\'del\','.$intUsrId.');" /></TD>';
            $lnkSave = '<TD><INPUT TYPE="image" SRC="imgs/check.png" TITLE="Guardar usuario" CLASS="inputImg" Onclick="return userMgmt(\'save\','.$intUsrId.');" /></TD>';

            $rowclass = ( $rowclass == "row_odd" ) ? "row_even": "row_odd";

            echo '<TR CLASS="usr_odd usrrow_'.$intUsrId.' '.$rowclass.'">';
            echo '<TD CLASS="ilabel">Nombre</TD><TD CLASS="itext"><INPUT TYPE="text" ID="usrname_'.$intUsrId.'" NAME="usrname_'.$intUsrId.'" TITLE="'.$username.'" VALUE="'.$username.'" CLASS="txtuser '.$clsdisabled.'" readonly /></LABEL></TD>';
            echo '<TD CLASS="ilabel">Login</TD><TD CLASS="itext"><INPUT TYPE="text" ID="usrlogin_'.$intUsrId.'" NAME="usrlogin_'.$intUsrId.'" TITLE="'.$userlogin.'" VALUE="'.$userlogin.'" CLASS="txtlogin '.$clsdisabled.'" readonly /></TD>';
            echo '<TD CLASS="ilabel">Perfil</TD><TD CLASS="itext"><SELECT id="usrprofile_'.$intUsrId.'" NAME="usrprofile_'.$intUsrId.'" CLASS="'.$clsdisabled.'" disabled>';
            foreach ($usersprofiles as $profileid => $profiledesc ){
                ( $profileid == $userprofile ) ? $profileselected = "selected": $profileselected = "";
                echo '<OPTION VALUE="'.$profileid.'" '.$profileselected.'>'.$profiledesc.'</OPTION>';
            }
            echo '</SELECT></TD>';
            echo '<TD CLASS="ilabel">Admin</TD><TD CLASS="checkbox"><INPUT TYPE="checkbox" ID="chkadmin_'.$intUsrId.'" NAME="chkadmin_'.$intUsrId.'" CLASS="icheck" '.$chkadmin.' disabled /></TD>';
            echo '<TD ROWSPAN="2"><TABLE ID="tblActions"><TR>'.$lnkEdit.$lnkSave.$lnkDel.$lnkPass.'</TR></TABLE></TD>';
            echo '</TR><TR CLASS="usr_even usrrow_'.$intUsrId.' '.$rowclass.'">';
            echo '<TD CLASS="ilabel">Email</TD><TD CLASS="itext"><INPUT TYPE="text" ID="usremail_'.$intUsrId.'" NAME="usremail_'.$intUsrId.'" TITLE="'.$useremail.'" VALUE="'.$useremail.'" CLASS="txtemail '.$clsdisabled.'" readonly/></LABEL></TD>';
            echo '<TD CLASS="ilabel">Notas</TD><TD CLASS="itext"><INPUT TYPE="text" id="usrnotes_'.$intUsrId.'" NAME="usrnotes_'.$intUsrId.'" TITLE="'.$usernotes.'" VALUE="'.$usernotes.'" CLASS="txtnotes '.$clsdisabled.'" readonly /></TD>';
            echo '<TD CLASS="ilabel">Grupo</TD><TD CLASS="itext"><SELECT ID="usrgroup_'.$intUsrId.'" NAME="usrgroup_'.$intUsrId.'" CLASS="'.$clsdisabled.'" disabled>';
            foreach ($usersgroups as $groupname => $groupid){
                ( $groupid == $usergroup ) ? $grpselected = "selected": $grpselected = "";
                echo '<OPTION VALUE="'.$groupid.'" '.$grpselected.'>'.$groupname.'</OPTION>';
            }
            echo '</SELECT></TD>';
            echo '<TD CLASS="ilabel">Inactivo</TD><TD CLASS="checkbox"><INPUT TYPE="checkbox" ID="chkdisabled_'.$intUsrId.'" NAME="chkdisabled_'.$intUsrId.'" CLASS="icheck" '.$chkdisabled.' disabled /></TD>';
            echo '</TR>';
            
            echo ( $user["blnFromLdap"] ) ? '<input type="hidden" id="usrldap_'.$intUsrId.'" NAME="ldap_'.$intUsrId.'" value="1" />' : '<input type="hidden" id="usrldap_'.$intUsrId.'" NAME="ldap_'.$intUsrId.'" value="0" />';
            echo '<INPUT TYPE="hidden" ID="usrid_'.$intUsrId.'" NAME="usrid_'.$intUsrId.'" VALUE="'.$intUsrId.'" />';
            
        }
        echo '</TBODY></TABLE></FORM>';
        
        $resQuery->free();
    }

    // Función para mostrar la tabla de nuevo usuario
    public function getNewUserTable(){
        
        $objAccount = new Account;
        $usersgroups = $objAccount->getSecGroups();
        unset($objAccount);
        
        $usersprofiles = array("Acceso total","Editar&Borrar","Editar","Ver&Clave","Ver");
        
        echo '<FORM NAME="frm_tblnewuser" ID="frm_tblnewuser" OnSubmit="return false;" >';
        echo '<TABLE ID="tblNewUser" CLASS="data"><TBODY>';
        
        echo '<TR><TD CLASS="descCampo">Nombre</TD><TD><INPUT TYPE="text" ID="usrname_0" NAME="usrname_0" TITLE="Nombre de usuario" CLASS="txtuser" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">Login</TD><TD><INPUT TYPE="text" ID="usrlogin_0" NAME="usrlogin_0" TITLE="Login de usuario" CLASS="txtloginr" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">Perfil</TD>';
        echo '<TD><SELECT ID="usrprofile_0" NAME="usrprofile_0">';
        
        foreach ($usersprofiles as $profileid => $profiledesc ){
            echo '<OPTION VALUE="'.$profileid.'" '.$profileSELECTed.'>'.$profiledesc.'</OPTION>';
        }
        
        echo '</SELECT></TD></TR>';
        echo '<TR><TD CLASS="descCampo">Grupo</TD>';
        echo '<TD><SELECT ID="usrgroup_0" NAME="usrgroup_0">';
        
        foreach ($usersgroups as $groupname => $groupid){
            echo '<OPTION VALUE="'.$groupid.'" '.$grpSELECTed.'>'.$groupname.'</OPTION>';
        }
        echo '</SELECT></TD></TR>';
        echo '<TR><TD CLASS="descCampo">Admin</TD><TD CLASS="checkbox"><INPUT TYPE="checkbox" ID="chkadmin_0" NAME="chkadmin_0" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">Email</TD><TD><INPUT TYPE="text" ID="usremail_0" NAME="usremail_0" TITLE="Dirección de correo" CLASS="txtemail" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">Notas</TD><TD><INPUT TYPE="text" ID="usrnotes_0" NAME="usrnotes_0" TITLE="Notas" CLASS="txtnotes" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">Clave</TD><TD><INPUT TYPE="password" ID="usrpass_0" NAME="usrpass_0" TITLE="Clave" CLASS="txtpass" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">Clave (Repetir)</TD><TD><INPUT TYPE="password" ID="usrpassv_0" NAME="usrpassv_0" TITLE="Clave (Repetir)" CLASS="txtpassv" /></TD></TR>';
        echo '<INPUT TYPE="hidden" ID="usrid_0" NAME="usrid_0" VALUE="0" />';
        echo '</TBODY></TABLE></FORM>';
    }

    // Función para mostrar la tabla de gestión de grupos
    public function getGroupsTable(){
        $strQuery = "SELECT intUGroupId, vacUGroupName, vacUGroupDesc FROM usergroups ORDER BY vacUGroupName";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
                
        echo '<FORM NAME="frm_tblgroups" ID="frm_tblgroups" OnSubmit="return false;" >';
        echo '<TABLE ID="tblGroups"><THEAD><TR CLASS="header">';
        echo '<TH>Nombre</TH>';
        echo '<TH>Descripción</TH>';
        echo '<TH>Acciones</TH>';
        echo '</TR></THEAD><TBODY>';
        
        //echo "<TBODY>";
        
        while ( $group = $resQuery->fetch_assoc() ){
            $intGrpId = $group["intUGroupId"];
            $groupname = $group["vacUGroupName"];
            $groupdesc = $group["vacUGroupDesc"];

            $lnkEdit = '<TD><INPUT TYPE="image" SRC="imgs/edit.png" TITLE="Editar grupo" CLASS="inputImg" Onclick="return groupMgmt(\'edit\','.$intGrpId.');" /></TD>';
            $lnkDel = '<TD><INPUT TYPE="image" SRC="imgs/delete.png" TITLE="Eliminar grupo" CLASS="inputImg" Onclick="return groupMgmt(\'del\','.$intGrpId.');" /></TD>';
            $lnkSave = '<TD><INPUT TYPE="image" SRC="imgs/check.png" TITLE="Guardar grupo" CLASS="inputImg" Onclick="return groupMgmt(\'save\','.$intGrpId.');" /></TD>';

            $rowclass = ( $rowclass == "row_odd" ) ? "row_even": "row_odd";
        
            echo '<TR CLASS="grprow_'.$intGrpId.' '.$rowclass.'">';
            echo '<TD CLASS="itext"><INPUT TYPE="text" ID="grpname_'.$intGrpId.'" NAME="grpname_'.$intGrpId.'" TITLE="'.$groupname.'" VALUE="'.$groupname.'" CLASS="txtgroup '.$clsdisabled.'" readonly /></LABEL></TD>';
            echo '<TD CLASS="itext"><INPUT TYPE="text" ID="grpdesc_'.$intGrpId.'" NAME="grpdesc_'.$intGrpId.'" TITLE="'.$groupdesc.'" VALUE="'.$groupdesc.'" CLASS="txtdesc '.$clsdisabled.'" readonly /></TD>';
            echo '<TD><TABLE ID="tblActions"><TR>'.$lnkEdit.$lnkSave.$lnkDel.$lnkPass.'</TR></TABLE></TD>';
            echo '</TR>';
            echo '<INPUT TYPE="hidden" ID="grpid_'.$intGrpId.'" NAME="grpid_'.$intGrpId.'" VALUE="'.$intGrpId.'" />';
            
        }
        echo '</TBODY></TABLE></FORM>';
        
        $resQuery->free();
    }

    // Función para mostrar la tabla de nuevo usuario
    public function getNewGroupTable(){
        echo '<FORM NAME="frm_tblnewgroup" ID="frm_tblnewgroup" OnSubmit="return false;" >';
        echo '<TABLE ID="tblNewGroup" CLASS="data"><TBODY>';
        
        echo '<TR><TD CLASS="descCampo">Nombre</TD><TD><INPUT TYPE="text" ID="grpname_0" NAME="grpname_0" TITLE="Nombre del grupo" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">Descripción</TD><TD><INPUT TYPE="text" ID="grpdesc_0" NAME="grpdesc_0" TITLE="Descripción" /></TD></TR>';
        echo '<INPUT TYPE="hidden" ID="grpid_0" NAME="grpid_0" VALUE="0" />';
        echo '</TBODY></TABLE></FORM>';
    }    
    
    // Función para comprobar si un usuario/email existen en la BD
    public function checkUserExist() {
        $strLogin = strtoupper($this->strLogin);
        $strEmail = strtoupper($this->strEmail);
        
        $strQuery = "SELECT vacULogin, vacUEmail FROM users 
                    WHERE (UPPER(vacULogin) = '$strLogin' OR UPPER(vacUEmail) = '$strEmail') 
                    AND intUserId != $this->intUserId ";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        while ( $resResult = $resQuery->fetch_assoc() ){
            $resULogin = strtoupper($resResult["vacULogin"]);
            $resUEmail = strtoupper($resResult["vacUEmail"]);
            
            if ( $resULogin == $strLogin ){
                return 1;
            } elseif ( $resUEmail == $strEmail ){
                return 2;
            }
        }
        
        $resQuery->free();
    }

    // Función para comprobar si un grupo existe en la BBDD
    public function checkGroupExist() {
        $strGroupName = strtoupper($this->strUGroupName);
        
        if ( $this->intUGroupId ){
            $strQuery = "SELECT vacUGroupName FROM usergroups WHERE UPPER(vacUGroupName) = '$strGroupName' AND intUGroupId != $this->intUGroupId";
        } else {
            $strQuery = "SELECT vacUGroupName FROM usergroups WHERE UPPER(vacUGroupName) = '$strGroupName'";
        }
        
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        $resNum = $resQuery->num_rows;
        $resQuery->free();
        
        if ( $resNum == 1 ) return FALSE;
        return TRUE;
    }

    // Función para comprobar si un grupo está en uso
    public function checkGroupInUse() {
        $strQuery = "SELECT count(intUserId) FROM users WHERE intUGroupFid = $this->intUGroupId";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        $resNum = $resQuery->fetch_array(MYSQLI_NUM);
        $resQuery->free();
        
        if ( $resNum[0] >= 1 ) return "Usuarios ($resNum[0])";
        
        $strQuery = "SELECT count(intAccountId) FROM accounts WHERE intUGroupFId = $this->intUGroupId";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        $resNum = $resQuery->fetch_array(MYSQLI_NUM);
        $resQuery->free();
        
        if ( $resNum[0] >= 1 ) return "Cuentas ($resNum[0])";
        
        $strQuery = "SELECT count(id) FROM acc_usergroups WHERE intUGroupId = $this->intUGroupId";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        $resNum = $resQuery->fetch_array(MYSQLI_NUM);
        $resQuery->free();
        
        if ( $resNum[0] >= 1 ) return "Cuentas ($resNum[0])";
        
        return TRUE;
    }   
    
    // Función para comprobar la clave del usuario en MySQL
    public function checkUserPass($strLogin, $strPassword) {
        $strQuery = "SELECT vacULogin, vacUPassword, blnDisabled FROM users WHERE vacULogin = '$strLogin' LIMIT 1";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        if ( $resQuery->num_rows == 0 ) return FALSE;
        
        $resResult = $resQuery->fetch_assoc();
        $resQuery->free();
        
        if ( $resResult["blnDisabled"] == 1 ) return 2;
        
        $this->strPassword = $resResult["vacUPassword"];
        if ( $this->strPassword == md5($strPassword) ) return TRUE;
        
        return FALSE;
    }

    // Función para comprobar si existe el usuario de LDAP en MySQL
    public function checkUserLDAP($strLogin) {
        $strLogin = $this->dbh->real_escape_string($strLogin);
		
        $strQuery = "SELECT vacULogin FROM users WHERE vacULogin = '$strLogin' LIMIT 1";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        if ( $resQuery->num_rows == 0 ) return FALSE;
        
        return TRUE;
    }

    // Función para insertar usuarios de LDAP en MySQL
    public function newUserLDAP() {
        $strQuery = "INSERT INTO users (vacUName, intUGroupFid, vacULogin, vacUPassword, vacUEmail, txtUNotes, 
                    intUProfile, blnFromLdap) VALUES ('".$this->strName."',99,'".$this->strLogin ."',
                    MD5('".$this->strPwd."'),'".$this->strEmail."','LDAP',2,1)";
        $strQuerySafe = "INSERT INTO users (vacUName, intUGroupFid, vacULogin, vacUPassword, vacUEmail, txtUNotes, 
                    intUProfile, blnFromLdap) VALUES ('".$this->strName."',99,'".$this->strLogin ."',
                    MD5('***'),'".$this->strEmail."','LDAP',3,1)";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuerySafe);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        Common::sendEmail("Su cuenta está pendiente de activación.\n\nEn breve recibirá un email de confirmación.", $this->strEmail);
        
        return $resQuery;
    }

    // Función para actualizar los usuarios en MySQL
    public function manageUser($strAction) {
        
        switch ($strAction){
            case "add":
                $strQuery = "INSERT INTO users (vacUName, vacULogin, vacUEmail, txtUNotes, intUGroupFid, 
                            intUProfile, blnIsAdmin, vacUPassword, blnFromLdap) 
                            VALUES ('".$this->strName."','".$this->strLogin."','".$this->strEmail."',
                            '".$this->strNotes."',".$this->intGroupId.",".$this->intProfile.",".$this->blnAdmin.",
                            MD5('".$this->strPwd."'),0)";
                break;
            case "update":
                $strQuery = "UPDATE users SET vacUName = '".$this->strName."', vacULogin = '".$this->strLogin."', 
                            vacUEmail = '".$this->strEmail."', txtUNotes = '".$this->strNotes."', 
                            intUGroupFid = ".$this->intGroupId.", intUProfile = ".$this->intProfile.", 
                            blnIsAdmin = ".$this->blnAdmin.", blnDisabled = ".$this->blnDisabled.", datULastUpdate = NOW() 
                            WHERE intUserId = ".$this->intUserId;
                break;
            case "updatepass":
                $strQuery = "UPDATE users SET vacUPassword = MD5('".$this->strPwd."') WHERE intUserId = ".$this->intUserId;
                break;
            case "updateldap":
                $strQuery = "UPDATE users SET vacUPassword = MD5('".$this->strPwd . "') WHERE intUserId = ".$this->arrUserInfo['intUserId'];
                break;
            case "delete":
                $strQuery = "DELETE FROM users WHERE intUserId = ".$this->intUserId." LIMIT 1";
                break;
                
        }
        
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        return $resQuery;
    }

    // Función para la gestión de grupos de usuarios
    public function manageGroup($strAction) {
                
        switch ($strAction){
            case "add":
                $strQuery = "INSERT INTO usergroups (vacUGroupName, vacUGroupDesc) VALUES ('".$this->strUGroupName."','".$this->strUGroupDesc."')";
                break;
            case "update":
                $strQuery = "UPDATE usergroups SET vacUGroupName = '".$this->strUGroupName."', vacUGroupDesc = '".$this->strUGroupDesc."' WHERE intUGroupId = ".$this->intUGroupId;
                break;
            case "delete":
                $strQuery = "DELETE FROM usergroups WHERE intUGroupId = ".$this->intUGroupId." LIMIT 1";
                break;
                
        }
        
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        return $resQuery;
    }
    
    // Función para actualizar la clave de usuarios de LDAP en MySQL
    public function updateUserLDAP() {
        $strQuery = "UPDATE users SET vacUPassword = MD5('".$this->strPwd."'), datULastUpdate = NOW() WHERE intUserId = ".$this->arrUserInfo['intUserId'];
        $strQuerySafe = "UPDATE users SET vacUPassword = MD5('***'), datULastUpdate = NOW() WHERE intUserId = ".$this->arrUserInfo['intUserId'];
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuerySafe);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        return $resQuery;
    }

    // Función para establecer variables de sesión
    public function setUserSession() {
        $_SESSION['ulogin'] = $this->arrUserInfo['vacULogin'];
        $_SESSION['uprofile'] = $this->arrUserInfo['intUProfile'];
        $_SESSION['uname'] = $this->arrUserInfo['vacUName'];
        $_SESSION['ugroup'] = $this->arrUserInfo['intUGroupFid'];
        $_SESSION['ugroupn'] = $this->arrUserInfo['vacUGroupName'];
        $_SESSION['uid'] = $this->arrUserInfo['intUserId'];
        $_SESSION['uemail'] = $this->arrUserInfo['vacUEmail'];
        $_SESSION['uisadmin'] = $this->arrUserInfo['blnIsAdmin'];
        $_SESSION['uisldap'] = $this->arrUserInfo['blnFromLdap'];

        $this->serUserLastLogin();
    }

    // Función para establecer el último inicio de sesión del usuario
    private function serUserLastLogin() {
        $strQuery = "UPDATE users SET datULastLogin = NOW() WHERE intUserId = " . $this->arrUserInfo['intUserId'];
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
    }

    // Función para autentificación con LDAP
    public function authUserLDAP($strUser, $strPass) {
        global $CFG_LDAP;

        if ($CFG_LDAP["ldap"] == 0) return FALSE;
        
        foreach ( $CFG_LDAP as $configVal ){
            if ( ! is_array($configVal) ){
                if ( $configVal == "" ) return FALSE;
            }
        }
        
        $ldapAccess = FALSE;
        
        // Base del LDAP
        $ldapDn = $CFG_LDAP["ldapbase"];

        // Conexión al servidor LDAP
        $ldapConn = ldap_connect($CFG_LDAP["ldapserver"]);
        $userCN = "cn=$strUser,$ldapDn";

        // Establecemos el timeout en 10 seg.
        @ldap_set_option($ldapConn, LDAP_OPT_NETWORK_TIMEOUT, 10);

        // Comprobamos que la conexión se realiza
        if ( @ldap_bind($ldapConn, $userCN, $strPass) ) {
            $filter = "(&(cn=$strUser)(objectCLASS=inetOrgPerson))";
            $filterAttr = $CFG_LDAP["ldapuserattr"];
            $searchRes = ldap_search($ldapConn, $ldapDn, $filter, $filterAttr) or die("ERROR: no es posible realizar la búsqueda en LDAP");
            $searchEntries = ldap_get_entries($ldapConn, $searchRes);
            ldap_unbind($ldapConn);

            foreach ($searchEntries as $entry => $entryValue) {
                if (is_array($entryValue)) {
                    foreach ($entryValue as $entryAttr => $attrValue) {
                        if (is_array($attrValue)) {
                            switch ($entryAttr) {
                                case "groupmembership":
                                    foreach ($attrValue as $group) {
                                        // Comprobamos que el usuario está en el grupo indicado
                                        if ( $group == $CFG_LDAP["ldapgroup"] ){
                                            $this->strLogin = $strUser;
                                            $this->strPwd = $strPass;
                                            $ldapAccess = TRUE;
                                            break;
                                        }
                                    }
                                    break;
                                case "fullname":
                                    $this->strName = $attrValue[0];
                                    break;
                                case "mail":
                                    $this->strEmail = $attrValue[0];
                                    break;
                            }
                        }
                    }
                }
            }
        } 
        return $ldapAccess;
    }

    // Función para comprobar si el usuario utiliza clave maestra atual
    public function checkUserMPass($strUserRealPass) {
        $userMPass = $this->getUserMPass($strUserRealPass, TRUE);
        
        if ( $userMPass == FALSE ) return FALSE;

        $strQuery = "SELECT vacValue FROM config WHERE vacParameter = 'masterPwd' ";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resResult = $resQuery->fetch_assoc();
        $resQuery->free();

        // Comprobamos el hash de la clave del usuario con la guardada
        $objCrypt = new Crypt;
        $checkUserMPass = $objCrypt->checkHashPass($userMPass, $resResult["vacValue"]);

        return $checkUserMPass;
    }

    // Función para actualizar la clave maestra para un usuario
    public function updateUserMPass($masterPwd, $strUserRealPass) {
        $strQuery = "SELECT vacValue FROM config WHERE vacParameter = 'masterPwd' ";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resResult = $resQuery->fetch_assoc();
        $resQuery->free();

        $objCrypt = new Crypt;

        if ( $objCrypt->checkHashPass($masterPwd, $resResult["vacValue"]) ) {
            $strUserMPwd = $objCrypt->mkUserMPassEncrypt($strUserRealPass, $masterPwd);

            if ( ! $strUserMPwd ) return FALSE;
        } else { return FALSE; }

        $strQuery = "UPDATE users SET vacUserMPwd = '$strUserMPwd[0]', vacUserMIv = '$strUserMPwd[1]', datUserLastUpdateMPass = NOW()  WHERE intUserId = " . $this->arrUserInfo['intUserId'];
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return TRUE;
    }

    // Función para desencriptar la clave maestra para la sesión
    public function getUserMPass($strUserRealPass, $showPass = FALSE) {
        $strQuery = "SELECT vacUserMPwd, vacUserMIv  FROM users WHERE intUserId = " . $this->arrUserInfo['intUserId'];
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resResult = $resQuery->fetch_assoc(); 
        $resQuery->free();
        
        $userMPwd = $resResult["vacUserMPwd"];
        $userMIv = $resResult["vacUserMIv"];
        
        if ( $resResult["vacUserMPwd"] && $resResult["vacUserMIv"] ) {
            $objCrypt = new Crypt;
            $strClearMPwd = $objCrypt->decrypt($userMPwd, $strUserRealPass, $userMIv);

            if ( ! $strClearMPwd ) return FALSE;

            if ( $showPass == TRUE ) {
                return $strClearMPwd;
            } else {
                $_SESSION['mPass'] = $strClearMPwd;
                return TRUE;
            }
        }
        return FALSE;
    }
    
    // Función para comprobar los permisos de acceso del usuario
    public static function checkUserAccess($strAction,$intUid = 0){
        $userProfileId = $_SESSION["uprofile"];
        $blnUIsAdmin = $_SESSION["uisadmin"];
        
        switch ($strAction){
            case ( $strAction == "backup" || $strAction == "users" || $strAction == "config" || $strAction == "logview"):
                if ( $blnUIsAdmin == 1 AND $userProfileId <= 1 ) return TRUE;
                break;
            case ( $strAction == "chpass" ):
                if ( ($blnUIsAdmin == 1 AND $userProfileId <= 1) OR $_SESSION["uid"] == $intUid ) return TRUE;
                break;                
            default :
                Common::wrLogInfo("Check Acceso", "Denegado acceso a '".$strAction."'");
                return FALSE;
        }
    }    
}
?>