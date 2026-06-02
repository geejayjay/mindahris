// written by alvin

var r = $;
var link = (typeof BASE_URL !== 'undefined' ? BASE_URL : window.location.origin + '/').replace(/\/+$/, '');
var emp = new Object();
	emp.id 	  = null;
	emp.div   = null;
	emp.cntid = null;
	emp.coverage = null;
	emp.frm_sumrep = null;
	
r(document).ready(function(){
	
	/*
	if (viewdtr == true) {
		emp.id 	  = frm_empid;
		emp.cntid = frm_cntid;
		emp.frm_sumrep = frm_sumrep;
		
		$(document).find("#statmsg").text("loading selected dtr coverage");
		alert(frm_empid)
		r("#dtrloadhere").load("http://office.minda.gov.ph:9003/hr/dtr",{emp_id:emp.id, 
																		 retwat : return_wat, 
																		 cntid : emp.cntid , 
																		 sumrep : emp.frm_sumrep}, function() {
			if (getdate__ == true) {
				$('#jqxfullname').val( empname );
				processdtr(datefrom , dateto , bioid , areaid , empid);
				$(document).find("#statmsg").text("");
			}
		});
	}
	*/
	
	r(document).on("click",".viewlink", function() {
		emp.id 	  	  = r(this).data("empid");
		emp.cntid 	  = r(this).data("dtrcover");
		emp.coverage  = r(this).data("coverage");
		
		// dtrloadhere 
		
		// hr/dtr
		// emp_id
		r("#dtrloadhere").html("loading DTR...");
	
		$(document).find("#statmsg").text("loading selected dtr coverage");
		r("#dtrloadhere").load(link+"/hr/dtr",{emp_id:emp.id, retwat : return_wat, cntid : emp.cntid , coverage : emp.coverage}, function() {
			if (getdate__ == true) {
				$('#jqxfullname').val( empname );
				processdtr(datefrom , dateto , bioid , areaid , empid);
				$(document).find("#statmsg").text("");
				
				
			}
		});
		
	})
	
	r(document).on("click", ".printedspan", function() {
		var countersign_id = $(this).data("countersign_id");
		var t = $(this);
		
		var conf = confirm("Are you sure you have printed it all?");
		
		if (conf) {
			performajax(["Dtr/printed",{cid:countersign_id}],function(data){
				if (data == true || data == "true") {
					t.closest("li").fadeOut();
				}
			});
		}
		
	})
	
	r(document).on("mouseover", ".printedspan" ,function() {
		r(this).prepend("<i id='arrowright' class='fa fa-arrow-right' aria-hidden='true'></i>");
	}).on("mouseout", ".printedspan" ,function() {
		r(this).find("#arrowright").remove();
	})
	
	r("#thedivision li").on("click", function() {
		r(this).addClass("selected_office").siblings().removeClass("selected_office");
		
		emp.div   = r(this).data("divid");

		r(".theright_wrap").animate({
			"width":"0%"
		})
						
		r("#thelist").children().remove();
		
		performajax(["Dtr/getsubmitteddtrs",{divid:emp.div, returnwhat : return_wat}], function(ret) {
			console.log(ret);
			if (ret.length == 0) {
				r("<p style='width: 100%; text-align: center; padding-top: 55px;'> No DTR found. </p>").appendTo("#thelist");
			} else {
				for(var i = 0 ; i<=ret.length-1; i++) {
					var empclass = (ret[i]['employment_type']== "JO")?"emp_class":"regular_class";
					r("<li>")
						.append("<h4 title='click to remove this person from the list' class='printedspan' data-empid='"+ret[i]['employee_id']+"' data-countersign_id='"+ret[i]['countersign_id']+"'> "+ret[i]['f_name']+" </h4>"+
									"<p>"+
										"<span class='dtr_coverage'> "+ret[i]['dtr_coverage']+" </span>"+
										"<span class='"+empclass+"'> "+ret[i]['employment_type']+" </span>"+
										"<span class='viewlink' data-empid='"+ret[i]['employee_id']+"' data-dtrcover ='"+ret[i]['countersign_id']+"' data-coverage = '"+ret[i]['dtr_coverage']+"'> view </span>"+
									"</p>")
							.appendTo("#thelist");
				}
			}
			
			r(".theright_wrap").animate({
				"width":"16%"
			},100)
		})
		
	})
	
	

})