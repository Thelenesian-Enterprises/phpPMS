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

    define('PMS_ROOT', '.');
    include_once (PMS_ROOT . "/inc/includes.php");
    //check_session(TRUE,TRUE);
    session_start();
    
    Common::wrLogInfo($LANG['event'][16], $LANG['eventdesc'][11].":".$_SESSION['uname'].";".$LANG['eventdesc'][12].":".$_SESSION['uprofile'].";".$LANG['eventdesc'][13].":".$_SESSION['ugroup'].";".$LANG['eventdesc'][14].":".$_SERVER['REMOTE_ADDR']);
    
    session_destroy();
    
    echo '<div id="fancyView" class="msgWarn">'.$LANG['msg'][74].'</div>';
?>