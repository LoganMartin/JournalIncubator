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
<div>
	<h1>Article #<?php echo $_GET['id']; ?></h1>
	<div id="ojs-link"><a href="../index.php/digital_studies/editor/submissionReview/<?php echo $_GET['id']; ?>" target="_blank">Open Article in OJS<span id="ojs-icon" class="glyphicon glyphicon-share-alt" aria-hidden="true"></span></a></div>
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
	
	
	<div id="reviewer-container">
		<?php echo getReviewerInfo($_GET['id']); ?>
	</div>
	
	<div id="events-container sub-field">
		<div id="timeline-container" class="well">
			<h3 id="timeline-header">Review Timeline</h3>
			<div id="table-container">
				<?php echo getArticleTimeline($_GET['id']); ?>
			</div>
		</div>
		<div id="event-form">
			<div id="event-alert-div" class="hidden"></div>
			<p>Add New Event:</p>
			<textarea id="articleEvent"></textarea>
			<button id="event-submit-button" class="btn btn-primary pull-right" onclick="submitEvent()">Submit</button>
		</div>
	</div>
</div>

<script src='js/jquery.tablesorter.min.js'></script>
<script> 
	var articleID = <?php echo $_GET['id']; ?>;
	var userID = <?php echo $_SESSION['ojs_userID']; ?>;
</script>
<script src='js/article.js'></script>
<?php 
	echo file_get_contents('footer.php');
?>