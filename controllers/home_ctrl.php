<?php
	require("database_ctrl.php");
//	require("../../interfacer.php");
	
	//Do not change, or everything will break.
	//Allows for communication with OJS code.
	
	//Takes function name 'action' specified by ajax, and executes said function.
	if(isset($_POST['action']) && !empty($_POST['action'])) {
		$action = $_POST['action'];
		switch($action) {
			case 'getJournals': 		getJournals(); break;
			default: 			break;
		}
	}
	
	
	function getJournals() {
		
		/**
		 * Since OJS sucks, it decides to give an arbatrary ass number to identify roles, making everything a million times harder to follow
		 * here's the following role_id's and their actual damn role 
		 * 256 = Editor
		 */
		 
		global $connection;
		$username = $_SESSION['ojs_username'];
		//below is grabbing from the database
		$select = "SELECT * FROM users INNER JOIN roles ON users.user_id=roles.user_id
										INNER JOIN article_comments ON roles.role_id=article_comments.role_id
										
			WHERE users.username = '$username'";
		
		//INNER JOIN article_settings ON article_comments.article_id=article_settings.article_id
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		$tableData = "<table id = 'results-table' class='table table-striped table-hover'>
						<thead>
							<tr>
								<th>Journal Title</th>
								<th>Author</th>
								<th>Date Submitted</th>
							</tr>
						</thead>
						<tbody>";
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$tableData .= '<tr><td>'.$row['user_id'].'</td>';
			$tableData .= '<td>'.$row['author_id'].'</td></tr>';
		}
		echo $tableData;
	}

	function getEditorJournals() {
		global $connection;
		$userID = $_SESSION['ojs_userID'];
		
		$select = "SELECT * FROM edit_assignments e 
						INNER JOIN articles a ON e.article_id=a.article_id
						INNER JOIN edit_decisions d ON a.article_id=d.article_id
						WHERE e.editor_id=".$_SESSION['ojs_userID']."
						AND d.decision <> 1";
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		$table = "<table id='review-table' class='table tablesorter table-striped table-hover'>
						<thead>
							<tr>
								<th width='10%'>ID</th>
								<th width='30%'>User</th>
								<th width='60%'>Submitted</th>
							</tr>
						</thead>
						<tbody>";
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$table .= "<tr onclick='openArticle(".$row['article_id'].")'>";
			$table .='<td>'.$row['article_id'].'</td>';
			$table .= '<td>'.$row['user_id'].'</td>';
			$table .= '<td>'.$row['date_submitted'].'</td></tr>';
		}
						
		$table .= "</tbody></table>";
		return $table;
	}

	//I did this super lazy copy paste job, should probably redo in a better way if we have time.
	function getEditNum() {
		global $connection;
		$userID = $_SESSION['ojs_userID'];
		
		$select = "SELECT * FROM edit_assignments e 
						INNER JOIN articles a ON e.article_id=a.article_id
						INNER JOIN edit_decisions d ON a.article_id=d.article_id
						WHERE e.editor_id=".$_SESSION['ojs_userID']."";
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		return mysql_num_rows($result);
	}

	

?>