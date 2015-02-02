<?php 
	include('header.php');
	
	//Makes sure a user is logged in, else it redirects to the login page.
	if(!isset($_SESSION['username'])) {header("Location: login.php");}
?>
<link rel="stylesheet" type="text/css" href="css/home.css">
<div>
	<h1>Here's your homepage!</h1>
</div>
<script src='js/home.js'></script>
<?php 
	echo file_get_contents('footer.php');
?>