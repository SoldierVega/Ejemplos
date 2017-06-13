// Author:  GSI1034
// Created On: 15/7/2014
// Description: Javascript file containing all the common Anexo24 functions.

// -----------------------------------------------------------------
// ------------------------ ANEXO 24 ------------------------
// -----------------------------------------------------------------

// Set datatables custom spanish messages
var dataTableEspanol={
    "sProcessing":     "Procesando...",
    "sLengthMenu":     "Mostrar _MENU_ registros",
    "sZeroRecords":    "No se encontraron resultados",
    "sEmptyTable":     "Ningún dato disponible en esta tabla",
    "sInfo":           "Mostrando registros del _START_ al _END_ de  _TOTAL_ registros",
    "sInfoEmpty":      "Mostrando registros del 0 al 0 de 0 registros",
    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
    "sInfoPostFix":    "",
    "sSearch":         "Buscar:",
    "sUrl":            "",
    "sInfoThousands":  ",",
    "sLoadingRecords": "Cargando...",
    "oPaginate": {
        "sFirst":    "Primero",
        "sLast":     "Último",
        "sNext":     "Siguiente",
        "sPrevious": "Anterior"
    },
    "oAria": {
        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
    }
}

// Adds sort types to the datatables columns
jQuery.extend( jQuery.fn.dataTableExt.oSort, {
    "date-mex-pre": function ( a ) {
        if(a == 'ilimitado') {
            return Number.MAX_VALUE;
        } else {
            var ukDatea = a.split('/');
            return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
        }
    },
 
    "date-mex-asc": function ( a, b ) {
        return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
 
    "date-mex-desc": function ( a, b ) {
        return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
} );

// Add a method to JQuery validator that does not accept special characters
$.validator.addMethod('noSpecialCharacters', function(value, element) {
   return this.optional(element) || /^[A-Za-z0-9\s~!@#%^&*()+={}|,.<>?-]+$/.test(value);
}, 'Favor no utilizar caracteres especiales.');




// Object used to control webpage functionality.
Anexo24 = { 
    
    // Initialize webpage divs.
    init: function() {
        if(!$.cookie("logged_in")) {
        	$("#header_layer").hide();
            $("#login_layer").show();
            $("#gui_layer").hide();
            Anexo24.load_login();
        } else {
        	$("#header_layer").show();
            $("#login_layer").hide();
            $("#gui_layer").show();
            Anexo24.load_header();
            Anexo24.load_module();
            Anexo24.last_update();
        }
    },
    
    // Function to run when a user tries to login
    valid_user: function(){
        if($("#loginUsername").val() == "" || $("#loginPassword").val() == ""){
            var mensaje  = "Uno o mas de los campos contienen valores nulos, favor de verificar.";
            alert(mensaje);
        } else {            
            $.ajax({
                type: "POST",
                url: "scripts/php/ajax_functions.php",
                data: {
                    accion   : cryptex.getEncode("valid_user"),
                    username : cryptex.getEncode($("#loginUsername").val()),
                    password : cryptex.getEncode($("#loginPassword").val())
                },
                //async: false,
                dataType: "json",
                success : function(response) {
                    Anexo24.successful_login_call(response);        
                },
                error:function(response) {
                    alert("ERROR: LOGIN FAILED");
                    console.log(response);
                    Anexo24.cancel_login();
                }
            });
        }
    },
    
   
    
    // Function that clears user and password input boxes.
    cancel_login: function() {
        $("#loginUsername").val("");    
        $("#loginPassword").val("");    
    },

    //Load the login form for the webpage
    load_login : function() {
    	// Load the login_layer with the login.html script
    	$("#login_layer").load("./scripts/html/login.html", function() {

    		// Add jqueryui style to buttons and textfields
    		$("input:text, input:password").button().addClass("input_text_field");
    		$("input:button").button();

    		// Redirect action when enter is pressed on either textfield
    		$("#loginPassword, #loginUsername").keypress(function(e) {
    			if(e.which == 13) {
    				e.preventDefault();
    				Anexo24.valid_user();
    			}
    		});
    	});
    },

    // Load the dynamic menu for the webpage
    load_header: function() {
        $("#header_layer").load("./scripts/html/header.html", function() {

            // Use AJAX to send a POST request and obtain the html string that creates the menu html.
            $.ajax({
                type: "POST",
                url: "scripts/php/ajax_functions.php",
                data: {
                    accion: cryptex.getEncode("load_menu"),
                    administrator: $.cookie("administrator")
                },
                dataType: "html",
                success: function(response) {
                    var userString = "<a id='aCambioPassUser' style='cursor:pointer;'>Usuario: " + cryptex.getDecode($.cookie('user_name'))+"</a>";
                    $("#user_info_header").html(userString);
                    $("#menu_div").html(response);
                    //$("td#logo_slot").html("<img src='./images/logoglobal.gif' width='100' height='58' />");
                    Anexo24.load_change_pass();
                    //$("td#logo_slot").html("<img src='./images/logoglobal.gif' width='100' height='58' />");
                },
                error: function(response) {
                    alert("ERROR: COULD NOT LOAD MENU.");
                }
            });
        });
    },

    load_module: function() {
    	if($.cookie("module_loaded") && cryptex.getDecode($.cookie("module_loaded")) == "true") {
            var fileName = cryptex.getDecode($.cookie("module_filename"));
            var functionName = cryptex.getDecode($.cookie("module_functionName"));
            var container = cryptex.getDecode($.cookie("module_container"));
            Anexo24.carga_modulo(fileName, functionName, container);
        }
        if($.cookie("module_filename")!="admon_empresa.php"){
            var user_type = cryptex.getDecode($.cookie("user_type"));
            if(user_type == "GPC" || user_type == "ADM"){
                Anexo24.verificarEmpresa("getCountEmpresa");
                Anexo24.verificarCert("verificarCertificacion");
            }else{
                Anexo24.verificarEmpresaUsuario("getCountEmpresa");
            }
        }
    },
};