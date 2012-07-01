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
    
    session_start();
    
    if ( ! $_SESSION["pms_upd"] ){
        $checkVersion = Config::checkUpdates();
    } else {
        $checkVersion = $_SESSION["pms_upd"];
    }
    
    session_write_close();

    if ( is_array($checkVersion) ){
        echo '<A HREF="'.$checkVersion[1].'" TARGET="_blank" TITLE="'.$LANG['common'][1].'"><IMG SRC="imgs/update.png" />&nbsp;'.$checkVersion[0].'</A>';
    } elseif ( $checkVersion == TRUE ){
        echo '<IMG SRC="imgs/ok.png" TITLE="'.$LANG['common'][0].'"/>';
    }
?>