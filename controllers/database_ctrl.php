<?php	
	$connection = mysql_connect("localhost", "root", "password");
	if(!$connection) {
		die('Could not connect: '. mysql_error());
	}
	mysql_select_db("ojs_project", $connection);
?>