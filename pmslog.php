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
    check_session();

    $intUGroupFId = $_SESSION["ugroup"];
    $intProfileId = $_SESSION["uprofile"];
    $blnUIsAdmin = $_SESSION["uisadmin"];
    
    Common::printHeader(FALSE,TRUE);

    echo '<BODY>';
    
    Common::printBodyHeader(); 
    Users::checkUserAccess("logview") || die ('<DIV CLASS="error"'.$LANG['msg'][34].'</DIV');
    
    echo '<DIV ID="container" ALIGN="center">';
    echo '<H2>'.$LANG['buttons'][15].'</H2>';
    echo '<DIV CLASS="action midround">';

    $objCommon = new Common();
    $objCommon->printBackLinks(TRUE); 

    echo '</DIV>';
    
    echo '<DIV ID="resEventLog">';
    echo '<TABLE CLASS="data">';
    echo '<THEAD><TR CLASS="headerGrey"><TH>'.$LANG['eventlogs'][0].'</TH><TH>'.$LANG['eventlogs'][1].'</TH><TH>'.$LANG['eventlogs'][2].'</TH><TH>'.$LANG['eventlogs'][3].'</TH></TR></THEAD>';
    echo '<TBODY ID="resSearch"></TBODY></TABLE></DIV>';
        
    echo '<SCRIPT> $("#resSearch").load(pms_root + "/ajax_eventlog.php");</SCRIPT>';
    
    Common::PrintFooter();
?>