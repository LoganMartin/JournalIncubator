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
				$tabHTML .= "><a href='#$roleTab' aria-controls='$roleTab' role='tab' data-toggle='pill'>$roleName <span id='".$roleTab."-badge' class='badge'>".getEditNum()."</span></a></li>";
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
				$unassignedData = getArticlesByType("editor-unassigned");
				$reviewData = getArticlesByType("editor-review");
				$editingData = getArticlesByType("editor-editing");
				$tabHTML .= "<li role='presentation' class='active'><a href='#unassignedTab".$count."' aria-controls='unassignedTab$count' role='tab' data-toggle='tab'>Unassigned <span id='editor-unassigned-badge' class='badge'>".mysql_num_rows($unassignedData)."</span></a></li>";	
				$tabHTML .= "<li role='presentation'><a href='#reviewTab".$count."' aria-controls='reviewTab$count' role='tab' data-toggle='tab'>In Review <span id='editor-review-badge' class='badge'>".mysql_num_rows($reviewData)."</span></a></li>";
				$tabHTML .= "<li role='presentation'><a href='#editTab".$count."' aria-controls='editTab$count' role='tab' data-toggle='tab'>In Editing <span id='editor-editing-badge' class='badge'>".mysql_num_rows($editingData)."</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='unassignedTab$count'>".generateArticleTable($unassignedData)."</div>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane' id='reviewTab$count'>".generateArticleTable($reviewData)."</div>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane' id='editTab$count'>".generateArticleTable($editingData)."</div>";
				break;
			case 512: //Section Editor
				$reviewData = getArticlesByType("section-review");
				$editingData = getArticlesByType("section-editing");
				$tabHTML .= "<li role='presentation' class='active'><a href='#reviewTab".$count."' aria-controls='reviewTab$count' role='tab' data-toggle='tab'>In Review <span id='section-review-badge' class='badge'>".mysql_num_rows($reviewData)."</span></a></li>";
				$tabHTML .= "<li role='presentation'><a href='#editTab".$count."' aria-controls='editTab$count' role='tab' data-toggle='tab'>In Editing <span id='section-editing-badge' class='badge'>".mysql_num_rows($editingData)."</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='reviewTab$count'>".generateArticleTable($reviewData)."</div>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane' id='editTab$count'>".generateArticleTable($editingData)."</div>";
				break;
			case 768: //Layout Editor
				$layoutData = getArticlesByType("layout-editing");
				$tabHTML .= "<li role='presentation' class='active'><a href='#editTab".$count."' aria-controls='editTab$count' role='tab' data-toggle='tab'>In Editing <span id='layout-badge' class='badge'>".mysql_num_rows($layoutData)."</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='editTab$count'>".generateArticleTable($layoutData)."</div>";
				break;
			case 4096: //Reviewer
				$reviewerData = getArticlesByType("reviewer-active");
				$tabHTML .= "<li role='presentation' class='active'><a href='#unassignedTab".$count."' aria-controls='unassignedTab$count' role='tab' data-toggle='tab'>Active <span id='reviewer-badge' class='badge'>".mysql_num_rows($reviewerData)."</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='unassignedTab$count'>".generateArticleTable($reviewerData)."</div>";
				break;
			case 8192: //Copy Editor
				$copyData = getArticlesByType("copy-editing");
				$tabHTML .= "<li role='presentation' class='active'><a href='#editTab".$count."' aria-controls='editTab$count' role='tab' data-toggle='tab'>In Editing <span id='copy-badge' class='badge'>".mysql_num_rows($copyData)."</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='editTab$count'>".generateArticleTable($copyData)."</div>";
				break;
			case 12288: //Proofreader
				$proofData = getArticlesByType("proof-editing");
				$tabHTML .= "<li role='presentation' class='active'><a href='#editTab".$count."' aria-controls='editTab$count' role='tab' data-toggle='tab'>In Editing <span id='proof-badge' class='badge'>".mysql_num_rows($proofData)."</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='editTab$count'>".generateArticleTable($proofData)."</div>";
				break;
			case 65536: //Author
				$activeData = getArticlesByType("author-active");
				$archiveData = getArticlesByType("author-archive");		
				$tabHTML .= "<li role='presentation' class='active'><a href='#unassignedTab".$count."' aria-controls='unassignedTab$count' role='tab' data-toggle='tab'>Active <span id='author-active-badge' class='badge'>".mysql_num_rows($activeData)."</span></a></li>";
				$tabHTML .= "<li role='presentation'><a href='#reviewTab".$count."' aria-controls='reviewTab$count' role='tab' data-toggle='tab'>Archive <span id='author-archive-badge' class='badge'>".mysql_num_rows($archiveData)."</span></a></li>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane active' id='unassignedTab$count'>".generateArticleTable($activeData)."</div>";
				$contentHTML .= "<div role='tabpanel' class='tab-pane nested-tab-pane' id='reviewTab$count'>".generateArticleTable($archiveData)."/div>";
				break;
			default:
				break;
		}
		$html = $tabHTML."</ul>".$contentHTML."</div>";
		return $html;
	}

	/**
	 * Generates an html table, formatted to display some basic article information
	 * @param $tableData data pulled from the database, to be inserted into our html table.
	 * @return string html code of our generated table.
	 */
	function generateArticleTable($tableData) {
		$table = "<table class='table tablesorter table-striped table-hover article-table'>
						<thead>
							<tr>
								<th width='10%'>ID</th>
								<th width='15%'>Author</th>
								<th width='35%'>Title</th>
								<th width='40%'>Latest Updates</th>
							</tr>
						</thead>
						<tbody>";
		
		//$tableData = getArticlesByType($articleType);
		
		while($row = mysql_fetch_array($tableData, MYSQL_ASSOC)) {
			$table .= "<tr onclick='openArticle(".$row['ID'].")'>";
			$table .='<td>'.$row['ID'].'</td>';
			$table .= '<td>'.$row['Author'].'</td>';
			$table .= '<td>'.$row['Title'].'</td>';
			$table .= '<td>'.getLatestUpdates($row['ID']).'</td></tr>';
		}
						
		$table .= "</tbody></table>";
		return $table;
	}
	
	/**
	 * Selects articles from the OJS database, based on the role of the user, and the status of the article.
	 * Has super long, messy SQL statements due to the poor OJS database design.
	 * @param $articleType string A string identifying what type of articles are to be pulled from the db.
	 * @return array $data article data pulled from the database, as well as a count of articles found.
	 */
	function getArticlesByType($articleType) {
		global $connection;
		$userID = $_SESSION['ojs_userID'];	
		switch($articleType) {
			case "editor-unassigned":
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
				break;
				
			case "editor-review":
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
					
					--  and E1.editor_id='$userID'  
					 order by A1.article_id Desc;";
				break;
				
			case "editor-editing":
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
					 -- AND E1.editor_id='$userID'  
					 order by A1.article_id Desc;";
				break;
				
			case"section-review":
				$select = "SELECT Distinct a.article_id as ID
					,aap.last_name as Author
					,(select distinct actpl.setting_value from article_settings where setting_name='title' and article_id=a.article_id and setting_value <>'') as Title
					FROM articles a 
					LEFT JOIN authors aa ON (aa.submission_id = a.article_id) 
					LEFT JOIN authors aap ON (aap.submission_id = a.article_id AND aap.primary_contact = 1) 
					LEFT JOIN edit_assignments e ON (e.article_id = a.article_id) 
					LEFT JOIN users ed ON (e.editor_id = ed.user_id) 
					LEFT JOIN sections s ON (s.section_id = a.section_id) 
					LEFT JOIN signoffs scf ON (a.article_id = scf.assoc_id AND scf.assoc_type = 'ASSOC_TYPE_ARTICLE' AND scf.symbolic = 'SIGNOFF_COPYEDITING_FINAL') 
					LEFT JOIN users ce ON (scf.user_id = ce.user_id) 
					LEFT JOIN signoffs spr ON (a.article_id = spr.assoc_id AND spr.assoc_type = 'ASSOC_TYPE_ARTICLE' AND spr.symbolic = 'SIGNOFF_PROOFREADING_PROOFREADER') 
					LEFT JOIN users pe ON (pe.user_id = spr.user_id) 
					LEFT JOIN review_rounds r2 ON (a.article_id = r2.submission_id and a.current_round = r2.round) 
					LEFT JOIN signoffs sle ON (a.article_id = sle.assoc_id AND sle.assoc_type = 'ASSOC_TYPE_ARTICLE' AND sle.symbolic = 'SIGNOFF_LAYOUT') 
					LEFT JOIN users le ON (le.user_id = sle.user_id) 
					LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = 'title' AND stpl.locale = a.locale) 
					LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title' AND stl.locale = a.locale) 
					LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'abbrev' AND sapl.locale =  a.locale) 
					LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'abbrev' AND sal.locale =  a.locale) 
					LEFT JOIN article_settings actpl ON (actpl.article_id = a.article_id AND actpl.setting_name = 'cleanTitle' AND actpl.locale = a.locale) 
					LEFT JOIN article_settings actl ON (a.article_id = actl.article_id AND actl.setting_name = 'cleanTitle' AND actl.locale = a.locale) 
					LEFT JOIN article_settings atpl ON (atpl.article_id = a.article_id AND atpl.setting_name ='title' AND atpl.locale = a.locale) 
					LEFT JOIN article_settings atl ON (a.article_id = atl.article_id AND atl.setting_name ='title' AND atl.locale = a.locale) 
					LEFT JOIN edit_decisions edec ON (a.article_id = edec.article_id) LEFT JOIN edit_decisions edec2 ON (a.article_id = edec2.article_id AND edec.edit_decision_id < edec2.edit_decision_id) 
					WHERE a.journal_id = 2 AND a.submission_progress = 0 
					AND (a.status = 1 AND e.can_review = 1 AND (edec.decision IS NULL OR edec.decision <> 1)) AND edec2.edit_decision_id IS NULL
					AND a.status = 1 AND e.can_review = 1 AND (edec.decision IS NULL OR edec.decision <> ' . SUBMISSION_EDITOR_DECISION_ACCEPT . ')
					AND e.editor_id = '$userID' ;";
				break;
				
			case"section-editing":
				$select = "SELECT Distinct a.article_id as ID
					,aap.last_name as Author
					,(select distinct actpl.setting_value from article_settings where setting_name='title' and article_id=a.article_id and setting_value <>'') as Title
					FROM articles a 
					LEFT JOIN authors aa ON (aa.submission_id = a.article_id) 
					LEFT JOIN authors aap ON (aap.submission_id = a.article_id AND aap.primary_contact = 1) 
					LEFT JOIN edit_assignments e ON (e.article_id = a.article_id) 
					LEFT JOIN users ed ON (e.editor_id = ed.user_id) 
					LEFT JOIN sections s ON (s.section_id = a.section_id) 
					LEFT JOIN signoffs scf ON (a.article_id = scf.assoc_id AND scf.assoc_type = 'ASSOC_TYPE_ARTICLE' AND scf.symbolic = 'SIGNOFF_COPYEDITING_FINAL') 
					LEFT JOIN users ce ON (scf.user_id = ce.user_id) 
					LEFT JOIN signoffs spr ON (a.article_id = spr.assoc_id AND spr.assoc_type = 'ASSOC_TYPE_ARTICLE' AND spr.symbolic = 'SIGNOFF_PROOFREADING_PROOFREADER') 
					LEFT JOIN users pe ON (pe.user_id = spr.user_id) 
					LEFT JOIN review_rounds r2 ON (a.article_id = r2.submission_id and a.current_round = r2.round) 
					LEFT JOIN signoffs sle ON (a.article_id = sle.assoc_id AND sle.assoc_type = 'ASSOC_TYPE_ARTICLE' AND sle.symbolic = 'SIGNOFF_LAYOUT') 
					LEFT JOIN users le ON (le.user_id = sle.user_id) 
					LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = 'title' AND stpl.locale = a.locale) 
					LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title' AND stl.locale = a.locale) 
					LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'abbrev' AND sapl.locale =  a.locale) 
					LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'abbrev' AND sal.locale =  a.locale) 
					LEFT JOIN article_settings actpl ON (actpl.article_id = a.article_id AND actpl.setting_name = 'cleanTitle' AND actpl.locale = a.locale) 
					LEFT JOIN article_settings actl ON (a.article_id = actl.article_id AND actl.setting_name = 'cleanTitle' AND actl.locale = a.locale) 
					LEFT JOIN article_settings atpl ON (atpl.article_id = a.article_id AND atpl.setting_name ='title' AND atpl.locale = a.locale) 
					LEFT JOIN article_settings atl ON (a.article_id = atl.article_id AND atl.setting_name ='title' AND atl.locale = a.locale) 
					LEFT JOIN edit_decisions edec ON (a.article_id = edec.article_id) LEFT JOIN edit_decisions edec2 ON (a.article_id = edec2.article_id AND edec.edit_decision_id < edec2.edit_decision_id) 
					WHERE a.journal_id = 2 AND a.submission_progress = 0 
					AND a.status = 1 AND e.can_edit = 1 AND edec.decision =1 
					AND edec2.edit_decision_id IS NULL
					 AND e.editor_id = '$userID' ;";
				break;
				
			case"layout-editing":
				$select = "SELECT Distinct a.article_id as ID
					,aap.last_name as Author
					,(select distinct actpl.setting_value from article_settings where setting_name='title' and article_id=a.article_id and setting_value <>'') as Title
					,SUBSTRING(COALESCE(stl.setting_value, stpl.setting_value) FROM 1 FOR 255) AS section_title
					FROM	articles a
					LEFT JOIN authors aa ON (aa.submission_id = a.article_id)
					LEFT JOIN authors aap ON (aap.submission_id = a.article_id AND aap.primary_contact = 1)
					LEFT JOIN sections s ON s.section_id = a.section_id
					LEFT JOIN edit_assignments e ON (e.article_id = a.article_id)
					LEFT JOIN users ed ON (e.editor_id = ed.user_id)
					LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = 'title' AND stpl.locale = a.locale)
					LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title' AND stl.locale = a.locale)
					LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'abbrev' AND sapl.locale = a.locale)
					LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'abbrev' AND sal.locale = a.locale)
					LEFT JOIN article_settings actpl ON (actpl.article_id = a.article_id AND actpl.setting_name = 'cleanTitle' AND actpl.locale = a.locale)
					LEFT JOIN article_settings actl ON (a.article_id = actl.article_id AND actl.setting_name = 'clean_title' AND actl.locale = a.locale)
					LEFT JOIN article_settings atpl ON (atpl.article_id = a.article_id AND atpl.setting_name = 'title' AND atpl.locale = a.locale)
					LEFT JOIN article_settings atl ON (a.article_id = atl.article_id AND atl.setting_name = 'title' AND atl.locale = a.locale)
					LEFT JOIN signoffs scpf ON (a.article_id = scpf.assoc_id AND scpf.assoc_type = 257 AND scpf.symbolic = 'SIGNOFF_COPYEDITING_FINAL')
					LEFT JOIN signoffs sle ON (a.article_id = sle.assoc_id AND sle.assoc_type =257 AND sle.symbolic = 'SIGNOFF_LAYOUT')
					LEFT JOIN signoffs spr ON (a.article_id = spr.assoc_id AND spr.assoc_type = 257 AND spr.symbolic = 'SIGNOFF_PROOFREADING_LAYOUT')
					LEFT JOIN signoffs scpi ON (a.article_id = scpi.assoc_id AND scpi.assoc_type = 257 AND scpi.symbolic = 'SIGNOFF_COPYEDITING_INITIAL')        
					WHERE sle.date_notified IS NOT NULL 
			        AND a.status = 1
					AND a.journal_id = 2  
			        AND sle.user_id = '$userID';";
				break;
				
			case"reviewer-active":
				$select = "SELECT	Distinct a.article_id as ID, u.last_name as User, 
					CONCAT(LPAD(MONTH(a.date_submitted),2,'0'),'-',DAYOFMONTH(a.date_submitted),'-',YEAR(a.date_submitted)) as SUBMITTED,
					COALESCE(atl.setting_value, atpl.setting_value) AS submission_title,
					COALESCE(stl.setting_value, stpl.setting_value) AS section_title
					FROM	articles a
					LEFT JOIN review_assignments r ON (a.article_id = r.submission_id)
					LEFT JOIN article_settings atpl ON (atpl.article_id = a.article_id AND atpl.setting_name = 'cleanTitle' AND atpl.locale = a.locale)
					LEFT JOIN article_settings atl ON (atl.article_id = a.article_id AND atl.setting_name = 'cleanTitle' AND atl.locale = a.locale)
					LEFT JOIN sections s ON (s.section_id = a.section_id)
					LEFT JOIN users u ON (r.reviewer_id = u.user_id)
					LEFT JOIN review_rounds r2 ON (r.submission_id = r2.submission_id AND r.round = r2.round)
					LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = 'title' AND stpl.locale = a.locale)
					LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title' AND stl.locale = a.locale)
					LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'abbrev' AND sapl.locale = a.locale)
					LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'abbrev' AND sal.locale = a.locale)
					WHERE	r.date_notified IS NOT NULL
			        AND r.date_completed IS NULL AND r.declined <> 1 
			        AND (r.cancelled = 0 OR r.cancelled IS NULL) AND a.status = 1
			        AND a.journal_id = 2 
			        AND r.reviewer_id = '$userID';";
				break;
				
			case"copy-editing":
				$select = "SELECT Distinct a.article_id as ID,aap.last_name as Author
					,(select distinct actpl.setting_value from article_settings where setting_name='title' and article_id=a.article_id and setting_value <>'') as Title
					FROM	articles a
					LEFT JOIN published_articles pa ON (a.article_id = pa.article_id)
					LEFT JOIN issues i ON (pa.issue_id = i.issue_id)
					LEFT JOIN authors aa ON (aa.submission_id = a.article_id)
					LEFT JOIN authors aap ON (aap.submission_id = a.article_id AND aap.primary_contact = 1)
					LEFT JOIN sections s ON (s.section_id = a.section_id)
					LEFT JOIN edit_assignments e ON (e.article_id = a.article_id)
					LEFT JOIN users ed ON (e.editor_id = ed.user_id)
					LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name ='title' AND stpl.locale = a.locale)
					LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title' AND stl.locale =  a.locale)
					LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'abbrev' AND sapl.locale =  a.locale)
					LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'abbrev' AND sal.locale =  a.locale)
					LEFT JOIN article_settings actpl ON (actpl.article_id = a.article_id AND actpl.setting_name = 'cleanTitle' AND actpl.locale = a.locale)
					LEFT JOIN article_settings actl ON (a.article_id = actl.article_id AND actl.setting_name = 'cleanTitle' AND actl.locale = a.locale)
					LEFT JOIN article_settings atpl ON (atpl.article_id = a.article_id AND atpl.setting_name = 'title' AND atpl.locale = a.locale)
					LEFT JOIN article_settings atl ON (a.article_id = atl.article_id AND atl.setting_name = 'title'AND atl.locale = a.locale)
					LEFT JOIN signoffs scpf ON (a.article_id = scpf.assoc_id AND scpf.assoc_type = 257 AND scpf.symbolic = 'SIGNOFF_COPYEDITING_FINAL')
					LEFT JOIN signoffs sle ON (a.article_id = sle.assoc_id AND sle.assoc_type = 257 AND sle.symbolic = 'SIGNOFF_LAYOUT')
					LEFT JOIN signoffs spr ON (a.article_id = spr.assoc_id AND spr.assoc_type = 257 AND spr.symbolic = 'SIGNOFF_PROOFREADING_PROOFREADER')
					LEFT JOIN signoffs scpi ON (a.article_id = scpi.assoc_id AND scpi.assoc_type = 257 AND scpi.symbolic = 'SIGNOFF_COPYEDITING_INITIAL')
					WHERE
					a.journal_id = 2 AND 
					scpi.user_id ='$userID' 
					AND  ( (i.date_published IS NOT NULL AND a.status = 1));";
				break;
				
			case"proof-editing":
				$select = "SELECT  Distinct a.article_id as ID
					,aap.last_name as Author
					,(select distinct actpl.setting_value from article_settings where setting_name='title' and article_id=a.article_id and setting_value <>'') as Title
					 FROM articles a 
					 LEFT JOIN authors aa ON (aa.submission_id = a.article_id) 
					 LEFT JOIN authors aap ON (aap.submission_id = a.article_id AND aap.primary_contact = 1) 
					 LEFT JOIN sections s ON s.section_id = a.section_id 
					 LEFT JOIN edit_assignments e ON (e.article_id = a.article_id) 
					 LEFT JOIN users ed ON (e.editor_id = ed.user_id) 
					 LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = 'title' AND stpl.locale = a.locale) 
					 LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title' AND stl.locale = a.locale) 
					 LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'abbrev' AND sapl.locale = a.locale) 
					 LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'abbrev' AND sal.locale = a.locale) 
					 LEFT JOIN article_settings actpl ON (actpl.article_id = a.article_id AND actpl.setting_name = 'cleanTitle' AND actpl.locale = a.locale) 
					 LEFT JOIN article_settings actl ON (a.article_id = actl.article_id AND actl.setting_name = 'cleanTitle' and actl.locale = a.locale) 
					 LEFT JOIN article_settings atpl ON (atpl.article_id = a.article_id AND atpl.setting_name = 'title' AND atpl.locale = a.locale) 
					 LEFT JOIN article_settings atl ON (a.article_id = atl.article_id AND atl.setting_name = 'title' and atl.locale = a.locale) 
					 LEFT JOIN signoffs scpf ON (a.article_id = scpf.assoc_id AND scpf.assoc_type = 257 AND scpf.symbolic = 'SIGNOFF_COPYEDITING_FINAL') 
					 LEFT JOIN signoffs sle ON (a.article_id = sle.assoc_id AND sle.assoc_type = 257 AND sle.symbolic = 'SIGNOFF_LAYOUT') 
					 LEFT JOIN signoffs spr ON (a.article_id = spr.assoc_id AND spr.assoc_type = 257 AND spr.symbolic = 'SIGNOFF_PROOFREADING_PROOFREADER') 
					 LEFT JOIN signoffs scpi ON (a.article_id = scpi.assoc_id AND scpi.assoc_type = 257 AND scpi.symbolic = 'SIGNOFF_COPYEDITING_INITIAL') 
					 WHERE 
					 a.journal_id = 2 AND spr.date_notified IS NOT NULL AND a.status = 1
					 AND spr.user_id = '$userID';";
				break;
			case"author-active":
				$select = "SELECT	Distinct a.article_id as ID
					,aa.last_name as Author
					,(select distinct atpl.setting_value from article_settings where setting_name='title' and article_id=a.article_id and setting_value <>'') as Title
					,CONCAT(LPAD(MONTH(a.date_submitted),2,'0'),'-',DAYOFMONTH(a.date_submitted),'-',YEAR(a.date_submitted)) as SUBMITTED
					FROM	articles a
					LEFT JOIN authors aa ON (aa.submission_id = a.article_id AND aa.primary_contact = 1)
					LEFT JOIN article_settings atpl ON (atpl.article_id = a.article_id AND atpl.setting_name = 'cleanTitle' AND atpl.locale = a.locale)
					LEFT JOIN article_settings atl ON (atl.article_id = a.article_id AND atl.setting_name = 'cleanTitle' AND atl.locale = a.locale)
					LEFT JOIN sections s ON (s.section_id = a.section_id)
					LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = 'title' AND stpl.locale = a.locale)
					LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title' AND stl.locale = a.locale)
					LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'abbrev' AND sapl.locale = a.locale)
					LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'abbrev' AND sal.locale = a.locale)
					WHERE  a.journal_id = 2 AND a.status = 1
					AND a.user_id = '$userID';";
				break;
			case"author-archive":
				$select = "SELECT	Distinct a.article_id as ID
					,aa.last_name as Author
					,(select distinct atpl.setting_value from article_settings where setting_name='title' and article_id=a.article_id and setting_value <>'') as Title
					,CONCAT(LPAD(MONTH(a.date_submitted),2,'0'),'-',DAYOFMONTH(a.date_submitted),'-',YEAR(a.date_submitted)) as SUBMITTED
					FROM	articles a
					LEFT JOIN authors aa ON (aa.submission_id = a.article_id AND aa.primary_contact = 1)
					LEFT JOIN article_settings atpl ON (atpl.article_id = a.article_id AND atpl.setting_name = 'cleanTitle' AND atpl.locale = a.locale)
					LEFT JOIN article_settings atl ON (atl.article_id = a.article_id AND atl.setting_name = 'cleanTitle' AND atl.locale = a.locale)
					LEFT JOIN sections s ON (s.section_id = a.section_id)
					LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = 'title' AND stpl.locale = a.locale)
					LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = 'title' AND stl.locale = a.locale)
					LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = 'abbrev' AND sapl.locale = a.locale)
					LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = 'abbrev' AND sal.locale = a.locale)
					WHERE 
					a.status <> 1 AND a.submission_progress = 0
					AND a.user_id = '$userID';";
				break;
			default:
				die("This article type doesn't exist!");
				break;
		}
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		//$data['articleData'] = $result;
		//$data['numArticles'] = mysql_num_rows($result);
		
		return $result;
	}
	
	/**
	 * Gets the two most recent events of an article, to be displayed in the articles table on each article.
	 * @param $articleID The ID # of the article we are getting the events from.
	 * @return A string containing the two comments, with an html <br> between them.
	 */
	function getLatestUpdates($articleID) {
		global $connection;
		$select = "SELECT * FROM event_log INNER JOIN users ON event_log.user_id=users.user_id 
					WHERE assoc_id = $articleID ORDER BY event_log.date_logged DESC LIMIT 2";
					
		if(!$result = mysql_query($select, $connection)) {
			die('Error:'.mysql_error());
		}
		
		$updates = "";
		while($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$updates .= '<b>'.date("D, M d, Y",strtotime($row['date_logged']))." - ";
			$updates .= $row['first_name'].' '.$row['last_name'].":</b><br> ";
			$updates .= $row['message'].'<br>';
		}
		
		return $updates;
	}
	
?>