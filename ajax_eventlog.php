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
    
    if ( check_session(TRUE) ) return "0";

    $objConfig = new Config;
    $dbh = $objConfig->connectDb();
    $resQuery = $dbh->query("SELECT * FROM log");

    while ( $row = $resQuery->fetch_assoc()){
        $rowClass = ( $rowClass == "row_even" ) ? "row_odd" : "row_even";

        echo "<TR CLASS='$rowClass'>";
        echo "<TD>".$row["datLog"]."</TD>";
        echo "<TD>".utf8_decode($row["vacAccion"])."</TD>";
        echo "<TD>".strtoupper($row["vacLogin"])."</TD>";
        echo "<TD>".str_replace(";","<br />",$row["txtDescripcion"])."</TD>";
        echo "</TR>";
    }
    
    $resQuery->free();
?>
