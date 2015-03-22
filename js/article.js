
//Makes the timeline table sortable, and by default is sorted descending by date i.e most recent first.
$(document).ready(function() { 
	$("#timeline-table").tablesorter( {sortList: [[0,1]]} );
}); 

/**
 * Takes the text that a user has entered into the event textbox, and sends it to the php backend to
 * be entered into the database, as a new entry in the event log. 
 */
function submitEvent() {
	var eventText = $("#articleEvent").val();
	
	if(eventText == "") {
		alert("You can't submit an empty event!");
	}
	else {
		$.ajax({
			type: "POST",
			url: "controllers/article_ctrl.php",
			data: {"action": "submitEvent", "eventText": eventText, "articleID": articleID, "userID": userID, "edNote": true},
			success: function(data) {
				$("#event-alert-div").html(data);
				refreshTimeline();
				$("#event-alert-div").removeClass("hidden");
			},
			error: function(xhr) {
				alert("An error occured: " + xhr.status + " " + xhr.statusText);
			}
		});
	}
}

/**
 * Refreshes the timeline table that's displayed on the page. 
 */
function refreshTimeline() {
	$.ajax({
		type: "POST",
		url: "controllers/article_ctrl.php",
		data: {"action": "ajaxGetTimeline", "articleID": articleID},
		success: function(data) {
			$("#table-container").html(data);
			$("#timeline-table").tablesorter( {sortList: [[0,1]]} );
		},
		error: function(xhr) {
			alert("An error occured: " + xhr.status + " " + xhr.statusText);
		}
	});
}
/**
 * Passes some user information for the selected editor, to be placed in the database to the php end.
 * @param editorID the id number of the editor selected.
 * @param userName the first + last name of the editor selected.
 */
function assignEditor(editorID, userName) {
	$.ajax({
		type: "POST",
		url: "controllers/article_ctrl.php",
		data: {"action": "assignEditor", "articleID": articleID, "editorID": editorID, "userName": userName, "userID": userID},
		success: function(data) {
			$("#assign"+editorID).attr("disabled", "disabled");
			$("#assign"+editorID).html("Assigned");
			$("#editor-p").append(userName+", ");
			$("#article-alerts").html(data);
		},
		error: function(xhr) {
			alert("An error occured: " + xhr.status + " " + xhr.statusText);
		}
	});
}
$('#article-modal').on('hidden.bs.modal', function () {
    refreshStatus();
	refreshTimeline();
})

/**
 * Refreshes the current status section of the page, by making an ajax call to the function that generates
 * those elements. 
 */
function refreshStatus() {
	$.ajax({
		type: "POST",
		url: "controllers/article_ctrl.php",
		data: {"action": "ajaxGetStatus", "articleID": articleID},
		success: function(data) {
			$("#status-container").html(data);
		},
		error: function(xhr) {
			alert("An error occured: " + xhr.status + " " + xhr.statusText);
		}
	});
}
