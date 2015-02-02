<?php
	//Starts/Restarts session when user navigates to login page, in order to save their credentials.
	session_start();
	session_destroy();
	include('header.php');
	
?>
<link rel="stylesheet" type="text/css" href="css/login.css">
<div class="form-signin well">
	<h2 class="form-signin-heading">Please Sign In</h2>
	<div id="error-div" class="hidden">
		<div id="error-alert" class="alert alert-danger"></div>
	</div>
	<input id="loginUsername" type="text" class="form-control" placeholder="Username">
	<input id="loginPassword" type="password" class="form-control" placeholder="Password">
	<button id="signin-button" class="btn btn-large btn-primary" onclick="verifyLogin()">Sign in</button>
	<a href="#" id="register-button" class="btn btn-large btn-default pull-right" role="button">Need An Account?</a>
</div>
<script src='js/login.js'></script>

<?php 
	echo file_get_contents('footer.php');
?>