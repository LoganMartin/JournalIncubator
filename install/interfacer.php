<?php

	/**
	 *	README
	 * Paste this file into the main OJS folder, which holds the JournalIncubator folder. 
	 */
	 
	 
	//Do not change, or everything will break.
	//Allows for communication with OJS code.
	define('INDEX_FILE_LOCATION', __FILE__);
	require("lib/pkp/includes/bootstrap.inc.php");
	
	
	function checkLogin($username, $password) {
		$user = Validation::checkCredentials($username, $password, $reason);
		return $user;		
	}

?>