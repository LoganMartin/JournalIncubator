
//Makes the timeline table sortable, and by default is sorted descending by date i.e most recent first.
$(document).ready(function() { 
	$("#timeline-table").tablesorter( {sortList: [[0,1]]} );
}); 