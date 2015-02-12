<?php 
	include('header.php');
	include('controllers/home_ctrl.php');
	
	//Makes sure a user is logged in, else it redirects to the login page.
	if(!isset($_SESSION['ojs_username'])) {header("Location: login.php");}
	
	
//needed for page still:
//	click on row to go to next page
//	fill table with correct info
?>
<link rel="stylesheet" type="text/css" href="css/home.css">
<div class="page-header">
	<h1>Active Articles</h1>
</div>
<div role="tabpanel">
	<?php echo getUsersTabs(); ?>
</div>
<script src='js/jquery.tablesorter.min.js'></script>
<script src='js/home.js'></script>
<?php 
	echo file_get_contents('footer.php');
?>