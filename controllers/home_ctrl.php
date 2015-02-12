<?php
	require("database_ctrl.php");

	//Takes function name 'action' specified by ajax, and executes said function.
	if(isset($_POST['action']) && !empty($_POST['action'])) {
		$action = $_POST['action'];
		switch($action) {
			case 'getJournals': 		getJournals(); break;
			default: 			break;
		}
	}
	
	
	function getJournals() {
		 
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

	function getUsersTabs() {
		global $connection;
		$count = 0;
		$tabHTML = "<ul class='nav nav-pills' role='tablist'>";
		$contentHTML = "<div class='tab-content'>";
		
		$select = "SELECT * FROM roles WHERE user_id=".$_SESSION['ojs_userID'];
		
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		
		//Loops through each role assigned to a user, and generates a tab to hold the relevant articles.
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			switch($row['role_id']) {
				//Since OJS sucks, it decides to give an arbitrary ass number to identify roles. 
				case 256: //Editor
					$roleTab = "editorTab";
					$roleName = "Editor";
					break;
				case 512: //Section Editor
					$roleTab = "sectionEditorTab";
					$roleName = "Section Editor";
					break;
				case 768: //Layout Editor
					$roleTab = "layoutEditorTab";
					$roleName = "Layout Editor";
					break;
				case 4096: //Reviewer
					$roleTab = "reviewerTab";
					$roleName = "Reviewer";
					break;
				case 8192: //Copy Editor
					$roleTab = "copyEditorTab";
					$roleName = "Copy Editor";
					break;
				case 12288: //Proofreader
					$roleTab = "proofreaderTab";
					$roleName = "Proofreader";
					break;
				case 65536: //Author
					$roleTab = "authorTab";
					$roleName = "Author";
					break;
				default: //Debugging purposes
					$roleTab = "errorTab";
					$roleName = "Error";
					break;
			}
			if($row['role_id'] != 16 && $row['role_id'] != 1048576) { //These roles don't need tables of articles to be displayed
				$tabHTML .= "<li roll='presentation'"; if($count==0) {$tabHTML .= "class='active'";}
				$tabHTML .= "><a href='#$roleTab' aria-controls='$roleTab' role='tab' data-toggle='pill'>$roleName <span class='badge'>".getEditNum()."</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane ";  if($count==0) {$contentHTML .= "active";}
				$contentHTML .= "' id='$roleTab'>".generateArticleTabs($row['role_id'], $count)."</div>";
				$count++;
			}
		}
		
		$html = $tabHTML."</ul>".$contentHTML."</div>";
		return $html;
	}
	
	/**
	 * Needs to be completely rewritten if there's time. There has to be a more dynamic way to determine what states an article is in
	 * based on the role you have with it.
	 */
	function generateArticleTabs($role, $count) {
		$tabHTML = "<ul class='nav nav-tabs' role='tablist' id='theTabs$count'>";
		$contentHTML = "<div class='tab-content status-tab-content'>";
		
		switch($role) {
			case 256: //Editor
				$tabHTML .= "<li role='presentation' class='active'><a href='#unassignedTab".$count."' aria-controls='unassignedTab$count' role='tab' data-toggle='tab'>Unassigned <span class='badge'>#</span></a></li>";
				$tabHTML .= "<li role='presentation'><a href='#reviewTab".$count."' aria-controls='reviewTab$count' role='tab' data-toggle='tab'>In Review <span class='badge'>#</span></a></li>";
				$tabHTML .= "<li role='presentation'><a href='#editTab".$count."' aria-controls='editTab$count' role='tab' data-toggle='tab'>In Editing <span class='badge'>#</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='unassignedTab$count'>".getUnassignedArticles()."</div>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane' id='reviewTab$count'>".getReviewArticles()."</div>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane' id='editTab$count'>".getEditArticles()."</div>";
				break;
			case 512: //Section Editor
				$tabHTML .= "<li role='presentation' class='active'><a href='#reviewTab".$count."' aria-controls='reviewTab$count' role='tab' data-toggle='tab'>In Review <span class='badge'>#</span></a></li>";
				$tabHTML .= "<li role='presentation'><a href='#editTab".$count."' aria-controls='editTab$count' role='tab' data-toggle='tab'>In Editing <span class='badge'>#</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='reviewTab$count'>".getReviewArticles()."</div>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane' id='editTab$count'>".getEditArticles()."</div>";
				break;
			case 768: //Layout Editor
				$tabHTML .= "<li role='presentation' class='active'><a href='#editTab".$count."' aria-controls='editTab$count' role='tab' data-toggle='tab'>In Editing <span class='badge'>#</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='editTab$count'>".getEditArticles()."</div>";
				break;
			case 4096: //Reviewer
				$tabHTML .= "<li role='presentation' class='active'><a href='#unassignedTab".$count."' aria-controls='unassignedTab$count' role='tab' data-toggle='tab'>Active <span class='badge'>#</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='unassignedTab$count'>".getEditorJournals()."</div>";
				break;
			case 8192: //Copy Editor
				$tabHTML .= "<li role='presentation' class='active'><a href='#editTab".$count."' aria-controls='editTab$count' role='tab' data-toggle='tab'>In Editing <span class='badge'>#</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='editTab$count'>".getEditArticles()."</div>";
				break;
			case 12288: //Proofreader
				$tabHTML .= "<li role='presentation' class='active'><a href='#editTab".$count."' aria-controls='editTab$count' role='tab' data-toggle='tab'>In Editing <span class='badge'>#</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='editTab$count'>".getEditArticles()."</div>";
				break;
			case 65536: //Author
				$tabHTML .= "<li role='presentation' class='active'><a href='#unassignedTab".$count."' aria-controls='unassignedTab$count' role='tab' data-toggle='tab'>Active <span class='badge'>#</span></a></li>";
				$tabHTML .= "<li role='presentation'><a href='#reviewTab".$count."' aria-controls='reviewTab$count' role='tab' data-toggle='tab'>Archive <span class='badge'>#</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='unassignedTab$count'>".getEditorJournals()."</div>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane' id='reviewTab$count'>Review</div>";
				break;
			default:
				break;
		}
		$html = $tabHTML."</ul>".$contentHTML."</div>";
		return $html;
	}

	function getUnassignedArticles() {
		global $connection;
		$userID = $_SESSION['ojs_userID'];
		
		$select = "SELECT Distinct A1.article_id as ID,CONCAT(LPAD(MONTH(A1.date_submitted),2,'0'),'-',DAYOFMONTH(A1.date_submitted)) as SUBMIT,U1.last_name as Author
					 ,(select stpl.setting_value from section_settings where section_id=A1.section_id and setting_name='abbrev') as SEC
					,(select distinct actpl.setting_value from article_settings where setting_name='title' and article_id=A1.article_id and setting_value <>'') as Title
					FROM 		articles A1 LEFT JOIN authors aa ON (aa.submission_id = A1.article_id)
					LEFT JOIN authors aap ON (aap.submission_id = A1.article_id AND aap.primary_contact = 1)
					LEFT JOIN sections s ON (s.section_id = A1.section_id)
					LEFT JOIN edit_assignments E1 on E1.article_id=A1.article_id 
					LEFT JOIN users U1 on A1.USER_id= U1.user_id
					LEFT JOIN review_assignments r ON (r.submission_id = A1.article_id)
					LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = 'abbrev' )
					LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title' )
					LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'policy' )
					LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'identifyType' )
					LEFT JOIN article_settings actpl ON (A1.article_id = actpl.article_id AND actpl.setting_name ='title' )
					LEFT JOIN article_settings actl ON (A1.article_id = actl.article_id AND actl.setting_name = 'cleanTitle' )
					LEFT JOIN edit_assignments ea ON (A1.article_id = ea.article_id)
					 LEFT JOIN edit_assignments ea2 ON (A1.article_id = ea2.article_id AND ea.edit_id < ea2.edit_id)
					LEFT JOIN edit_decisions edec ON (A1.article_id = edec.article_id)
					LEFT JOIN edit_decisions edec2 ON (A1.article_id = edec2.article_id AND edec.edit_decision_id < edec2.edit_decision_id)
					where A1.status = 1
					AND ea.edit_id IS NULL
					AND A1.submission_progress = 0 
					 
					 -- and E1.editor_id='$userID'  
					 order by A1.article_id Desc;";
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		$table = "<table id='review-table' class='table tablesorter table-striped table-hover'>
						<thead>
							<tr>
								<th width='10%'>ID</th>
								<th width='30%'>Author</th>
								<th width='60%'>Title</th>
							</tr>
						</thead>
						<tbody>";
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$table .= "<tr onclick='openArticle(".$row['ID'].")'>";
			$table .='<td>'.$row['ID'].'</td>';
			$table .= '<td>'.$row['Author'].'</td>';
			$table .= '<td>'.$row['Title'].'</td></tr>';
		}
						
		$table .= "</tbody></table>";
		return $table;
	}
	
	function getReviewArticles() {
		global $connection;
		$userID = $_SESSION['ojs_userID'];
		
		$select = "SELECT Distinct A1.article_id as ID,CONCAT(LPAD(MONTH(A1.date_submitted),2,'0'),'-',DAYOFMONTH(A1.date_submitted)) as SUBMIT,U1.last_name as Author
					 ,(select stpl.setting_value from section_settings where section_id=A1.section_id and setting_name='abbrev') as SEC
					,(select distinct actpl.setting_value from article_settings where setting_name='title' and article_id=A1.article_id and setting_value <>'') as Title
					FROM 		articles A1 LEFT JOIN authors aa ON (aa.submission_id = A1.article_id)
					LEFT JOIN authors aap ON (aap.submission_id = A1.article_id AND aap.primary_contact = 1)
					LEFT JOIN sections s ON (s.section_id = A1.section_id)
					LEFT JOIN edit_assignments E1 on E1.article_id=A1.article_id 
					LEFT JOIN users U1 on A1.USER_id= U1.user_id
					LEFT JOIN review_assignments r ON (r.submission_id = A1.article_id)
					LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = 'abbrev'  AND stpl.locale = A1.locale)
					LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title'  AND stl.locale = A1.locale)
					LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'policy'  AND sapl.locale =A1.locale)
					LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'identifyType'  AND sal.locale = A1.locale)
					LEFT JOIN article_settings actpl ON (A1.article_id = actpl.article_id AND actpl.setting_name ='title'  AND actpl.locale = A1.locale)
					LEFT JOIN article_settings actl ON (A1.article_id = actl.article_id AND actl.setting_name = 'cleanTitle'  AND actl.locale = A1.locale)
					LEFT JOIN article_settings atpl ON (A1.article_id = atpl.article_id AND atpl.setting_name = 'sponsor'  AND atpl.locale = A1.locale)
					LEFT JOIN article_settings atl ON (A1.article_id = atl.article_id AND atl.setting_name = 'abstract' AND atl.locale = A1.locale)
					LEFT JOIN edit_assignments ea ON (A1.article_id = ea.article_id)
					LEFT JOIN edit_assignments ea2 ON (A1.article_id = ea2.article_id AND ea.edit_id < ea2.edit_id)
					LEFT JOIN edit_decisions edec ON (A1.article_id = edec.article_id)
					LEFT JOIN edit_decisions edec2 ON (A1.article_id = edec2.article_id AND edec.edit_decision_id < edec2.edit_decision_id)
					where A1.status = 1-- '.STATUS_QUEUED.' 
					AND ea.edit_id IS NOT NULL
					AND (edec.decision IS NULL OR edec.decision <> 1 )
					AND A1.journal_id=2
					AND edec2.edit_decision_id IS NULL
					AND ea2.edit_id IS NULL
					
					--  and E1.editor_id='21'  
					 order by A1.article_id Desc;";
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		$table = "<table id='review-table' class='table tablesorter table-striped table-hover'>
						<thead>
							<tr>
								<th width='10%'>ID</th>
								<th width='30%'>Author</th>
								<th width='60%'>Title</th>
							</tr>
						</thead>
						<tbody>";
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$table .= "<tr onclick='openArticle(".$row['ID'].")'>";
			$table .='<td>'.$row['ID'].'</td>';
			$table .= '<td>'.$row['Author'].'</td>';
			$table .= '<td>'.$row['Title'].'</td></tr>';
		}
						
		$table .= "</tbody></table>";
		return $table;
	}
	
	function getEditArticles() {
		global $connection;
		$userID = $_SESSION['ojs_userID'];
		
		$select = "SELECT Distinct A1.article_id as ID,CONCAT(LPAD(MONTH(A1.date_submitted),2,'0'),'-',DAYOFMONTH(A1.date_submitted)) as SUBMIT,U1.last_name as Author
					 ,(select stpl.setting_value from section_settings where section_id=A1.section_id and setting_name='abbrev') as SEC
					,(select distinct actpl.setting_value from article_settings where setting_name='title' and article_id=A1.article_id and setting_value <>'') as Title
					FROM 		articles A1 LEFT JOIN authors aa ON (aa.submission_id = A1.article_id)
					LEFT JOIN authors aap ON (aap.submission_id = A1.article_id AND aap.primary_contact = 1)
					LEFT JOIN sections s ON (s.section_id = A1.section_id)
					LEFT JOIN edit_assignments E1 on E1.article_id=A1.article_id 
					LEFT JOIN users U1 on A1.USER_id= U1.user_id
					LEFT JOIN review_assignments r ON (r.submission_id = A1.article_id)
					LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = 'abbrev'  AND stpl.locale = A1.locale)
					LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title'  AND stl.locale = A1.locale)
					LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'policy'  AND sapl.locale =A1.locale)
					LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'identifyType'  AND sal.locale = A1.locale)
					LEFT JOIN article_settings actpl ON (A1.article_id = actpl.article_id AND actpl.setting_name ='title'  AND actpl.locale = A1.locale)
					LEFT JOIN article_settings actl ON (A1.article_id = actl.article_id AND actl.setting_name = 'cleanTitle'  AND actl.locale = A1.locale)
					LEFT JOIN article_settings atpl ON (A1.article_id = atpl.article_id AND atpl.setting_name = 'sponsor'  AND atpl.locale = A1.locale)
					LEFT JOIN article_settings atl ON (A1.article_id = atl.article_id AND atl.setting_name = 'abstract' AND atl.locale = A1.locale)
					LEFT JOIN edit_assignments ea ON (A1.article_id = ea.article_id)
					LEFT JOIN edit_assignments ea2 ON (A1.article_id = ea2.article_id AND ea.edit_id < ea2.edit_id)
					LEFT JOIN edit_decisions edec ON (A1.article_id = edec.article_id)
					LEFT JOIN edit_decisions edec2 ON (A1.article_id = edec2.article_id AND edec.edit_decision_id < edec2.edit_decision_id)
					where A1.status =1
					 AND ea.edit_id IS NOT NULL
					 AND edec.decision = 1
					 -- AND E1.editor_id='21'  
					 order by A1.article_id Desc;";
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		$table = "<table id='review-table' class='table tablesorter table-striped table-hover'>
						<thead>
							<tr>
								<th width='10%'>ID</th>
								<th width='30%'>Author</th>
								<th width='60%'>Title</th>
							</tr>
						</thead>
						<tbody>";
		
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$table .= "<tr onclick='openArticle(".$row['ID'].")'>";
			$table .='<td>'.$row['ID'].'</td>';
			$table .= '<td>'.$row['Author'].'</td>';
			$table .= '<td>'.$row['Title'].'</td></tr>';
		}
						
		$table .= "</tbody></table>";
		return $table;
	}
	
?>