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

//{'overlayShow' : true,'overlayOpacity' : 0,'hideOnOverlayClick' : false,'content' : txt}

var gsorder = 0;
var lastlen = 0;
var usr_inedit = 0;
var grp_inedit = 0;
var pms_root = '/phppms';

jQuery.extend(jQuery.fn.fancybox.defaults, {
    overlayShow : true,
    overlayOpacity : 0,
    hideOnOverlayClick : true
});

// Función para limpiar un formulario
function Clear(id,search){
    $("#"+id).resetForm();
    
    if ( search == 1 ){
        document.frmSearch.search.value = "";
        document.frmSearch.cliente.selectedIndex = 0;
        document.frmSearch.categoria.selectedIndex = 0;
    }
}

// Función para realizar una búsqueda
function Buscar(continous){
    //if ( clear == 1){ $('#frmSearch').each(function(){ this.reset();}); }
    //if ( clear == 1){ $("#frmSearch").resetForm(); }
    
    var lenTxtSearch = document.frmSearch.search.value.length;
    
    if ( lenTxtSearch < 3 && continous == 1 && lenTxtSearch >  window.lastlen ) return;
    
    window.lastlen = lenTxtSearch;

    var datos = $("#frmSearch").serialize();
    $("#resBuscar").html('<img src="imgs/loading.gif" />'); 

    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: pms_root + '/ajax_search.php',
        data: datos,
        success: function(response){
            if ( response == 0 ){
                //location.href='login.php?sesion=1';
                doLogout();
            } else {
                $('#resBuscar').html(response);
            }
        },
        error:function(){$('#resBuscar').html('<p class="error"><strong>Oops!</strong> Ha ocurrido un error en la consulta.</p>');}
    });
    return false;
}

function searchSort(skey,page,nav){
    if ( typeof(skey) == "undefined" || typeof(page) == "undefined" ) return false
        
    var cliente = document.frmSearch.cliente.value;
    var categoria = document.frmSearch.categoria.value;
    var buscar = document.frmSearch.search.value;
    
    if ( window.gsorder == 0 ){
        if ( nav == 1 ){
            var sorder = "ASC";
        } else {
            gsorder = 1;
            var sorder = "DESC";
        }
    } else {
        if ( nav == 1 ){
            var sorder = "DESC";
        } else {
            gsorder = 0;
            var sorder = "ASC";
        }
    }

    var form_data = {skey: skey, sorder: sorder, page: page, cliente: cliente, categoria: categoria, search: buscar};

    $("#resBuscar").html('<img src="imgs/loading.gif" />');

    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: pms_root + '/ajax_search.php',
        data: form_data,
        success: function(response){
            if ( response == 0 ){
                //location.href='login.php?sesion=1';
                doLogout();
            } else {
                $('#resBuscar').html(response);
            }
        },
        error:function(){$('#resBuscar').html('<p class="error"><strong>Oops!</strong> Ha ocurrido un error en la consulta.</p>');}
    });
}

function Reload(campos) {
    $("#resBuscar").html('<img src="imgs/loading.gif" />');
    $("#resBuscar").load("/phppms/ajax_search.php?" + campos, function(response, status, xhr) {
        if ( response == 0) {doLogout();}
    });
}

// Función para ver la clave de una cuenta
function verClave(id,full){
    if ( full == 0 ) {
        $.post( 
            pms_root + '/ajax_viewpass.php',
            {'accountid' : id, 'full': 0},
            function( data ) {$( "#clave" ).html(data); 
        });
    } else{
        $.post( pms_root + '/ajax_viewpass.php', {'accountid': id, 'full': full}, 
            function( data ) {var txt = '<div id="fancyView" class="backGrey">' + data + '</div>'; 
                $.fancybox(txt);
            }
        );
    }
}

// Función para las variables de la URL y parsearlas a un array.
function getUrlVars(){
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}

// Función para autentificar usuarios
function doLogin(){
    $("#loading").show();
    $("#loading").html('<img src="imgs/loading.gif" /> Comprobando...');

    var form_data = {user: $("#user").val(), pass: $("#pass").val(), mpass: $("#mpass").val(), login: 'login', is_ajax: 1};
    
    $("#btnLogin").prop('disabled',true);
    
    $.ajax({
        type: "POST",
        dataType: "xml",
        url: 'ajax_chklogin.php',
        data: form_data,
        success: function(xml){
            var status = $(xml).find("status").text();
            var description = $(xml).find("description").text();
            
            $("#loading").empty();
            
            if( status == 0 ){
                location.href='index.php';
            } else if ( status == 2 ){
//                var txt = '<div id="fancyView" class="fancyNone"><span class="altTxtOrange">' + description + '</span></div>';
//                $.fancybox({
//                    'content': txt,
//                    'onClosed' : function() { location.href = 'index.php';}
//                });
                location.href = 'index.php';
            } else if ( status == 3 || status == 4 ){
                var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">' + description + '</span></div>';
                $.fancybox(txt,
                    {'onClosed':function (){$("#btnLogin").prop('disabled',false);}
                });
                $('#smpass').show();
            } else if ( status == 5 ){
                var txt = '<div id="fancyView" class="fancyNone"><span class="altTxtOrange">' + description + '</span></div>';
                 $.fancybox({
                    'content': txt,
                    'onClosed' : function() {location.href = 'index.php';}
                }); 
            } else {
                var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">' + description + '</span></div>';
                $.fancybox(txt,
                    {'onClosed':function (){$("#btnLogin").prop('disabled',false);}
                });
            }},
        error: function (jqXHR, textStatus, errorThrown){
            var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">Ha ocurrido un error<p>' + errorThrown + textStatus + '</p></span></div>';
            $("#loading").empty();
            $.fancybox(txt,
                {'onClosed':function (){$("#btnLogin").prop('disabled',false);}
            });
        }
    });
    
    return false;
}

function doLogout() {
    $.fancybox({
        'href': 'logout.php',
        'onClosed' : function() {location.href = 'login.php';}
    });
}

function checkLogout(){
    var session = getUrlVars()["session"];

    if ( session == 0 ){
        var txt = '<div id="fancyView" class="fancyNone"><span class="altTxtOrange">Sesión finalizada</span></div>';
        $.fancybox(txt,{'onClosed' : function() {location.search = '';}});				
    }
}

// Función para añadir/editar una cuenta
function saveAccount(frm) {
    var datos = $("#"+frm).serialize();
    
    $('#btnGuardar').attr('disabled', true);
    $("#resAccion").html('<img src="imgs/loading.gif" />');                    

    $.ajax({
        type: 'POST',
        dataType: 'xml',
        url: pms_root + '/ajax_accountsave.php',
        data: datos,
        success: function(xml){
            var status = $(xml).find("status").text();
            var description = $(xml).find("description").text();

            if ( status == 0 ){
                var txt = '<div id="fancyView"><span class="altTxtBlue">' + description + '</span></div>';
                $.fancybox(txt);
                $("#resAccion").empty();
                $('#btnGuardar').attr('disabled', true);
            } else {
                var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">' + description + '</span></div>';
                $.fancybox(txt);
                $("#resAccion").empty();
                $('#btnGuardar').removeAttr("disabled");						
            }
        },
        error:function(jqXHR, textStatus, errorThrown){
            var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">Ha ocurrido un error<p>' + errorThrown + textStatus + '</p></span></div>';
            $.fancybox(txt);
        }
    });
}

// Función para eliminar una cuenta
function delAccount(id,action){
    
    var datos = {accountid: id, savetyp: action};

    $("#resAccion").html('<img src="imgs/loading.gif" />');
    
    var res = confirm ('Borrar la cuenta?');
    if (!res){
        $("#resAccion").empty();
        return false;
    }    

    $.ajax({
        type: 'POST',
        dataType: 'xml',
        url: pms_root + '/ajax_accountsave.php',
        data: datos,
        success: function(xml){
            var status = $(xml).find("status").text();
            var description = $(xml).find("description").text();

            if ( status == 0 ){
                var txt = '<div id="fancyView"><span class="altTxtBlue">' + description + '</span></div>';
                $.fancybox({
                    'onClosed' : function() {location.href='index.php';},
                    'content' : txt
                });
                $("#resAccion").empty();
            } else {
                var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">' + description + '</span></div>';
                $.fancybox(txt);
                $("#resAccion").empty();
            }
        },
        error:function(jqXHR, textStatus, errorThrown){ 
            var txt = ('<div id="fancyView" class="fancyErr"><span class="altTxtRed">Oops!...Ha ocurrido un error en la consulta</span></div>');
            $.fancybox(txt);
        }
    });
}

// Función para guardar la configuración
function configMgmt(action){
    switch(action){
        case "addcat":
            var datos = $("#frmAddCategory").serialize();
            var url = pms_root + '/ajax_categorymgmt.php';
            break;
        case "editcat":
            var datos = $("#frmEditCategory").serialize();
            var url = pms_root + '/ajax_categorymgmt.php';
            break;
        case "delcat":
            var datos = $("#frmDelCategory").serialize();
            var url = pms_root + '/ajax_categorymgmt.php';
            break;
        case "saveconfig":
            var datos = $("#frmConfig").serialize();
            var url = pms_root + '/ajax_configsave.php';
            break;
        case "savempwd":
            var datos = $("#frmCrypt").serialize();
            var url = pms_root + '/ajax_configsave.php';
            break;
        default:
            return;
    }    

    $('#btnGuardar').attr('disabled', true);
    $("#resAccion").html('<img src="imgs/loading.gif" />');                    

    $.ajax({
        type: 'POST',
        dataType: 'xml',
        url: url,
        data: datos,
        success: function(xml){
            var status = $(xml).find("status").text();
            var description = $(xml).find("description").text();

            if ( status == 0 ){
                var txt = '<div id="fancyView"><span class="altTxtBlue">' + description + '</span></div>';
                $("#resAccion").empty();
                $('#btnGuardar').attr('disabled', true);
                $.fancybox({
                    'onClosed' : function() {location.reload(true);},
                    'content' : txt
                });
            } else {
                var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">' + description + '</span></div>';
                $.fancybox(txt);
                $("#resAccion").empty();
                $('#btnGuardar').removeAttr("disabled");						
            }
        },
        error:function(jqXHR, textStatus, errorThrown){
            var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">Ha ocurrido un error<p>' + errorThrown + textStatus + '</p></span></div>';
            $.fancybox(txt);
        }
    });

    return false;
}

// Función para descargar/ver archivos de una cuenta
function downFile(fancy){
    if ( $("#files").val() == null ){
        var txt = '<div id="fancyView" class="fancyErr" ><span class="altTxtRed">Archivo no seleccionado</span></div>';
        $.fancybox(txt);
        return false;                
    }
    
    if ( fancy == 1){
        $.fancybox.showActivity();
        $("#action").val('view')
        var frm_data = $('#files_form').serialize();
        
	$.ajax({
		type : "POST",
		cache : false,
		url : pms_root + "/ajax_files.php",
		data : frm_data,
		success: function(file) {
                    //$("#resAccion").html(file);
                    $.fancybox({'content' : file,'overlayOpacity' : 0.5});
                    //setTimeout ($.fancybox.resize() , 3000);
                }
	});
    } else {
        $('#files_form').submit();
    }
}

// Función para eliminar archivos de una cuenta
function delFile(id){
    if ( $("#files").val() == null ){
        var txt = '<div id="fancyView" class="fancyErr" ><span class="altTxtRed">Archivo no seleccionado</span></div>';
        $.fancybox(txt);
        return false;                
    }

    var datos = {fileId: $("#files").val(), action: 'delete'};

    $("#resAccion").html('<img src="imgs/loading.gif" />');

    $.post( pms_root + '/ajax_files.php', {fileId: $("#files").val(), action: 'delete'}, 
        function( data ) { 
            var txt = '<div id="fancyView"><span class="altTxtBlue">' + data + '</span></div>';
            $.fancybox(txt);
            $("#downFiles").load("/phppms/ajax_files.php?id=" + id +"&del=1");
            $("#resAccion").empty();
        }
    );
}

// Función para subir archivos de una cuenta
function upldFile(id){
    var optionsUpld = { 
        beforeSubmit:  function(){
            if ( $("#upload_form input[name=file]").val()  == '' ){
                var txt = '<div id="fancyView" class="fancyErr" ><span class="altTxtRed">Archivo no indicado</span></div>';
                $.fancybox(txt);
                return false;                
            }
            $("#resAccion").html('<img src="imgs/loading.gif" />'); 
        }, 
        success: function(responseText, statusText, xhr, $form){
            var txt = '<div id="fancyView"><span class="altTxtBlue">' + responseText + '</span></div>';
            $.fancybox(txt);
            $("#downFiles").load("/phppms/ajax_files.php?id=" + id +"&del=1");
            $("#resAccion").empty();
        },
        error:function(jqXHR, textStatus, errorThrown){
            var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">Ha ocurrido un error<p>' + errorThrown + textStatus + '</p></span></div>';
            $.fancybox(txt);
        }
    }; 

    //$('#upload_form').ajaxForm(optionsUpld);
    $('#upload_form').ajaxSubmit(optionsUpld);
}

// Función para cargar la lista de usuarios
function loadUsrMgmt(action){
    var datos = {'action' : action};
    
    switch(action){
        case 1:
            $('#usrmgmt_head').html('Gestión de Usuarios');
            $('#actionbar').find('img').hide();
            $('#btnAddUsr,#btnGroups,#btnBack').show();
            break;
        case 2:
            $('#usrmgmt_head').html('Gestión de Usuarios');
            $('#actionbar').find('img').hide();
            $('#btnUsrSave,#btnUsrCancel,#btnBack').show();
            break;
        case 3:
            $('#usrmgmt_head').html('Gestión de Grupos');
            $('#actionbar').find('img').hide();
            $('#btnAddGrp,#btnUsers,#btnBack').show();
            break;
        case 4:
            $('#usrmgmt_head').html('Gestión de Grupos');
            $('#actionbar').find('img').hide();
            $('#btnGrpSave,#btnGrpCancel,#btnBack').show();
            break;            
    }
    
    $.ajax({
        type: 'POST',
        dataType: 'html',
        url: pms_root + '/ajax_usersmgmt.php',
        data: datos,
        success: function(response){
            if ( response == 0 ){
                doLogout();
            } else {
                $('#usrMgmt').html(response);
                $('#usrname_0').focus();
                $("#resAccion").empty();
            }
        },
        error:function(jqXHR, textStatus, errorThrown){ 
            $('#resBuscar').html('<p class="error"><strong>Oops!</strong> Ha ocurrido un error en la consulta.</p>'); 
        }
    });    
}

// Función para la gestión de usuarios
function userMgmt(action,id){
    var url = pms_root + '/ajax_usersave.php';
    var saveError = 0;
    
    switch(action){
        case "add":
            var usrname = $("#usrname_0").val();
            var usrlogin = $("#usrlogin_0").val();
            var usrprofile = $("#usrprofile_0").val();
            var usrgroup = $("#usrgroup_0").val();
            var chkadmin = $("#chkadmin_0").is(':checked');
            var usremail = $("#usremail_0").val();
            var usrnotes = $("#usrnotes_0").val();
            var usrpass = $("#usrpass_0").val();
            var usrpassv = $("#usrpassv_0").val();
            
            var datos = {'savetyp':1,'usrname':usrname,'usrlogin':usrlogin,'usrprofile':usrprofile,'usrgroup':usrgroup,'chkadmin':chkadmin,'usremail':usremail,'usrnotes':usrnotes,'usrpass':usrpass,'usrpassv':usrpassv};
            break;
        case "edit":
            if ( window.usr_inedit == 0 ){                
                window.usr_inedit = 1;
                usrMgmtEnable(id,'tblUsers');                
            } else{
                window.usr_inedit = 0;
                usrMgmtDisable('tblUsers');
            }
            
            return;
            break;
        case "save":
            $("#resAccion").html('<img src="imgs/loading.gif" />');
            
            var usrname = $("#usrname_"+ id).val();
            var usrlogin = $("#usrlogin_"+ id).val();
            var usrprofile = $("#usrprofile_"+ id).val();
            var usrgroup = $("#usrgroup_"+ id).val();
            var chkadmin = $("#chkadmin_"+ id).is(':checked');
            var chkdisabled = $("#chkdisabled_"+ id).is(':checked');
            var usremail = $("#usremail_"+ id).val();
            var usrnotes = $("#usrnotes_"+ id).val();
            
            var datos = {'savetyp':2,'usrid':id,'usrname':usrname,'usrlogin':usrlogin,'usrprofile':usrprofile,'usrgroup':usrgroup,'chkadmin':chkadmin,'chkdisabled':chkdisabled,'usremail':usremail,'usrnotes':usrnotes};
            
            $('input:text').prop('readonly',true)
            $('input:checkbox').prop('disabled',true)
            $('select').prop('disabled',true)
            
            break;
        case "del":
            $("#resAccion").html('<img src="imgs/loading.gif" />');
            
            var usrlogin = $("#usrlogin_"+ id).val();
            
            var res = confirm ('Borrar el usuario \''+usrlogin+ '\'?');
            if (!res){
                $("#resAccion").empty();
                return false;
            }
            
            var datos = {'savetyp':'4','usrid':id,'usrlogin':usrlogin};
            break;
        case "pass":
            var datos = $("#frmUpdUsrPass").serialize() + '&savetyp=3';
            break;
        default:
            return;
    }
    
    $.ajax({
        type: 'POST',
        dataType: 'xml',
        url: url,
        data: datos,
        success: function(xml){
            var status = $(xml).find("status").text();
            var description = $(xml).find("description").text();

            if ( status == 0 ){
                window.usr_inedit = 0;
                
                var txt = '<div id="fancyView"><span class="altTxtBlue">'+description+'</span></div>';
                $("#tblUsers").find(':text,:checkbox,select').removeClass("inedit");
                $.fancybox({
                    'onClosed' : function() {loadUsrMgmt(1);},
                    'content' : txt
                });
            } else if ( status == 1 && action == "pass"){
                $("#resFancyAccion").html('<span class="altTxtRed">'+description+'</span>');
                $("#resFancyAccion").show();
                $.fancybox.resize();                
            } else if ( status == 3 ){
                doLogout();               
            } else {
                var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">'+description+'</span></div>';
                $.fancybox({
                    'onClosed' : usrMgmtEnable(id),
                    'content' : txt
                });                
            }
        },
        error:function(jqXHR, textStatus, errorThrown){
            var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">Ha ocurrido un error<p>' + errorThrown + textStatus + '</p></span></div>';
            $.fancybox(txt);
        }
    });
    
    $("#resAccion").empty();    
}

// Función para la gestión de grupos de usuarios
function groupMgmt(action,id){
    var url = pms_root + '/ajax_groupsave.php';
    var saveError = 0;
    
    switch(action){
        case "add":
            var grpname = $("#grpname_0").val();
            var grpdesc = $("#grpdesc_0").val();
            
            var datos = {'savetyp':1,'grpname':grpname,'grpdesc':grpdesc};
            break;
        case "edit":
            if ( window.grp_inedit == 0 ){                
                window.grp_inedit = 1;
                
                $("#tblGroups").find(':text,:checkbox,select').removeClass("inedit");
                $(".grprow_"+ id ).find(':text,:checkbox,select').addClass("inedit");
    
                $('input:text').prop('readonly',true);
                $('input:checkbox').prop('disabled',true);
                
                $("#grpname_"+ id).prop('readonly',false);
                $("#grpdesc_"+ id).prop('readonly',false);
            } else{
                window.grp_inedit = 0;
                usrMgmtDisable('tblGroups');
            }
            
            return;
            break;
        case "save":
            $("#resAccion").html('<img src="imgs/loading.gif" />');

            var grpname = $("#grpname_"+ id).val();
            var grpdesc = $("#grpdesc_"+ id).val();
            
            var datos = {'savetyp':2,'grpid':id,'grpname':grpname,'grpdesc':grpdesc};
            
            usrMgmtDisable('tblGroups');
            break;
        case "del":
            $("#resAccion").html('<img src="imgs/loading.gif" />');
            
            var grpname = $("#grpname_"+ id).val();
            
            var res = confirm ('Borrar el grupo \''+grpname+ '\'?');
            if (!res){
                $("#resAccion").empty();
                return false;
            }
            
            var datos = {'savetyp':'3','grpid':id,'grpname':grpname};
            break;
        default:
            return;
    }
    
    $.ajax({
        type: 'POST',
        dataType: 'xml',
        url: url,
        data: datos,
        success: function(xml){
            var status = $(xml).find("status").text();
            var description = $(xml).find("description").text();
            description = description.replace(/;;/g,"<br />");

            if ( status == 0 ){
                window.grp_inedit = 0;
                
                var txt = '<div id="fancyView"><span class="altTxtBlue">'+description+'</span></div>';
                usrMgmtDisable('tblGroups');
                $.fancybox({
                    'onClosed' : function() {loadUsrMgmt(3);},
                    'content' : txt
                });
            } else if ( status == 2) {
                var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">'+description+'</span></div>';
                $.fancybox(txt);
            } else if ( status == 3 ){
                doLogout();               
            } else {
                var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">'+description+'</span></div>';
                $.fancybox(txt);
                $(".grprow_"+ id ).find(':text,:checkbox,select').addClass("inedit");
    
                $('input:text').prop('readonly',true);
                $('input:checkbox').prop('disabled',true);
                
                $("#grpname_"+ id).prop('readonly',false);
                $("#grpdesc_"+ id).prop('readonly',false);                
            }
        },
        error:function(jqXHR, textStatus, errorThrown){
            var txt = '<div id="fancyView" class="fancyErr"><span class="altTxtRed">Ha ocurrido un error<p>' + errorThrown + textStatus + '</p></span></div>';
            $.fancybox(txt);
        }
    });
    
    $("#resAccion").empty();
}

// Función para habilitar los campos del formulario de usuarios
function usrMgmtEnable(id,tbl){
    $("#"+tbl).find(':text,:checkbox,select').removeClass("inedit");
    $(".usrrow_"+ id ).find(':text,:checkbox,select').addClass("inedit");
    
    var ldap = $("#usrldap_"+ id).val();

    $('input:text').prop('readonly',true);
    $('input:checkbox').prop('disabled',true);
    $('select').prop('disabled',true);

    $("#usrname_"+ id).prop('readonly',false);
    $("#usrnotes_"+ id).prop('readonly',false);
    $("#usrgroup_"+ id).prop('disabled',false);
    $("#usrprofile_"+ id).prop('disabled',false);

    if ( ldap == 0 ){
        $("#usrname_"+ id).prop('readonly',false);
        $("#usrlogin_"+ id).prop('readonly',false);
        $("#usremail_"+ id).prop('readonly',false);
        $("#usrnotes_"+ id).prop('readonly',false);
        $("#chkadmin_"+ id).prop('disabled',false);
        $("#chkdisabled_"+ id).prop('disabled',false);
        $("#usrgroup_"+ id).prop('disabled',false);
        $("#usrprofile_"+ id).prop('disabled',false);
    }
    
    $("#usrname_"+ id).focus();
}

// Función para deshabilitar los campos del formulario de usuarios
function usrMgmtDisable(tbl){
    $("#"+tbl).find(':text,:checkbox,select').removeClass("inedit"); 
    $('input:text').prop('readonly',true)
    $('input:checkbox').prop('disabled',true)
    $('select').prop('disabled',true)    
}

// Función para mostrar el formulario para cambio de clave de usuario
function usrUpdPass(id,usrlogin){
    $.fancybox({
        'href': 'pmsusers_pass.php?usrid='+id+'&usrlogin='+usrlogin,
        'overlayOpacity' : 0.5
    });
}