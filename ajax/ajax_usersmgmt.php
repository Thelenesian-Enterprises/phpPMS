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

define('PMS_ROOT', '..');
include_once (PMS_ROOT."/inc/includes.php");

// Comprobamos si la sesión ha caducado
if ( check_session(TRUE) ) return "0";

Users::checkUserAccess("users") || die ('<DIV CLASS="error"'.$LANG['msg'][34].'</DIV');

$objUsers = new Users;

switch ( $_POST["action"] ){
    case 1:
        $objUsers->getUsersTable();
        break;
    case 2:
        $objUsers->getNewUserTable();
        break;
    case 3:
        $objUsers->getGroupsTable();
        break;
    case 4:
        $objUsers->getNewGroupTable();
        break;
}
?>