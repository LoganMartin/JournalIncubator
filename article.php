<?php
/**
 * article.php
 * contains html elements of the article page.
 */ 
	include('header.php');
	include('controllers/article_ctrl.php');
	
	//Makes sure a user is logged in, else it redirects to the login page.
	if(!isset($_SESSION['ojs_username'])) {header("Location: login.php");}
	if(!isset($_GET['id'])) {header("Location: home.php");}
?>
<link rel="stylesheet" type="text/css" href="css/article.css">
<link rel="stylesheet" type="text/css" href="css/tablesorter/style.css">
<div>
	<h1>Article #<?php echo $_GET['id']; ?></h1>
	
	<?php $article = getArticleInfo($_GET['id']); ?>
	<div id="submission-container">
		<h3>Submission</h3>
		<div class="sub-field">
			<p>Authors:</p>
			<p><?php echo $article['authors']; ?></p>
		</div>
		<div class="sub-field">
			<p>Title:</p>
			<p><?php echo $article['title']; ?></p>
		</div>
		<div class="sub-field">
			<p>Editor:</p>
			<p><?php echo $article['editor'];?></p>
		</div>
	</div>
	
	<div id="timeline-container" class="well">
		<h3 id="timeline-header">Review Timeline</h3>
		<div id="table-container">
			<?php echo getArticleTimeline($_GET['id']); ?>
		</div>
	</div>
</div>

<script src='js/jquery.tablesorter.min.js'></script>
<script src='js/article.js'></script>
<?php 
	echo file_get_contents('footer.php');
?>