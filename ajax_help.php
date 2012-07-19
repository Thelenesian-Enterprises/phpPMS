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

define('PMS_ROOT', '.');
include_once (PMS_ROOT."/inc/includes.php");
check_session(TRUE);

$helpType = $_GET["type"];
$helpId = (int)$_GET["id"];

if ( $helpId >= 0 && $helpType ){
    echo '<DIV ID="fancycontainer" CLASS="help" ALIGN="center">';
    echo '<TABLE CLASS="fancydata">';
    echo '<TR><TD>'.$LANG["help"][$helpType][$helpId].'</TD></TR>';
    echo '</TABLE></DIV>';
}

?>
