$("ul.nav-tabs a").click(function (e) {
  e.preventDefault();  
    $(this).tab('show');
});

$(document).ready(function() { 
	$("#review-table").tablesorter( {sortList: [[0,1]]} );
}); 

//Opens new tab for the article that has been clicked by the user.
function openArticle(id) {
	var url = "article.php?id=" + id;
	var page = window.open(url, '_blank');
	page.focus();
}
