/**  
 * When the "Enter" key is clicked for each of these fields
 * it emulates the sign-in button being clicked 
 */
	$("#loginUsername").keydown(function(event) {
		if(event.keyCode == 13) {
			$("#signin-button").click();
		}
	});
	
	$("#loginPassword").keydown(function(event) {
		if(event.keyCode == 13) {
			$("#signin-button").click();
		}
	});
	
	function verifyLogin() {
		var username = $("#loginUsername").val();
		var password = $("#loginPassword").val();
		
		if(username==""){
			alert("You must enter a username");
		}
		else if(password==""){
			alert("You must enter a password");
		}
		else {
			$.ajax({
				type: "POST",
				url: "controllers/login_ctrl.php",
				data: {"action": "login", "username": username, "password": password},
				success: function(data) {
					if(data=="success") {
						window.location = "home.php"; //TODO: insert page to be redirected to after successful login.
					}
					else {
						$("#error-alert").html(data);
						$("#error-div").removeClass("hidden");
					}
				},
				error: function(xhr) {
					alert("An error occured: " + xhr.status + " " + xhr.statusText);
				}
			});
		}
	}