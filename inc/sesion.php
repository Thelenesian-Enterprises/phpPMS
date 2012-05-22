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
 * Session
 *
 * @author nuxsmin
 * @version 0.9b
 * @link http://cygnux.org/phppms
 * 
 */

function check_session($isAjax = FALSE, $isLogin = FALSE){
    global $CFG_PMS;
    
    session_start();
    
    if ( $isLogin == TRUE ) return;

    if( ! isset($_SESSION["ulogin"]) ) {
        //Common::wrLogInfo("Timeout sesión", "Nombre:".$_SESSION['uname'].";Perfil:".$_SESSION['uprofile'].";Grupo:".$_SESSION['ugroup'].";IP:".$_SERVER['REMOTE_ADDR']);

        session_destroy();

        if ( $isAjax == FALSE ) {
            header("Location: login.php");
        } else {
            return 1;
        }
    }

    $session_maxlife = $CFG_PMS["session_timeout"];
    
    if ( $session_maxlife == 0 ) return;
    
    if( isset($_SESSION['timeout']) ){
        $session_life = time() - $_SESSION['timeout'];

        if( $session_life > $session_maxlife ){
            Common::wrLogInfo("Timeout sesión", "Nombre:".$_SESSION['uname'].";Perfil:".$_SESSION['uprofile'].";Grupo:".$_SESSION['ugroup'].";IP:".$_SERVER['REMOTE_ADDR']);

            session_destroy(); 

            if ( $isAjax ) return TRUE;
            header("Location: login.php?session=0");
        }
    }
    
    // Establecemos el nuevo tiempo de sesion
    $_SESSION['timeout'] = time();
}
?>