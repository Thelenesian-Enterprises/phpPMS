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

class Common {

    public $arrBackLinks = array('skey' => '', 'sorder' => '', 'categoria' => '', 'cliente' => '', 'search' => '', 'page' => '');
    
    // Método para escribir en el log
    public static function wrLogInfo ($strAccion, $strDescripcion) {
        global $CFG_PMS, $LANG;
        
        session_start();
        
        if ( $CFG_PMS["logenabled"] == 0 ) return FALSE;
        
        $strLogin = ( $_SESSION["ulogin"] ) ? $_SESSION["ulogin"] : "-";
        $intUserId = ( $_SESSION['uid'] ) ? $_SESSION['uid'] : 0;
        $strAccion = utf8_encode($strAccion);
        $strDescripcion = utf8_encode(addslashes($strDescripcion));
        
        $strQuery = "INSERT INTO log (vacLogin, intUserId, vacAccion, txtDescripcion) 
                    VALUES('$strLogin',$intUserId,'$strAccion','$strDescripcion')";

        $objConfig = new Config;
        $resQuery = $objConfig->connectDb();
        $resQuery->query($strQuery);
        
        if ( ! $resQuery ) {
            $strQueryEsc = $this->dbh->real_escape_string($strQuery);
            Common::wrLogInfo(__FUNCTION__, $this->dbh->error.";SQL: ".$strQueryEsc);
            return FALSE;
        }
        
        $resQuery->close();        
    }

    // Método para enviar un email
    // TODO: mail auth
    static function sendEmail($strMensaje,$strTo = ""){
        global $CFG_PMS, $LANG;
        
        if ( $CFG_PMS["mailenabled"] == 0 ) return FALSE;
        
        $strTo = isset ($strTo) ? $strTo : $CFG_PMS["mailfrom"];
        
        $strFrom = $CFG_PMS["mailfrom"];
        $strAsunto = $LANG['common'][4].' '.$CFG_PMS["siteshortname"];

        // Para enviar un correo HTML mail, la cabecera Content-type debe fijarse
        //$strHead  = 'MIME-Version: 1.0' . "\r\n";
        //$strHead .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Cabeceras adicionales
        //$strHead .= "To: $strDestinatario \r\n";
        $strHead = "From: ".$CFG_PMS["siteshortname"]." <$strFrom>\r\n";
        $strHead .= "Reply-To: $strTo \r\n";
        $strHead .= "Cc: $strFrom\r\n";

        $strMensaje = $strMensaje." ".$LANG['common'][3]." '".$_SESSION["ulogin"]."'";
        // Enviar correo
        mail($strTo, $strAsunto, $strMensaje, $strHead);
    }
    
    // Método para imprimir la cabecera HTML
    static function printBodyHeader() {
        global $CFG_PMS, $startTime, $LANG;
        
        $startTime = microtime();
        
        $strAdmin = ( $_SESSION["uisadmin"] == 1 ) ? "(A)" : "";
        $strUserName = ( $_SESSION["uname"] ) ? $_SESSION["uname"] : $_SESSION["ulogin"];
        $strUserGroup = ( $_SESSION["ugroupn"] ) ? $_SESSION["ugroupn"] : $_SESSION["ugroup"];
        
        $strUser = $strUserName." (".$strUserGroup.") $strAdmin";
        $chpass = ( $_SESSION['uisldap'] == 0 ) ? '<IMG SRC="imgs/key.png" CLASS="iconMini" TITLE="'.$LANG['buttons'][0].'" Onclick="usrUpdPass('.$_SESSION["uid"].',\''.$_SESSION["ulogin"].'\')" />' : '';
        
        echo '<NOSCRIPT><DIV ID="nojs">'.$LANG['common'][2].'</DIV></NOSCRIPT>';
        echo '<DIV ID="header" CLASS="round"><DIV ID="logo"><IMG SRC="imgs/logo.png" />'.$CFG_PMS["sitename"].'</DIV>';
        echo '<DIV ID="sesion">'.$strUser.$chpass.'<IMG SRC="imgs/exit1.png" TITLE="'.$LANG['buttons'][1].'" OnClick="doLogout();" /></DIV></DIV>';

        // FIXME
        if ( $_SESSION["ugroup"] == 99 ){			
            echo '<DIV CLASS="error">'.$LANG['msg'][92].'</DIV>';
        }
    }

    // Método para imprimir el pie HTML
    static function PrintFooter() {
        global $LANG, $CFG_PMS, $startTime;
        
        echo '<DIV ID="footer">
                <DIV ID="updates"></DIV>
                <DIV ID="project">
                    <A HREF="http://sourceforge.net/projects/phppms/" TARGET="_blank">phpPMS '.PMS_VERSION.'</A> 
                    &nbsp;::&nbsp;
                    <A HREF="http://cygnux.org" TARGET="_blank">cygnux.org</A>
                </DIV>';
            
        
        if ( $LANG["completed"] == 0 ){
            echo '<DIV ID="status">'.$LANG['common'][7].'</DIV>';
        }
        
        echo '</DIV>';
        
        if ( $CFG_PMS["debug"] && $_SESSION["uisadmin"] == 1 ){
            $stopTime = microtime();
            
            echo '<DIV ID="debug"><PRE>';
            echo "Render start : ".$startTime;
            echo "\nRender stop : ".$stopTime;
            echo "\nRender total: ".($stopTime - $startTime)."\n\n";
            
            print_r($_SESSION);
            print_r($CFG_PMS);
            echo "</PRE></DIV>";
        }
        
        echo "</DIV></BODY></HTML>";
    }

    function StartRenderTimer() {
        $RenderStartTime = microtime();
    }

    function StopRenderTimer() {
        $RenderStopTime = microtime();
        $RenderTime = $RenderStopTime - $RenderStartTime;
        return $RenderTime;
    }

    function PrintRenderTime($RenderTime) {
        echo ("<BR>");
        echo ("<DIV ALIGN=\"center\">");
        echo ("<P CLASS=\"footer\">[ created in  ". $RenderTime . " seconds ]</P>");
        echo ("</DIV>");
    }

    // Método para imprimir los links de la cabecera HTML
    static function printHeader($isLogin=FALSE,$endHead=FALSE){
        global $CFG_PMS;
        
        // UTF8 Headers
        header("Content-Type: text/html; charset=UTF-8");
        
        // Start the page
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
            <HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
            <HEAD><TITLE>';
        echo ( $isLogin ) ? "Acceso ".$CFG_PMS["siteshortname"] : $CFG_PMS["siteshortname"]." - ".$CFG_PMS["sitename"];
                
        echo '</TITLE>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <LINK REL="icon" TYPE="image/png" HREF="/phppms/imgs/logo.png">
            <LINK REL="stylesheet" HREF="css/styles.css" TYPE="text/css">
            <LINK rel="stylesheet" HREF="javascript/fancybox/jquery.fancybox-1.3.4.css" TYPE="text/css" MEDIA="screen" />
            <SCRIPT TYPE="text/javascript" SRC="javascript/jquery.js"></SCRIPT>
            <SCRIPT TYPE="text/javascript" SRC="javascript/jquery.form.js"></SCRIPT>
            <SCRIPT TYPE="text/javascript" SRC="javascript/fancybox/jquery.fancybox-1.3.4.pack.js"></SCRIPT>
            <SCRIPT TYPE="text/javascript" SRC="javascript/functions.php?lang='.PMS_LANG.'&root='.PMS_ROOTURL.'"></SCRIPT>';
        echo ( $endHead ) ? "</HEAD>" : "";
    }
    
    // Método para imprimir los enlaces y el formulario de "volver"
    function printBackLinks($printBackForm = FALSE){
        global $LANG;
        
        foreach ($this->arrBackLinks as $name => $value){
            $txtLinks .= '<INPUT TYPE="hidden" NAME="'.$name.'" VALUE="'.$value.'" />';
        }
        
        if ( $printBackForm ){
            echo '<IMG SRC="imgs/back.png" TITLE="'.$LANG['buttons'][3].'" CLASS="inputImg" ID="btnBack" OnClick="document.frmBack.submit();" />';
            echo '<FORM ACTION="index.php" METHOD="post" NAME="frmBack" >';
            echo $txtLinks;
            echo '</FORM>';
        } else {
            echo $txtLinks;
        }
    }

    // Método para devolver un documento XML
    static function printXML($resXML){
        if ( !is_array($resXML) ) return FALSE;
        
        // Header para el tipo XML
        header("Content-Type: application/xml");

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<root>\n<status>".$resXML["status"]."</status>\n <description>".$resXML["description"]."</description>\n</root>";
        echo $xml;
        return;
    }
    
    static function printHelpButton($type, $id){
        global $LANG;
        
        echo '<IMG SRC="imgs/help.png" TITLE="'.$LANG['buttons'][49].'" CLASS="inputImgMini" OnClick="getHelp(\''.$type.'\', '.$id.')" />';
    }
}
?>