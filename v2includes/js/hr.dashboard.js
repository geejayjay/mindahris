
/*
$(document).ready(function(){
	$("#element_loader").text("loading...");
	//$("#element_loader").load("http://office.minda.gov.ph:9003/hr/dtr");

	alert("doms are loaded")
	$(".hr_main_navs ul li p").on("click", function() {
		var link = $(this).data("link");

	})
})
*/


window.onload = function() {
	// load the DTR window right after all doms in the browser are loaded.
		// ======== important ============
			var base = (typeof BASE_URL !== 'undefined' ? BASE_URL : window.location.origin + '/').replace(/\/+$/, '');
			$("#element_loader").load(base + "/hr/employees");
			$(".hr_main_navs ul li").eq(9).addClass("liselected")
		// ======== important ============
	// end
	
	// listen on to administrative buttons
	$(".hr_main_navs ul li p").on("click", function() {
		var link = $(this).data("link");
		
		// bug found 
		// refresh the variables here
		
		$("#element_loader").text("loading...");
		$("#element_loader").load(link);
		
		$(this).closest("li").addClass("liselected").siblings().removeClass("liselected");
		// $(".showwindow").fadeIn();
	})

	$(".showwindow").on("click", function(e) {
		
		if (e.target.id == "showwindow") {
			$(this).fadeOut();
		}
	})

	$(document).on("click","#sendacct_to_email", function() {
		$(document).find("#btn_save_employees").fadeOut();
		
		var details 			= new Object();
			details.empid		= $(document).find("#textbox_employee_id").val();
			details.uname		= $(document).find("#textbox_username").val();
			details.email		= $(document).find("#textbox_company_email").val();
			
		var pass = null;
			
			if ($(document).find("#textbox_new_password").val() != "" || $(document).find("#textbox_new_password").val() != " " || 
				$(document).find("#textbox_confirm_password").val() != "" || $(document).find("#textbox_confirm_password").val() != " ") {
				if ($(document).find("#textbox_new_password").val() == $(document).find("#textbox_confirm_password").val()) {
					pass = $(document).find("#textbox_new_password").val();
				} else {
					alert("Passwords do not match");
					return;
				}
			} else {
				alert("Passwords is empty.");
				return;
			}
			
			if ($(document).find("#textbox_company_email").val() == "" || $(document).find("#textbox_company_email").val() == " ") {
				alert("Email address is important");
				return;
			}
			
			details.password	= pass;
			
			$(document).find("#sendingemail").text("...Sending Email! Please wait.");
			performajax(["Leave/send_loginaccount",{dets:details}], function(data) {
				var msg = null;
				if (data == true) {
					$(document).find("#btn_save_employees").fadeIn();
					msg = "Email is sent! Please click Save all changes.";
				} 	
				$(document).find("#sendingemail").text(msg);				
			});
			
	})
	
		

}
