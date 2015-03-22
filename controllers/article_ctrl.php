<?php 
	require("database_ctrl.php");
		
	if(isset($_POST['action']) && !empty($_POST['action'])) {
		$action = $_POST['action'];
		switch($action) {
			case 'getArticleInfo': 	getArticleInfo(); break;
			case 'submitEvent': 	submitEvent(); break;
			case 'ajaxGetTimeline':  getArticleTimeline($_POST['articleID']); break;
			default: 			break;
		}
	}
	
	function getArticleInfo($articleID) {
		global $connection;
		$author = "";
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
		
		$editResults = mysql_fetch_array($editResult, MYSQL_ASSOC);
		$editor = $editResults['first_name']." ";
		$editor .= $editResults['middle_name']." ";
		$editor .= $editResults['last_name'];
		
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
		$data['editor'] = $editor;
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
	
	//Called using ajax in artile.js. Needed to pass a POST variable as a function paramater to getArticleTimeline().
	//There's probably a better way of doing this, but this is good enough for now.
	function ajaxGetTimeline() {
		echo getArticleTimeline($_POST['articleID']);
	}
	
	function submitEvent() {
		$event = "Editors Note: ".$_POST['eventText']; //Add Editors note: to the start of a user submitted event, as requested by PO.
		$event = mysql_real_escape_string($event); //Escape special characters for INSERTING.
		$articleID = $_POST['articleID'];
		$userID = $_POST['userID'];
		$clientIP =  $_SERVER['REMOTE_ADDR'];
		$datetime = date("Y-m-d H:i:s");
		global $connection;
		
		
		$insert = "INSERT INTO event_log (assoc_type, assoc_id, user_id, date_logged, ip_address, message, is_translated)
					VALUES (257, $articleID, $userID, '$datetime', '$clientIP', '$event', 1)";
		
		if(!mysql_query($insert, $connection)) {
			die('Error:'.mysql_error());
		}

		$alert = "<div class='alert alert-success alert-dismissible' role='alert'>
					<button type='button' class='close' data-dismissed='alert' aria-label='Close'>
						<span aria-hidden='true'>&times;</span></button>
					<strong>Success!</strong> Your event message has been recorded.</div>";
		echo $alert;
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
				<p>This article needs to have an editor assigned to it! Open the article in OJS(link above) to assign an editor.</p>";
				
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
				<p>This article needs to be assigned reviewers! Open the article in OJS(link above) to assign them.</p>";
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

?>