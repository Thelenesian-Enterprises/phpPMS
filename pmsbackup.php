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
    include_once (PMS_ROOT."/inc/includes.php");
    check_session();

    global $CFG_PMS;

    $objCommon = new Common;
    
    $siteName = $CFG_PMS["siteshortname"];
    $doBackup = ( isset($_POST["doBackup"]) ) ? $_POST["doBackup"] : "" ;
    
    $bakDirPMS = dirname(__FILE__);
    $bakFilePMS = $bakDirPMS.'/backup/'.$siteName.'.tgz';
   
    Common::printHeader(FALSE,TRUE);
    
    echo '<BODY>';

    Common::printBodyHeader();
    
    Users::checkUserAccess("backup") || die ('<DIV CLASS="error">'.$LANG['msg'][34].'</DIV');
    
    echo '<DIV ID="container" ALIGN="center">';
    echo '<H2>'.$LANG['buttons'][12].'</H2>';
    echo '<DIV CLASS="action midround">';
    echo '<FORM ACTION="pmsbackup.php" METHOD="post" NAME="frmBackup">';
    echo '<INPUT TYPE="hidden" NAME="doBackup" VALUE="1" />';
    echo '</FORM>';
    echo '<IMG SRC="imgs/backup.png" TITLE="'.$LANG['buttons'][19].'" CLASS="inputImg" OnClick="document.frmBackup.submit();" />';
    
    $objCommon->printBackLinks(TRUE);
    
    echo '</DIV>';
    echo '<TABLE CLASS="data">';
    echo '<TR><TD CLASS="descCampo">'.$LANG['backup'][0].'</TD>';
    echo '<TD>';
    if ( $doBackup == 1 ){
        $objConfig = new Config;

        $arrOut = $objConfig->doDbBackup(dirname(__FILE__));
        if ( $arrOut ){
            foreach ($arrOut as $strOut){
                echo ( $strOut != "" ) ? $strOut."<br />" : "";
            }
            echo $LANG['backup'][2];
        } else {
            echo $LANG['backup'][3];
        }

    } else {
        if ( file_exists($bakFilePMS) ){
            echo $LANG['backup'][1].": ".date("F d Y H:i:s.", filemtime($bakFilePMS));
        } else {
            echo $LANG['backup'][4];
        }
    }		
    echo '</TD></TR>';
    
    echo '<TR><TD WIDTH="25%" CLASS="descCampo">'.$LANG['backup'][5].'</TD>';
    echo '<TD>';
    
    if ( file_exists($bakFilePMS) ){
        echo '<A HREF="backup/'.$siteName.'_db.sql">Backup BBDD</A> - <A HREF="backup/'.$siteName.'.tgz">Backup '.$siteName.'</A>';
    } else {
         echo $LANG['backup'][6];
    }
    
    echo '</TD></TR></TABLE>';
    
    echo '<DIV ID="resAccion"></DIV>';
    
    Common::PrintFooter();
?>