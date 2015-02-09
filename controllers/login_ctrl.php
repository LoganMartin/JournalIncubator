<?php
	session_start();
	require("database_ctrl.php");
	require("../../interfacer.php");
	
	//Do not change, or everything will break.
	//Allows for communication with OJS code.
	
	//Takes function name 'action' specified by ajax, and executes said function.
	if(isset($_POST['action']) && !empty($_POST['action'])) {
		$action = $_POST['action'];
		switch($action) {
			case 'login': 		login(); break;
			default: 			break;
		}
	}
	else {
		echo "Error: Function not found";
	}
	
	
	function login() {
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		$validCredentials = checkLogin($username, $password);
		
		if(!$validCredentials) {
			echo "Error: Please enter valid login credentials";
		}
		else {
			$_SESSION['ojs_username'] = $username;
			echo "success";
		}
	}
	

?>