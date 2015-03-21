$("ul.nav-tabs a").click(function (e) {
  e.preventDefault();  
    $(this).tab('show');
});

$(document).ready(function() { 
	calculateRoleBadges();
	$(".article-table").tablesorter();
});

//Opens new tab for the article that has been clicked by the user.
function openArticle(id, role) {
	var url = "article.php?id=" + id + "&r=" + role;
	var page = window.open(url, '_blank');
	page.focus();
}

//Calculates the number to be displayed next to role name, by adding the badge #'s of each tab within the role
function calculateRoleBadges() {
	var totalEditorArticles = parseInt($("#editor-unassigned-badge").text()) + parseInt($("#editor-review-badge").text()) + parseInt($("#editor-editing-badge").text());
	$("#editorTab-badge").html(totalEditorArticles); 
	var totalSectionArticles = parseInt($("#section-review-badge").text()) + parseInt($("#section-editing-badge").text());
	$("#sectionEditorTab-badge").html(totalSectionArticles);
	$("#layoutEditorTab-badge").html($("#layout-badge").text());
	$("#reviewerTab-badge").html($("#reviewer-badge").text());
	$("#copyEditorTab-badge").html($("#copy-badge").text());
	$("#proofreaderTab-badge").html($("#proof-badge").text());
	var totalAuthorArticles = parseInt($("#author-active-badge").text()) + parseInt($("#author-archive-badge").text());
	$("#authorTab-badge").html(totalAuthorArticles);
}
