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
<div>
	<h1>Active Articles</h1>
</div>
<div role="tabpanel">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#unassignedTab" aria-controls="unassignedTab" role="tab" data-toggle="tab">Unassigned <span class="badge"><?php echo getEditNum(); ?></span></a></li>
    <li role="presentation"><a href="#reviewTab" aria-controls="reviewTab" role="tab" data-toggle="tab">In Review</a></li>
    <li role="presentation"><a href="#editTab" aria-controls="editTab" role="tab" data-toggle="tab">In Editing</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="unassignedTab"><?php echo getEditorJournals();?></div>
    <div role="tabpanel" class="tab-pane" id="reviewTab">.fdafdas</div>
    <div role="tabpanel" class="tab-pane" id="editTab">.fdafdafdafdafda.</div>
  </div>

</div>

<script src='js/jquery.tablesorter.min.js'></script>
<script src='js/home.js'></script>
<?php 
	echo file_get_contents('footer.php');
?>