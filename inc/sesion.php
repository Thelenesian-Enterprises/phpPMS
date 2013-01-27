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

function check_session($isAjax = FALSE, $isLogin = FALSE){
    global $CFG_PMS, $LANG;
    
    session_start();
    
    if ( $isLogin == TRUE ) return;

    if( ! isset($_SESSION["ulogin"]) ) {
        session_destroy();

        if ( $isAjax == FALSE ) {
            header("Location: login.php");
            die();
        } else {
            return TRUE;
        }
    }

    $session_maxlife = $CFG_PMS["session_timeout"];
    
    if ( $session_maxlife == 0 ) return;
    
    if( isset($_SESSION['timeout']) ){

        $session_life = time() - $_SESSION['timeout'];

        if( $session_life > $session_maxlife ){
            Common::wrLogInfo($LANG['event'][18], $LANG['eventdesc'][11].":".$_SESSION['uname'].";".$LANG['eventdesc'][12].":".$_SESSION['uprofile'].";".$LANG['eventdesc'][13].":".$_SESSION['ugroup'].";".$LANG['eventdesc'][11].":".$_SERVER['REMOTE_ADDR']);

            if ( $isAjax ) return TRUE;
            
            session_destroy();
            header("Location: login.php?session=0");
            die();
        }
    }
    
    // Establecemos el nuevo tiempo de sesion
    $_SESSION['timeout'] = time();
}
?>