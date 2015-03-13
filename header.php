<?php 
	session_start();
?>
<html>
	<head>
		<title>Journal Incubator</title>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/main.css">
				
		<script src='js/jquery-2.1.0.min.js'></script>
		<script src='js/bootstrap.min.js'></script>
	</head>
	<body>
		<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" href="#">Journal Incubator</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
<<<<<<< HEAD
						<li><a href="#">Temp</a></li>
=======
						<li><a href="home.php"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></a></li>
>>>>>>> LogansBranch
						<li><a href="#">Temp 2</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li id="account-dropdown" class="dropdown">
							<?php if(!isset($_SESSION['ojs_username'])) {echo '<a href="login.php">Log In</a>';} 
								  else { echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">
			  	  						<span class="glyphicon glyphicon-user"></i> <b>'.$_SESSION['ojs_username'].'</b> <span class="caret"></span>
			  	  						<ul class="dropdown-menu">';
										  	echo '<li id="logoutLink"><a href="login.php">Logout</a></li>
										  </ul>';

									}?>
						</li>
					</ul>
				</div>
			</div>
		</nav>
		<div id="wrapper">
			<div class="container">