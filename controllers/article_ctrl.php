<?php 
	require("database_ctrl.php");
		
	if(isset($_POST['action']) && !empty($_POST['action'])) {
		$action = $_POST['action'];
		switch($action) {
			case 'getArticleInfo': 	getArticleInfo(); break;
			case 'submitEvent': 	submitEvent($_POST['userID'], $_POST['articleID'], $_POST['eventText']); break;
			case 'assignEditor': 	assignEditor(); break;
			case 'ajaxGetTimeline':  getArticleTimeline($_POST['articleID']); break;
			case 'ajaxGetStatus':  echo getCurrentStatus($_POST['articleID']); break;
			default: 			break;
		}
	}
	
	function getArticleInfo($articleID) {
		global $connection;
		$author = "";
		$editors = "";
		$title = "";
		
		//Get article's author information
		$select = "SELECT * FROM authors WHERE submission_id = $articleID ORDER BY seq";
					
		if(!$authResult = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		while($row = mysql_fetch_array($authResult, MYSQL_ASSOC)) {
			$author .= $row['first_name']." ";
			$author .= $row['middle_name']." ";
			$author .= $row['last_name'].", ";
		}
		
		$author = rtrim($author, ', '); //Removes comma at end of string.
		
		//Get articles editor information
		$select = "SELECT * FROM edit_assignments a INNER JOIN users ON a.editor_id=users.user_id 
					WHERE article_id = $articleID";
					
		if(!$editResult = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		while($row = mysql_fetch_array($editResult, MYSQL_ASSOC)) {
			$editors .= $row['first_name']." ";
			$editors .= $row['last_name'].", ";
		}
		
		$editors = rtrim($editors, ', '); //Removes comma at end of string.		
		
		//Get other article information
		$select = "SELECT * FROM articles a
					INNER JOIN article_settings s ON a.article_id=s.article_id			 
					WHERE a.article_id = $articleID";
					
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			if($row['setting_name'] == "title") {
				$title = $row['setting_value'];
			}
			
		}
		
		$data['title'] = $title;
		$data['authors'] = $author;
		$data['editor'] = $editors;
		return $data;
	}
	
	function getReviewerInfo($articleID){
		global $connection;
		$reviewer = "";
		$reviewer_number = 0;
		$reviewer_letter = array("A", "B", "C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q");
		//Get article's reviewer information
		$select = "SELECT * FROM review_assignments a INNER JOIN users  b ON a.reviewer_id=b.user_id WHERE submission_id = $articleID";
					
		if(!$authResult = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		while($row = mysql_fetch_array($authResult, MYSQL_ASSOC)) {
		if($row['cancelled']=="0"){
				$reviewer .= "<h3>Reviewer ".$reviewer_letter[$reviewer_number] ."</h3>
							<div class='sub-field'>
							<p>Reviewer:</p>";
		
				$reviewer .= "<p>".$row['first_name']." ";
				$reviewer .= $row['last_name']."</p></div>";
			
			if($row['date_notified']!=NULL){
				$temp = date("M d, Y g:ia",strtotime($row['date_notified']));
			}
			else {
				$temp = "-";
			}
			
			$reviewer .= "<div class='sub-field'><p>Request:</p><p>".$temp."</p></div>";
						
			if($row['date_confirmed']!=NULL){
				$temp = date("M d, Y g:ia",strtotime($row['date_confirmed']));
			}
			else {
				$temp = "-";
			}
			$reviewer .= "<div class='sub-field'><p>Underway:</p><p>".$temp."</p></div>";
			
			if($row['date_due']!=NULL){
				$temp = date("D, M d, Y",strtotime($row['date_due']));
			}
			else {
				$temp = "-";
			}
			$reviewer .= "<div class='sub-field'><p>Due By:</p><p>".$temp."<span class='glyphicon glyphicon-calendar' aria-hidden='true'></span></p></div>";
			
			if($row['recommendation']!=NULL){
				switch($row['recommendation']) {
					case 1: 
						$recommendation = "Accept Submission";
						break;	
					case 2:
						$recommendation = "Revisions Required"; 
						break;
					case 3: 
						$recommendation = "Resubmit for Review";
						break;
					case 4: 
						$recommendation = "Resubmit Elsewhere";
						break;
					case 5: 
						$recommendation = "Decline Submission";
						break;
					case 6: 
						$recommendation = "See Comments";
						break;
					default:
						break;
				}
			}
			else {
				$recommendation = "-";
			}
			$reviewer .= "<div class='sub-field'><p>Recommendation:</p><p>".$recommendation."</p></div>";
			//Need to find corrosponding data for these instead of just values
				$reviewer_number += 1;
			}
		}
		return $reviewer;
	}
	
	function getArticleTimeline($articleID) {
		global $connection;
		
		$select = "SELECT * FROM event_log INNER JOIN users ON event_log.user_id=users.user_id 
					WHERE assoc_id = $articleID";
					
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		$table = "<table id='timeline-table' class='table tablesorter table-striped table-hover'>
						<thead>
							<tr>
								<th width='10%'>Date</th>
								<th width='30%'>User</th>
								<th width='60%'>Event</th>
							</tr>
						</thead>
						<tbody>";
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$table .= '<tr><td>'.date("M d, Y H:i",strtotime($row['date_logged'])).'</td>';
			$table .= '<td>'.$row['first_name'].' '.$row['last_name'].'</td>';
			$table .= '<td>'.htmlspecialchars($row['message']).'</td></tr>';
		}
						
		$table .= "</tbody></table>";
		
		echo $table;
	}
	
	/**
	 * Submits an event to the event_log table in the ojs database.
	 * May be a manual event update, done from our interface. In this case, we have a post variable edNote.
	 * @param $userID the id of the user who is responsible for the event.
	 * @param $articleID the id number of the article which the event is for.
	 * @param $event a string containing the event details.
	 */
	function submitEvent($userID, $articleID, $event) {
		if(isset($_POST['edNote'])) {
			$event = "Editors Note: ".$event; //Add Editors note: to the start of a user submitted event, as requested by PO.
		}
		$event = mysql_real_escape_string($event); //Escape special characters for INSERTING.
		$clientIP =  $_SERVER['REMOTE_ADDR'];
		$datetime = date("Y-m-d H:i:s");
		global $connection;
		
		
		$insert = "INSERT INTO event_log (assoc_type, assoc_id, user_id, date_logged, ip_address, message, is_translated)
					VALUES (257, $articleID, $userID, '$datetime', '$clientIP', '$event', 1)";
		
		if(!mysql_query($insert, $connection)) {
			die('Error:'.mysql_error());
		}

		$alert = "<div class='alert alert-success alert-dismissible' role='alert'>
					<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
						<span aria-hidden='true'>&times;</span></button>
					<strong>Success!</strong> Your event message has been recorded.</div>";
		if(isset($_POST['edNote'])) {echo $alert;}
	}
	
	
	/**
	 * This function determines what part of the submission process an article is at in the workflow, and generates the relevant information to display.
	 * Since OJS's database is so poorly put together, we're unable to simply check the status of an article in the database,
	 * we have to run a series of queries in order to figure out what steps it has and hasn't gone through yet.
	 * @param $articleID The id of the article which we are getting the status for.
	 * @return string a string of html code that is displayed in the "current status" of an article
	 */
	function getCurrentStatus($articleID) {
		global $connection;
		$HTMLStatus = "<h3 id='status-header'><b>Current Status: </b>";
		
		//First let's check if the article has an editor assigned.
		$select = "SELECT * FROM edit_assignments WHERE article_id = $articleID";
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		if(mysql_num_rows($result) == 0) {
			$HTMLStatus .= "Awaiting Editor Assignment</h3>
				<p>This article needs to have an editor assigned to it! Open the article in OJS(link above) to assign an editor.</p>
				<button type='button' class='btn btn-primary btn-lg' data-toggle='modal' data-target='#article-modal'>Select an Editor</button>";
			$HTMLStatus .= generateModalByType("Editor");
			return $HTMLStatus;
		}
		
		//Now, let's see if the editor has accepted the submission, and pushed it to the editing phase.
		$select = "SELECT * FROM edit_decisions WHERE article_id = $articleID AND decision = 1";
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		if(mysql_num_rows($result) != 0) {
			$HTMLStatus .= "In Editing</h3>
				<p>The article has passed the review process, and is now in editing.</p>";
			return $HTMLStatus;
		}
		
		//Next, let's check if there are any reviewers assigned to the article.
		$select = "SELECT * FROM review_assignments WHERE submission_id = $articleID";
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		if(mysql_num_rows($result) == 0) {
			$HTMLStatus .= "Waiting for Reviewer/Referee Assignment</h3>
				<p>This article needs to be assigned reviewers! Open the article in OJS(link above) to assign them.</p>
				<button type='button' class='btn btn-primary btn-lg' data-toggle='modal' data-target='#article-modal'>Select Reviewers</button>";
			$HTMLStatus .= generateModalByType("Reviewer");
			return $HTMLStatus;
		}
		
		//Now, let's check if there are any reviewers who haven't made their decision yet..
		$select = "SELECT * FROM review_assignments WHERE submission_id = $articleID AND recommendation IS NOT NULL";
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		if(mysql_num_rows($result) == 0) {
			$HTMLStatus .= "Waiting for Reviewer Decisions</h3>
				<p>We're still waiting for some reviewers to make a decision.</p>";
			$HTMLStatus .= getReviewerInfo($articleID);
			return $HTMLStatus;
		}
		else {
			$HTMLStatus .= "Awaiting Editor Decision</h3>
				<p>The reviewers have all submitted their feedback, now we need an editors decision.</p>";
			$HTMLStatus .= getReviewerInfo($articleID);
			return $HTMLStatus;
		}
		
		
		$HTMLStatus .= "Unknown</h3>";
		return $HTMLStatus;
	}
	
	/**
	 * Generates a modal, that will dropdown and allow the user to perform certain tasks, such as assign editors
	 * or reviewers to the article.
	 * @param string $modalType A string that indicates what type of modal shall be generated
	 * @return string $modal A string of HTML code for the modal.
	 */
	function generateModalByType($modalType) {
		global $connection;	
		$modalContent = "";	
		switch($modalType) {
			case "Editor":
				$title = "Select an Editor to assign:";
				
				//Gets a list of all users who are editors
				$select = "SELECT * FROM roles r INNER JOIN users u ON r.user_id=u.user_id 
					WHERE r.role_id = 256";
				if(!$result = mysql_query($select, $connection)) {
					die('Error:'.mysql_error());
				}
				$oddRow = true; //Used to color every other row slightly differently, for visibility purposes
				while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					$modalContent .= "<div class='modal-row";
					if($oddRow) $modalContent .= " odd-row";
					$name = $row['first_name']." ".$row['last_name'];
					$name = mysql_real_escape_string($name);
					$modalContent .= "'><div class='modal-col'><p>".$row['first_name']." ".$row['last_name']."</p></div>
										<div class='modal-col'>
											<button id='assign".$row['user_id']."' class='btn btn-success assign-btn'";
												$modalContent .= "onclick=\"assignEditor(".$row['user_id'].", '$name')\">
												Assign
											</button>
										</div>
									  </div>";
					if($oddRow) $oddRow = false;
					else $oddRow = true;
				}
				
				break;
			case "Reviewer":
				$title = "Select a Reviewer to assign:";
				$modalContent = "testing Reviewers";
				break;
			default:
				break;
		}
		$modal = "<div class='modal fade' id='article-modal' tabindex='-1' role='dialog' aria-labelledby='article-modal-label' aria-hidden='true'>
					<div class='modal-dialog'>
						<div class='modal-content'>
							<div class='modal-header'>
								<button type='button' class='close' data-dismiss='modal' aria-label='Close'>
									<span aria-hidden='true'>&times;</span></button>
								<h4 class='modal-title' id='article-modal-label'>$title</h4>
							</div>
							<div class='modal-body'>$modalContent</div>
							<div class='modal-footer'>
								<button type='button' class='btn btn-primary' data-dismiss='modal'>Close</button>
							</div>
						</div>
					</div>
				  </div>";
		return $modal;
	}
	
	/**
	 * Takes a user id, and article id, and creates an edit_assignment in the database using those values.
	 * Get's it's "paramaters" from an ajax call.
	 * @return string a message indicating either success or failure
	 */
	function assignEditor() {
		$userID = $_POST['userID'];
		$editorID = $_POST['editorID'];
		$articleID = $_POST['articleID'];
		$userName = $_POST['userName'];		
		$datetime = date("Y-m-d H:i:s");
		global $connection;
		
		$insert = "INSERT INTO edit_assignments (article_id, editor_id, date_notified)
					VALUES ($articleID, $editorID, '$datetime')";
		
		if(!mysql_query($insert, $connection)) {
			die('Error:'.mysql_error());
		}
		
		//Submit a new event log, indicating the new editor assignment.
		$eventLog = "$userName has been assigned as an editor to submission $articleID.";
		submitEvent($userID, $articleID, $eventLog);
		
		$alert = "<div class='alert alert-success alert-dismissible' role='alert'>
					<button type='button' class='close' data-dismiss='alert' aria-label='Close'>
						<span aria-hidden='true'>&times;</span></button>
					<strong>Success!</strong> Editors have been assigned to the article</div>";
		echo $alert;
	}
?>