<?php 
	session_start();
?>
<html>
	<head>
		<title>Music Store!</title>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="css/main.css">
				
		<script src='js/jquery-2.1.0.min.js'></script>
		<script src='js/bootstrap.min.js'></script>
	</head>
	<body>
		<nav class="navbar navbar-default" role="navigation">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" href="library.php">Music!</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li><a href="library.php">My Library</a></li>
						<li><a href="store.php">Store</a></li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li id="account-dropdown" class="dropdown">
							<?php if(!isset($_SESSION['username'])) {echo '<a href="login.php">Log In</a>';} 
								  else { echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">
			  	  						<span class="glyphicon glyphicon-user"></i> <b>'.$_SESSION['username'].'</b> <span class="caret"></span>
			  	  						<ul class="dropdown-menu">';
											if($_SESSION['usertype'] == "admin") {
												echo '<li id="accountLink"><a href="managestore.php">Manage Store</a></li>';
											}
											echo '<li id="accountLink"><a href="managewallet.php">Manage Account</a></li>';
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