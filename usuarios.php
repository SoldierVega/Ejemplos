<?php
	// Require the connection to the empresa database and the cryptex functions
	require_once("./../database_scripts/db_empresa_connection.php");
    require_once("./../database_scripts/db_functions.php");
    require_once("./../cryptex_functions.php");

    // Decode the action and call the corresponding function
    $action = getDecode($_REQUEST['action']);
    $action and $action != "" ? call_user_func_array($action, array()) : "";

    function load_form() {
    	global $empresa_connection;

    	// Initiate the output
    	$response = array();
    	$response['html'] = "";

    	// Add the form elements
    	$response['html'] .= "<label for='tipo'>Tipo: </label><select name='tipo' id='tipo' rel='0'>";
    	$response['html'] .= get_tipo_options();
    	$response['html'] .= "</select><br />";
    	$response['html'] .= "<label for='nombre'>Nombre: </label><input type='text' name='nombre' id='nombre' rel='1' /><br />";
    	$response['html'] .= "<label for='login'>Login: </label><input type='text' name='login' id='login' rel='2' /><br />";
    	$response['html'] .= "<label for='correo_electronico'> Correo Electronico: </label><input type='text' name='correo_electronico' id='correo_electronico' rel='3' /><br />";
    	$response['html'] .= "<label for='contrasena'>Contrasena: </label><input type='password' name='contrasena' id='contrasena' /><br />";
    	$response['html'] .= "<label for='confirmacion'>Confirma Contrasena: </label><input type='password' name='confirma_contrasena' id='confirma_contrasena' /><br />";
        $response['html'] .= "<label for='habilitado'>Habilitado: </label><select name='habilitado' id='habilitado' rel='4'>";
        $response['html'] .= "<option value='1'>Habilitado</option><option value='0'>Deshabilitado</option>";
        $response['html'] .= "</select>";
        $response['html'] .= "<input type='hidden' name='editPassword' id='editPassword' rel='5' />";
    	$response['status'] = "success";

    	// Close the connection and return the JSON encoded response
    	close_connection($empresa_connection);
    	echo json_encode($response);
    }

    function load_table() {
    	global $empresa_connection;
    	
    	// Initiate HTML output
    	$html = "";

    	// Create the header of the table
    	$html .= "<thead>\n";

    	// Create the search boxes for the table
    	$html .= "<tr bgcolor='#ADD8E6' class='search' id='search_row'>";
        $html .= "<th><input type='text' class='filter_th' size='' style='width:75%;'></th>";
        $html .= "<th><input type='text' class='filter_th' size='' style='width:75%;'></th>";
        $html .= "<th><input type='text' class='filter_th' size='' style='width:75%;'></th>";
        $html .= "<th><input type='text' class='filter_th' size='' style='width:75%;'></th>";
        $html .= "<th><input type='text' class='filter_th' size='' style='width:75%;'></th>";
        $html .= "<th></th>"; // Add empty search box for password change column
        $html .= "</tr>";

    	// Create the column names
    	$html .= "<tr>\n";
    	$html .= "<th>Tipo</th>\n";
    	$html .= "<th>Nombre</th>\n";
    	$html .= "<th>Login</th>\n";
    	$html .= "<th>Correo Electr&oacute;nico</th>\n";
        $html .= "<th>Habilitado</th>\n";
        $html .= "<th>Editar Contrase&ntilde;a</th>\n";
    	$html .= "</tr>\n";

    	// Close the table header
    	$html .= "</thead>\n";

    	// Create the body of the table
    	$html .= "<tbody>\n";

    	// Create and run the query to select the table information
    	$query = "SELECT sTipoUsuario, sNombre, sLogin, iHabilitado, sCorreoElectronico FROM cb_usuarios";
    	$result_set = run_query($empresa_connection, $query);

        // Get assoc. array with user types to description.
        $userTypes = get_user_types_assoc();

    	while($row = mysqli_fetch_assoc($result_set)) {
            $userType = $userTypes[$row['sTipoUsuario']];
    		$html .= "<tr id='{$row['sLogin']}'>\n";
    		$html .= "<td class='center'>$userType</td>\n";
    		$html .= "<td class='center'>{$row['sNombre']}</td>\n";
    		$html .= "<td class='center'>{$row['sLogin']}</td>\n";
            $html .= "<td class='center'>{$row['sCorreoElectronico']}</td>\n";
            $html .= "<td class='center'>";
            switch ($row['iHabilitado']) {
                case 1:
                    $html .= "Habilitado";
                    break;
                case 0:
                    $html .= "Deshabilitado";
                    break;
            }
            $html .= "</td>\n";
    		$html .= "<td class='center'><button onclick='fn_administracion.change_password(\"{$row['sLogin']}\");' ><span class='ui-icon ui-icon-pencil'></span></button></td>\n"; 
    		$html .= "</tr>";
    	}

    	// Close the body of the table
    	$html .= "</tbody>\n";

    	// Close the database connection
    	close_connection($empresa_connection);

    	// Print out encoded JSON response
    	$response = array();
    	$response['status'] = 'success';
    	$response['html'] = $html;
    	echo json_encode($response);
    }

    
    function valid_login_name($login_name) {
    	global $empresa_connection;

    	// Run query to select a record with the same login name
    	$query = "SELECT COUNT(sLogin) as 'count' 
    			  FROM cb_usuarios
    			  WHERE sLogin = '$login_name'";
    	$result = run_query($empresa_connection, $query);
    	$row = mysqli_fetch_assoc($result);
    	$count = $row['count'];

    	if($count != 0) {
    		return_error("El usuario ya existe.");
    		return false;
    	}

    	return true;
    }

    function get_user_types_assoc() {
        global $empresa_connection;

        // Run query to select the user types
        $query = "SELECT sTipoUsuario, sDescripcion FROM ct_tipos_de_usuarios";
        $resultSet = run_query($empresa_connection, $query);

        // Itterate through resultSet and build the array
        $userTypes = array();
        while($row = mysqli_fetch_assoc($resultSet)) {
            $userTypes[$row['sTipoUsuario']] = $row['sDescripcion'];
        }

        // return the assoc. array
        return $userTypes;
    }

  

    function valid_user_pass() {
        global $empresa_connection;

        // Initate response with success
        $response = array();
        $response['status'] = "success";

        // Get user name and password from GET and decode them
        $sLogin = getDecode($_GET['username']);
        $hPassword = getDecode($_GET['password']);

        // Create and run query
        $query = "SELECT COUNT(sLogin) as 'count'
                  FROM cb_usuarios
                  WHERE sLogin = '$sLogin' AND hPassword = sha1('$hPassword')";
        $result = run_query($empresa_connection, $query);
        $row = mysqli_fetch_assoc($result);
        $count = $row['count'];

        // If there isn't exactly one result, there is an error.
        if($count < 1) {
            $response['status'] = "error";
            $response['message'] = "Invalid username and password combination.";
        } elseif($count > 1) {
            $response['status'] = "error";
            $response['message'] = "More than one account with the same username and password combination.";
        }

        // Echo out json encoded response and close the connection
        echo json_encode($response);
        close_connection($empresa_connection);
    }

    
    function change_yours_password(){
        global $empresa_connection;
        $hPassword = getDecode($_POST['hPassword']);
        $sUsuario = getDecode($_COOKIE['user_username']);
        $sIPActualizacion = $_SERVER['REMOTE_ADDR'];

        // BUild and run the query to change a users password
        $query = "UPDATE cb_usuarios 
                  SET hPassword = sha1('$hPassword'), sUsuarioActualizacion = '$sUsuario',
                      dFechaActualizacion = GetDate(), sIPActualizacion = '$sIPActualizacion'
                  WHERE sLogin = '$sUsuario'";
        run_query($empresa_connection, $query);
        
        $response=array();
        $response["msg"]="La contraseña se actualizo de forma exitosa.";
        echo json_encode($response);
    }

   

	function return_error($error_message) {
        header("HTTP/1.0 424 Method Failure");
        echo $error_message;
    }
?>