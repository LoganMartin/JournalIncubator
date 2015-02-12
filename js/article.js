
//Makes the timeline table sortable, and by default is sorted descending by date i.e most recent first.
$(document).ready(function() { 
	$("#timeline-table").tablesorter( {sortList: [[0,1]]} );
}); 

function submitEvent() {
	var eventText = $("#articleEvent").val();
	
	if(eventText == "") {
		alert("You can't submit an empty event!");
	}
	else {
		$.ajax({
			type: "POST",
			url: "controllers/article_ctrl.php",
			data: {"action": "submitEvent", "eventText": eventText, "articleID": articleID, "userID": userID},
			success: function(data) {
				$("#event-alert-div").html(data);
				$("#event-alert-div").removeClass("hidden");
			},
			error: function(xhr) {
				alert("An error occured: " + xhr.status + " " + xhr.statusText);
			}
		});
	}
}

function refreshTimeline() {
	
}
