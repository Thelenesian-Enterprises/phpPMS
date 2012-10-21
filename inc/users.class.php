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
    var $blnAdminApp;
    var $blnAdminAcc;
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

    // Método para obtener los datos del usuario en MySQL
    public function getUserInfo($strLogin) {
        $strQuery = "SELECT intUserId, vacUName, intUGroupFid, vacULogin, vacUEmail, txtUNotes, 
                    intUCount, intUProfile, vacUGroupName, blnIsAdminApp, blnIsAdminAcc, blnFromLdap, blnDisabled 
                    FROM users LEFT JOIN usergroups ON users.intUGroupFid=usergroups.intUGroupId 
                    WHERE vacULogin = '".$this->dbh->real_escape_string($strLogin)."' LIMIT 1";
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
    
    // Método para mostrar la tabla de gestión de usuarios
    public function getUsersTable(){
        global $LANG;
        $rowclass = "";
        
        $strQuery = "SELECT intUserId, vacUName, intUGroupFid, vacULogin, vacUEmail, txtUNotes, intUProfile, 
                    blnIsAdminApp, blnIsAdminAcc, blnFromLdap, blnDisabled FROM users ORDER BY vacUName";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $objAccount = new Account;
        $usersgroups = $objAccount->getSecGroups();
        unset($objAccount);
        
        $usersprofiles = array($LANG['users'][11],$LANG['users'][12],$LANG['users'][13],$LANG['users'][14],$LANG['users'][15]);
        
        echo '<FORM NAME="frm_tblusers" ID="frm_tblusers" OnSubmit="return false;" >';
        echo '<TABLE ID="tblUsers"><THEAD><TR CLASS="headerGrey">';
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
                   
            $chkadminapp = ( $user["blnIsAdminApp"] ) ? 'checked="checked"' : '';
            $chkadminacc = ( $user["blnIsAdminAcc"] ) ? 'checked="checked"' : '';
            $chkdisabled = ( $user["blnDisabled"] ) ? 'checked="checked"' : '';
            
            if ( $user["blnFromLdap"] ){
                $chkldap = 'checked="checked"';
                $clsdisabled = 'txtdisabled';
                $lnkPass = '';
            } else {
                $chkldap = '';
                $clsdisabled = '';
                $lnkPass = '<TD><INPUT TYPE="image" SRC="imgs/key.png" TITLE="'.$LANG['buttons'][32].'" CLASS="inputImg" Onclick="return usrUpdPass('.$intUsrId.',\''.$userlogin.'\');" /></TD>';
            }

            $lnkEdit = '<TD><INPUT TYPE="image" SRC="imgs/edit.png" TITLE="'.$LANG['buttons'][29].'" CLASS="inputImg" Onclick="return userMgmt(\'edit\','.$intUsrId.');" /></TD>';
            $lnkDel = '<TD><INPUT TYPE="image" SRC="imgs/delete.png" TITLE="'.$LANG['buttons'][31].'" CLASS="inputImg" Onclick="return userMgmt(\'del\','.$intUsrId.');" /></TD>';
            $lnkSave = '<TD><INPUT TYPE="image" SRC="imgs/check.png" TITLE="'.$LANG['buttons'][30].'" CLASS="inputImg" Onclick="return userMgmt(\'save\','.$intUsrId.');" /></TD>';

            $rowclass = ( $rowclass == "row_odd" ) ? "row_even" : "row_odd";

            echo '<TR CLASS="usr_odd usrrow_'.$intUsrId.' '.$rowclass.'">';
            echo '<TD CLASS="ilabel">'.$LANG['users'][1].'</TD><TD CLASS="itext"><INPUT TYPE="text" ID="usrname_'.$intUsrId.'" NAME="usrname_'.$intUsrId.'" TITLE="'.$username.'" VALUE="'.$username.'" CLASS="txtuser '.$clsdisabled.'" readonly /></LABEL></TD>';
            echo '<TD CLASS="ilabel">'.$LANG['users'][3].'</TD><TD CLASS="itext"><INPUT TYPE="text" ID="usrlogin_'.$intUsrId.'" NAME="usrlogin_'.$intUsrId.'" TITLE="'.$userlogin.'" VALUE="'.$userlogin.'" CLASS="txtlogin '.$clsdisabled.'" readonly /></TD>';
            echo '<TD CLASS="ilabel">'.$LANG['users'][5].'</TD><TD CLASS="itext"><SELECT id="usrprofile_'.$intUsrId.'" NAME="usrprofile_'.$intUsrId.'" CLASS="'.$clsdisabled.'" disabled>';
            foreach ($usersprofiles as $profileid => $profiledesc ){
                ( $profileid == $userprofile ) ? $profileselected = "selected": $profileselected = "";
                echo '<OPTION VALUE="'.$profileid.'" '.$profileselected.'>'.$profiledesc.'</OPTION>';
            }
            echo '</SELECT></TD>';
            echo '<TD CLASS="ilabel">'.$LANG['users'][7].'</TD><TD CLASS="checkbox"><INPUT TYPE="checkbox" ID="chkadminapp_'.$intUsrId.'" NAME="chkadminapp_'.$intUsrId.'" CLASS="icheck" '.$chkadminapp.' disabled /></TD>';
            echo '<TD CLASS="ilabel"></TD><TD CLASS="checkbox"></TD>';
            echo '<TD ROWSPAN="2"><TABLE ID="tblActions"><TR>'.$lnkEdit.$lnkSave.$lnkDel.$lnkPass.'</TR></TABLE></TD>';
            echo '</TR>';
            echo '<TR CLASS="usr_even usrrow_'.$intUsrId.' '.$rowclass.'">';
            echo '<TD CLASS="ilabel">'.$LANG['users'][2].'</TD><TD CLASS="itext"><INPUT TYPE="text" ID="usremail_'.$intUsrId.'" NAME="usremail_'.$intUsrId.'" TITLE="'.$useremail.'" VALUE="'.$useremail.'" CLASS="txtemail '.$clsdisabled.'" readonly/></LABEL></TD>';
            echo '<TD CLASS="ilabel">'.$LANG['users'][4].'</TD><TD CLASS="itext"><INPUT TYPE="text" id="usrnotes_'.$intUsrId.'" NAME="usrnotes_'.$intUsrId.'" TITLE="'.$usernotes.'" VALUE="'.$usernotes.'" CLASS="txtnotes '.$clsdisabled.'" readonly /></TD>';
            echo '<TD CLASS="ilabel">'.$LANG['users'][6].'</TD><TD CLASS="itext"><SELECT ID="usrgroup_'.$intUsrId.'" NAME="usrgroup_'.$intUsrId.'" CLASS="'.$clsdisabled.'" disabled>';
            foreach ($usersgroups as $groupname => $groupid){
                ( $groupid == $usergroup ) ? $grpselected = "selected": $grpselected = "";
                echo '<OPTION VALUE="'.$groupid.'" '.$grpselected.'>'.$groupname.'</OPTION>';
            }
            echo '</SELECT></TD>';
            echo '<TD CLASS="ilabel">'.$LANG['users'][20].'</TD><TD CLASS="checkbox"><INPUT TYPE="checkbox" ID="chkadminacc_'.$intUsrId.'" NAME="chkadminacc_'.$intUsrId.'" CLASS="icheck" '.$chkadminacc.' disabled /></TD>';
            echo '<TD CLASS="ilabel">'.$LANG['users'][8].'</TD><TD CLASS="checkbox"><INPUT TYPE="checkbox" ID="chkdisabled_'.$intUsrId.'" NAME="chkdisabled_'.$intUsrId.'" CLASS="icheck" '.$chkdisabled.' disabled /></TD>';
            echo '</TR>';
            
            echo ( $user["blnFromLdap"] ) ? '<input type="hidden" id="usrldap_'.$intUsrId.'" NAME="ldap_'.$intUsrId.'" value="1" />' : '<input type="hidden" id="usrldap_'.$intUsrId.'" NAME="ldap_'.$intUsrId.'" value="0" />';
            echo '<INPUT TYPE="hidden" ID="usrid_'.$intUsrId.'" NAME="usrid_'.$intUsrId.'" VALUE="'.$intUsrId.'" />';
            
        }
        echo '</TBODY></TABLE></FORM>';
        
        $resQuery->free();
    }

    // Método para mostrar la tabla de nuevo usuario
    public function getNewUserTable(){
        global $LANG;        

        $objAccount = new Account;
        $usersgroups = $objAccount->getSecGroups();
        unset($objAccount);
        
        $usersprofiles = array($LANG['users'][11],$LANG['users'][12],$LANG['users'][13],$LANG['users'][14],$LANG['users'][15]);
        
        echo '<FORM NAME="frm_tblnewuser" ID="frm_tblnewuser" OnSubmit="return false;" >';
        echo '<TABLE ID="tblNewUser" CLASS="data"><TBODY>';
        
        echo '<TR><TD CLASS="descCampo">'.$LANG['users'][1].'</TD><TD><INPUT TYPE="text" ID="usrname_0" NAME="usrname_0" TITLE="'.$LANG['users'][16].'" CLASS="txtuser" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">'.$LANG['users'][3].'</TD><TD><INPUT TYPE="text" ID="usrlogin_0" NAME="usrlogin_0" TITLE="'.$LANG['users'][17].'" CLASS="txtloginr" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">'.$LANG['users'][5].'</TD>';
        echo '<TD><SELECT ID="usrprofile_0" NAME="usrprofile_0">';
        
        foreach ($usersprofiles as $profileid => $profiledesc ){
            echo '<OPTION VALUE="'.$profileid.'" '.$profileSELECTed.'>'.$profiledesc.'</OPTION>';
        }
        
        echo '</SELECT></TD></TR>';
        echo '<TR><TD CLASS="descCampo">'.$LANG['users'][6].'</TD>';
        echo '<TD><SELECT ID="usrgroup_0" NAME="usrgroup_0">';
        
        foreach ($usersgroups as $groupname => $groupid){
            echo '<OPTION VALUE="'.$groupid.'" '.$grpSELECTed.'>'.$groupname.'</OPTION>';
        }
        echo '</SELECT></TD></TR>';
        echo '<TR><TD CLASS="descCampo">'.$LANG['users'][7].'</TD><TD CLASS="checkbox"><INPUT TYPE="checkbox" ID="chkadmin_0" NAME="chkadmin_0" TITLE="'.$LANG['users'][18].'" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">'.$LANG['users'][2].'</TD><TD><INPUT TYPE="text" ID="usremail_0" NAME="usremail_0" TITLE="'.$LANG['users'][19].'" CLASS="txtemail" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">'.$LANG['users'][4].'</TD><TD><INPUT TYPE="text" ID="usrnotes_0" NAME="usrnotes_0" CLASS="txtnotes" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">'.$LANG['users'][9].'</TD><TD><INPUT TYPE="password" ID="usrpass_0" NAME="usrpass_0" CLASS="txtpass" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">'.$LANG['users'][10].'</TD><TD><INPUT TYPE="password" ID="usrpassv_0" NAME="usrpassv_0" CLASS="txtpassv" /></TD></TR>';
        echo '<INPUT TYPE="hidden" ID="usrid_0" NAME="usrid_0" VALUE="0" />';
        echo '</TBODY></TABLE></FORM>';
    }

    // Método para mostrar la tabla de gestión de grupos
    public function getGroupsTable(){
        global $LANG;
        $rowclass = "";
        $clsdisabled = "";
        
        $strQuery = "SELECT intUGroupId, vacUGroupName, vacUGroupDesc FROM usergroups ORDER BY vacUGroupName";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
                
        echo '<FORM NAME="frm_tblgroups" ID="frm_tblgroups" OnSubmit="return false;" >';
        echo '<TABLE ID="tblGroups"><THEAD><TR CLASS="headerGrey">';
        echo '<TH>'.$LANG['groups'][0].'</TH>';
        echo '<TH>'.$LANG['groups'][1].'</TH>';
        echo '<TH>'.$LANG['groups'][2].'</TH>';
        echo '</TR></THEAD><TBODY>';
        
        //echo "<TBODY>";
        
        while ( $group = $resQuery->fetch_assoc() ){
            $intGrpId = $group["intUGroupId"];
            $groupname = $group["vacUGroupName"];
            $groupdesc = $group["vacUGroupDesc"];

            $lnkEdit = '<TD><INPUT TYPE="image" SRC="imgs/edit.png" TITLE="'.$LANG['buttons'][34].'" CLASS="inputImg" Onclick="return groupMgmt(\'edit\','.$intGrpId.');" /></TD>';
            $lnkDel = '<TD><INPUT TYPE="image" SRC="imgs/delete.png" TITLE="'.$LANG['buttons'][36].'" CLASS="inputImg" Onclick="return groupMgmt(\'del\','.$intGrpId.');" /></TD>';
            $lnkSave = '<TD><INPUT TYPE="image" SRC="imgs/check.png" TITLE="'.$LANG['buttons'][35].'" CLASS="inputImg" Onclick="return groupMgmt(\'save\','.$intGrpId.');" /></TD>';

            $rowclass = ( $rowclass == "row_odd" ) ? "row_even": "row_odd";
        
            echo '<TR CLASS="grprow_'.$intGrpId.' '.$rowclass.'">';
            echo '<TD CLASS="itext"><INPUT TYPE="text" ID="grpname_'.$intGrpId.'" NAME="grpname_'.$intGrpId.'" TITLE="'.$groupname.'" VALUE="'.$groupname.'" CLASS="txtgroup '.$clsdisabled.'" readonly /></LABEL></TD>';
            echo '<TD CLASS="itext"><INPUT TYPE="text" ID="grpdesc_'.$intGrpId.'" NAME="grpdesc_'.$intGrpId.'" TITLE="'.$groupdesc.'" VALUE="'.$groupdesc.'" CLASS="txtdesc '.$clsdisabled.'" readonly /></TD>';
            echo '<TD><TABLE ID="tblActions"><TR>'.$lnkEdit.$lnkSave.$lnkDel.'</TR></TABLE></TD>';
            echo '</TR>';
            echo '<INPUT TYPE="hidden" ID="grpid_'.$intGrpId.'" NAME="grpid_'.$intGrpId.'" VALUE="'.$intGrpId.'" />';
            
        }
        echo '</TBODY></TABLE></FORM>';
        
        $resQuery->free();
    }

    // Método para mostrar la tabla de nuevo usuario
    public function getNewGroupTable(){
        global $LANG;
        
        echo '<FORM NAME="frm_tblnewgroup" ID="frm_tblnewgroup" OnSubmit="return false;" >';
        echo '<TABLE ID="tblNewGroup" CLASS="data"><TBODY>';
        
        echo '<TR><TD CLASS="descCampo">'.$LANG['groups'][0].'</TD><TD><INPUT TYPE="text" ID="grpname_0" NAME="grpname_0" TITLE="'.$LANG['groups'][3].'" /></TD></TR>';
        echo '<TR><TD CLASS="descCampo">'.$LANG['groups'][1].'</TD><TD><INPUT TYPE="text" ID="grpdesc_0" NAME="grpdesc_0" TITLE="'.$LANG['groups'][4].'" /></TD></TR>';
        echo '<INPUT TYPE="hidden" ID="grpid_0" NAME="grpid_0" VALUE="0" />';
        echo '</TBODY></TABLE></FORM>';
    }    
    
    // Método para comprobar si un usuario/email existen en la BD
    public function checkUserExist() {
        $strLogin = strtoupper($this->strLogin);
        $strEmail = strtoupper($this->strEmail);
        
        $strQuery = "SELECT vacULogin, vacUEmail FROM users 
                    WHERE (UPPER(vacULogin) = '".$this->dbh->real_escape_string($strLogin)."' 
                    OR UPPER(vacUEmail) = '".$this->dbh->real_escape_string($strEmail)."') 
                    AND intUserId != ".(int)$this->intUserId;
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

    // Método para comprobar si un grupo existe en la BBDD
    public function checkGroupExist() {
        $strGroupName = strtoupper($this->strUGroupName);
        
        if ( $this->intUGroupId ){
            $strQuery = "SELECT vacUGroupName FROM usergroups 
                        WHERE UPPER(vacUGroupName) = '".$this->dbh->real_escape_string($strGroupName)."' 
                        AND intUGroupId != ".(int)$this->intUGroupId;
        } else {
            $strQuery = "SELECT vacUGroupName FROM usergroups 
                        WHERE UPPER(vacUGroupName) = '".$this->dbh->real_escape_string($strGroupName)."'";
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

    // Método para comprobar si un grupo está en uso
    public function checkGroupInUse() {
        $strQuery = "SELECT count(intUserId) FROM users WHERE intUGroupFid = ".(int)$this->intUGroupId;
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        $resNum = $resQuery->fetch_array(MYSQLI_NUM);
        $resQuery->free();
        
        if ( $resNum[0] >= 1 ) return "Usuarios ($resNum[0])";
        
        $strQuery = "SELECT count(intAccountId) FROM accounts WHERE intUGroupFId = ".(int)$this->intUGroupId;
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        $resNum = $resQuery->fetch_array(MYSQLI_NUM);
        $resQuery->free();
        
        if ( $resNum[0] >= 1 ) return "Cuentas ($resNum[0])";
        
        $strQuery = "SELECT count(id) FROM acc_usergroups WHERE intUGroupId = ".(int)$this->intUGroupId;
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
    
    // Método para comprobar la clave del usuario en MySQL
    public function checkUserPass($strLogin, $strPassword) {
        $strQuery = "SELECT vacULogin, vacUPassword, blnDisabled FROM users 
                    WHERE vacULogin = '".$this->dbh->real_escape_string($strLogin)."' LIMIT 1";
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

    // Método para comprobar si existe el usuario de LDAP en MySQL
    public function checkUserLDAP($strLogin) {
        $strLogin = $this->dbh->real_escape_string($strLogin);
		
        $strQuery = "SELECT vacULogin FROM users 
                    WHERE vacULogin = '".$this->dbh->real_escape_string($strLogin)."' LIMIT 1";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        if ( $resQuery->num_rows == 0 ) return FALSE;
        
        return TRUE;
    }

    // Método para insertar usuarios de LDAP en MySQL
    public function newUserLDAP() {
        $strQuery = "INSERT INTO users (vacUName, intUGroupFid, vacULogin, vacUPassword, vacUEmail, txtUNotes, 
                    intUProfile, blnFromLdap) 
                    VALUES ('".$this->dbh->real_escape_string($this->strName)."',99,
                    '".$this->dbh->real_escape_string($this->strLogin)."',
                    MD5('".$this->strPwd."'),
                    '".$this->dbh->real_escape_string($this->strEmail)."','LDAP',2,1)";
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

    // Método para actualizar los usuarios en MySQL
    public function manageUser($strAction) {
        
        switch ($strAction){
            case "add":
                $strQuery = "INSERT INTO users (vacUName, vacULogin, vacUEmail, txtUNotes, intUGroupFid, 
                            intUProfile, blnIsAdminApp, blnIsAdminAcc, vacUPassword, blnFromLdap) 
                            VALUES ('".$this->dbh->real_escape_string($this->strName)."',
                            '".$this->dbh->real_escape_string($this->strLogin)."',
                            '".$this->dbh->real_escape_string($this->strEmail)."',
                            '".$this->dbh->real_escape_string($this->strNotes)."',
                            ".(int)$this->intGroupId.",
                            ".(int)$this->intProfile.",
                            ".(int)$this->blnAdminApp.",
                            ".(int)$this->blnAdminAcc.",
                            MD5('".$this->dbh->real_escape_string($this->strPwd)."'),0)";
                break;
            case "update":
                $strQuery = "UPDATE users SET vacUName = '".$this->dbh->real_escape_string($this->strName)."',
                            vacULogin = '".$this->dbh->real_escape_string($this->strLogin)."',
                            vacUEmail = '".$this->dbh->real_escape_string($this->strEmail)."',
                            txtUNotes = '".$this->dbh->real_escape_string($this->strNotes)."',
                            intUGroupFid = ".(int)$this->intGroupId.",
                            intUProfile = ".(int)$this->intProfile.",
                            blnIsAdminApp = ".(int)$this->blnAdminApp.", 
                            blnIsAdminAcc = ".(int)$this->blnAdminAcc.", 
                            blnDisabled = ".(int)$this->blnDisabled.",
                            datULastUpdate = NOW() WHERE intUserId = ".(int)$this->intUserId;
                break;
            case "updatepass":
                $strQuery = "UPDATE users SET vacUPassword = MD5('".$this->dbh->real_escape_string($this->strPwd)."') 
                            WHERE intUserId = ".(int)$this->intUserId;
                break;
            case "updateldap":
                $strQuery = "UPDATE users SET vacUPassword = MD5('".$this->dbh->real_escape_string($this->strPwd). "') 
                            WHERE intUserId = ".(int)$this->arrUserInfo['intUserId'];
                break;
            case "delete":
                $strQuery = "DELETE FROM users WHERE intUserId = ".(int)$this->intUserId." LIMIT 1";
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

    // Método para la gestión de grupos de usuarios
    public function manageGroup($strAction) {
                
        switch ($strAction){
            case "add":
                $strQuery = "INSERT INTO usergroups (vacUGroupName, vacUGroupDesc) 
                            VALUES ('".$this->dbh->real_escape_string($this->strUGroupName)."',
                            '".$this->dbh->real_escape_string($this->strUGroupDesc)."')";
                break;
            case "update":
                $strQuery = "UPDATE usergroups SET 
                            vacUGroupName = '".$this->dbh->real_escape_string($this->strUGroupName)."',
                            vacUGroupDesc = '".$this->strUGroupDesc."' 
                            WHERE intUGroupId = ".(int)$this->intUGroupId;
                break;
            case "delete":
                $strQuery = "DELETE FROM usergroups WHERE intUGroupId = ".(int)$this->intUGroupId." LIMIT 1";
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
    
    // Método para actualizar la clave de usuarios de LDAP en MySQL
    public function updateUserLDAP() {
        $strQuery = "UPDATE users SET vacUPassword = MD5('".$this->strPwd."'), 
                    datULastUpdate = NOW() WHERE intUserId = ".(int)$this->arrUserInfo['intUserId'];
        $strQuerySafe = "UPDATE users SET vacUPassword = MD5('***'), datULastUpdate = NOW() 
                        WHERE intUserId = ".(int)$this->arrUserInfo['intUserId'];
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuerySafe);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }

        return $resQuery;
    }

    // Método para establecer variables de sesión
    public function setUserSession() {
        $_SESSION['ulogin'] = $this->arrUserInfo['vacULogin'];
        $_SESSION['uprofile'] = $this->arrUserInfo['intUProfile'];
        $_SESSION['uname'] = $this->arrUserInfo['vacUName'];
        $_SESSION['ugroup'] = $this->arrUserInfo['intUGroupFid'];
        $_SESSION['ugroupn'] = $this->arrUserInfo['vacUGroupName'];
        $_SESSION['uid'] = $this->arrUserInfo['intUserId'];
        $_SESSION['uemail'] = $this->arrUserInfo['vacUEmail'];
        $_SESSION['uisadminapp'] = $this->arrUserInfo['blnIsAdminApp'];
        $_SESSION['uisadminacc'] = $this->arrUserInfo['blnIsAdminAcc'];
        $_SESSION['uisldap'] = $this->arrUserInfo['blnFromLdap'];

        $this->serUserLastLogin();
    }

    // Método para establecer el último inicio de sesión del usuario
    private function serUserLastLogin() {
        $strQuery = "UPDATE users SET datULastLogin = NOW() WHERE intUserId = ".(int)$this->arrUserInfo['intUserId'];
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
    }

    // Método para autentificación con LDAP
    public function authUserLDAP($strUser, $strPass) {
        global $CFG_PMS, $LANG;

        if ( $CFG_PMS["ldapenabled"] == 0 || ! in_array("ldap", get_loaded_extensions()) ) return FALSE;
        
        if ( ! $CFG_PMS["ldapbase"] OR ! $CFG_PMS["ldapserver"] OR ! $CFG_PMS["ldapuserattr"] OR ! $CFG_PMS["ldapgroup"] ){
            return FALSE;
        }
        
        $ldapAccess = FALSE;
        
        // Base del LDAP
        $ldapDn = $CFG_PMS["ldapbase"];

        // Conexión al servidor LDAP
        $ldapConn = ldap_connect($CFG_PMS["ldapserver"]);
        $userCN = "cn=$strUser,$ldapDn";

        // Establecemos el timeout en 10 seg.
        @ldap_set_option($ldapConn, LDAP_OPT_NETWORK_TIMEOUT, 10);
        
        // Versión LDAP
        @ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);

        // Comprobamos que la conexión se realiza
        if ( @ldap_bind($ldapConn, $userCN, $strPass) ) {
            $filter = "(&(cn=$strUser)(objectClass=inetOrgPerson))";
            $filterAttr = $CFG_PMS["ldapuserattr"];
            $searchRes = ldap_search($ldapConn, $ldapDn, $filter, $filterAttr) or die($LANG['msg'][98]);
            $searchEntries = ldap_get_entries($ldapConn, $searchRes);
            ldap_unbind($ldapConn);

            foreach ( $searchEntries as $entry => $entryValue ) {
                if ( is_array($entryValue) ) {
                    foreach ( $entryValue as $entryAttr => $attrValue ) {
                        if ( is_array($attrValue) ) {
                            switch ($entryAttr) {
                                case "groupmembership":
                                    foreach ( $attrValue as $group ) {
                                        // Comprobamos que el usuario está en el grupo indicado
                                        if ( $group == $CFG_PMS["ldapgroup"] ){
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

    // Método para comprobar si el usuario tiene guardada la clave maestra actual (login)
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
    
    // Método para comprobar si el usuario tiene actualizada la clave maestra actual
    public function checkUserUpdateMPass() {
        $intUserId = $_SESSION["uid"];
        
        $strQuery = "SELECT vacValue FROM config WHERE vacParameter = 'lastupdatempass' ";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resResult = $resQuery->fetch_array(MYSQLI_NUM);
        $appLastUpdateMPass = $resResult[0];

        $strQuery = "SELECT datUserLastUpdateMPass FROM users WHERE intUserId = $intUserId ";
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resResult = $resQuery->fetch_array(MYSQLI_NUM);
        $userLastUpdateMPass = $resResult[0];
        
        if ( $appLastUpdateMPass > $userLastUpdateMPass ){
            return FALSE;
        }
        
        return TRUE;
    }    

    // Método para actualizar la clave maestra para un usuario
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

        $strQuery = "UPDATE users SET vacUserMPwd = '$strUserMPwd[0]', vacUserMIv = '$strUserMPwd[1]', 
                    datUserLastUpdateMPass = UNIX_TIMESTAMP() WHERE intUserId = ".(int)$this->arrUserInfo['intUserId'];
        $resQuery = $this->dbh->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        return TRUE;
    }

    // Método para desencriptar la clave maestra para la sesión
    public function getUserMPass($strUserRealPass, $showPass = FALSE) {
        $strQuery = "SELECT vacUserMPwd, vacUserMIv  FROM users 
                    WHERE intUserId = ".(int)$this->arrUserInfo['intUserId'];
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
    
    // Método para comprobar los permisos de acceso del usuario
    public static function checkUserAccess($strAction, $intUid = 0){
        global $LANG;
        
        $userProfileId = $_SESSION["uprofile"];
        $blnUIsAdminApp = $_SESSION["uisadminapp"];
        
        switch ($strAction){
            case ( $strAction == "backup" || $strAction == "users" || $strAction == "config" || $strAction == "logview"):
                if ( $blnUIsAdminApp && $userProfileId <= 1 ) return TRUE;
                break;
            case ( $strAction == "chpass" ):
                if ( ($blnUIsAdminApp && $userProfileId <= 1) || $_SESSION["uid"] == $intUid ) return TRUE;
                break;                
        }
        
        Common::wrLogInfo($LANG['event'][23], $LANG['eventdesc'][18]." '".$strAction."'");
        return FALSE;
    }    
}
?>