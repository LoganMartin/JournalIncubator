<?php 
	require("database_ctrl.php");
		
	if(isset($_POST['action']) && !empty($_POST['action'])) {
		$action = $_POST['action'];
		switch($action) {
			case 'getArticleInfo': 	getArticleInfo(); break;
			case 'submitEvent': 	submitEvent(); break;
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
		
				$reviewer .= "<p>".$row['first_name'];
				$reviewer .= $row['last_name']."</p></br>";
			
			if($row['date_notified']!=NULL){$reviewer .= "<p>Request: ".$row['date_notified']."</p></br>";}
			//Original page has button to send email here
			if($row['date_confirmed']!=NULL){$reviewer .= "<p>Underway: ".$row['date_confirmed']."</p></br>";}
			if($row['date_due']!=NULL){$reviewer .= "<p>Due: ".date("D, M d, Y",strtotime($row['date_due']))."</p><span class='glyphicon glyphicon-calendar' aria-hidden='true'></span></br>";}
			if($row['date_acknowledged']!=NULL){$reviewer .= "<p>Acknowledge: ".$row['date_acknowledged']."</p></br>";}
			
			if($row['recommendation']!=NULL){$reviewer .= "<p>Recommendation: ".$row['recommendation']."</p></br>";}
			//Need to find corrosponding data for these instead of just values
			
			
			$comments= getComments($articleID, $row['user_id']);
			if($comments!=NULL){$reviewer .= "<p>Review: ". $comments."</p></br>";}
				$reviewer .= "</div>";
				//need buttons asking whether or not reviewer will accept, 
				//if accepted, 'underway' changes to current date/time
				
				//Need 'Recommendation'
				//Need 'review'
				//Need 'uploaded files'
				//Need 'reviewer rating'
				$reviewer_number += 1;
			}
		}
		return $reviewer;
	}
	
	//function to grab the comments for a specific review from a specific user
	function getComments($articleID, $userID){
		global $connection;
		
		//run query
		return 5; //temp	
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
			$table .= '<tr><td>'.$row['date_logged'].'</td>';
			$table .= '<td>'.$row['first_name'].' '.$row['last_name'].'</td>';
			$table .= '<td>'.$row['message'].'</td></tr>';
		}
						
		$table .= "</tbody></table>";
		
		return $table;
	}
	
	function submitEvent() {
		$event = $_POST['eventText'];
		$articleID = $_POST['articleID'];
		$userID = $_POST['userID'];
		$clientIP =  $_SERVER['REMOTE_ADDR'];
		$datetime = date("Y-m-d h:i:s");
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

?>