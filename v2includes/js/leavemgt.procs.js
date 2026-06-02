// written by Alvin

var lm = $;
var conversions = null;

lm(document).ready(function() {
	// alert( window.location.href );
	// reset search table
		lm(document).find(".content_wrapper .rightbox").hide();
	// end reset
		
	performajax(['Leave/conversions',{a:"none"}], function(data) {
		conversions = data;
		
		var lmgt = new leavemgt();
			lmgt.init_();
	})

	// add balance
	lm(document).find("#addbalance_cal").jqxDateTimeInput({ width: "100%", height: 25 });
	/*
    lm(document).find("#addbalance_cal").on('close', function (event) {
		
			var selection = lm(document).find("#addbalance_cal").jqxDateTimeInput('getDate');
			console.log(selection);
                if (selection.from != null) {
					    var range = lm(document).find("#addbalance_cal").jqxDateTimeInput('getDate');
						var from  = range.from;
						var to    = range.to;
						
						 console.log(range);
						
						var monthfrom = themonths[from.getMonth()];
						var monthto   = themonths[to.getMonth()];
						
						var datefrom  = from.getDate();
						var dateto    = to.getDate();
						
						var a_months = monthfrom , a_days = datefrom+","+dateto+" " , a_year = from.getFullYear();
						
						if (datefrom == dateto) {
							a_days = datefrom +", ";
						}
						
						if (monthfrom != monthto) {
							a_months = monthfrom+" "+datefrom;
							a_months += " - ";
							a_months += monthto+" "+dateto;
							
							a_days = ", ";
						}
						
						thefulldate = a_months +" "+ a_days +""+ a_year;
					//	console.log(thefulldate);
						
                    }
                });
		*/
		// tabs
			lm(document).on("click","#addbalance", function() {
				lm(document).find(".add_ot").fadeOut();
				lm(document).find(".deduction_group").fadeOut();
				lm(document).find(".add_balance_group").fadeIn();
				lm(document).find("#adddeds").show();
				lm(document).find(".save_ot_action").fadeOut();
				lm(this).hide();
				// add_balance_group
				// #adddeds
				// #addbalance
				// deduction_group 
			})
			
			
			lm(document).on("click","#adddeds, #adddeductions", function() {
				lm(document).find(".add_ot").fadeOut();
				lm(document).find(".add_balance_group").fadeOut();
				lm(document).find(".deduction_group").fadeIn();
				lm(document).find("#addbalance").show();
				lm(document).find(".save_ot_action").fadeOut();
				lm(this).hide();
				// add_balance_group
				// #adddeds
				// #addbalance
				// deduction_group 
			})
			
			lm(document).on("click","#add_ot_btn", function() {
				lm(document).find(".add_ot").fadeIn();
				lm(document).find(".save_ot_action").fadeIn();
				lm(document).find("#adddeductions").fadeIn();
				lm(document).find(".add_balance_group").fadeOut();
				lm(document).find(".deduction_group").fadeOut();
				lm(document).find("#addbalance").fadeOut();
			})
		// end tabs
	// end add balance
		
	// CTO time text

		lm(document).find(".cto_time_txt").jqxDateTimeInput({ 
			width:"100%",
			formatString: 't', 
			showTimeButton: true, 
			showCalendarButton:false 
		});
		
		
		
	// end
	
	// deduction type ====================================================================================
	var dedtype = null;
	lm(document).on("change","#deductiontype", function() {
		dedtype = lm(this).val();
		
		if (thefulldate != null) {
			lm(document).find("#days_deds").val(thefulldate.length);
		}
		
		lm(document).find("#pssliprow").hide();	// for pass slip
		
		lm(document).find("#hrs_deds").removeAttr("disabled")
		lm(document).find("#mins_deds").removeAttr("disabled")
			
		if (dedtype == "leave") {
			lm(document).find("#ps_type").fadeOut();
			lm(document).find("#leavetypes").fadeIn();
			
			lm(document).find("#cto_row").fadeOut();
			lm(document).find("#dhm_row").fadeIn();
	
			lm(document).find("#hrs_deds").attr("disabled","disabled")
			lm(document).find("#mins_deds").attr("disabled","disabled")
		} else if (dedtype	== 'ps') {
			lm(document).find("#leavetypes").fadeOut();
			lm(document).find("#ps_type").fadeIn();
			
			lm(document).find("#cto_row").fadeOut();
			lm(document).find("#dhm_row").fadeIn();
			
			lm(document).find("#pssliprow").show();
		} else if (dedtype == "cto") {
			lm(document).find("#cto_row").fadeIn();
			lm(document).find("#dhm_row").fadeOut();
			
			lm(document).find("#leavetypes").fadeOut();
			lm(document).find("#ps_type").fadeOut();
			
		} else {
			lm(document).find("#leavetypes").fadeOut();
			lm(document).find("#ps_type").fadeOut();
			
			lm(document).find("#cto_row").fadeOut();
			lm(document).find("#dhm_row").fadeIn();
			
		}
		
		if (dedtype !== "leave") {
			if (thefulldate != null) {
				if (thefulldate.length > 1) {
					lm(document).find("#days_deds").val("");
					alert("multiple days for PS, UT, UNDERTIME and CTO is not allowed");
					thefulldate = null;
					return;
				}
			}
		}
		
	})
	// end deduction type ====================================================================================
	
	// type of leave ==== 
		lm(document).on("change","#typeofleave",function() {
			lm(document).find("#halfdaysick").hide();
			if ( lm(this).val() == 2 || 
				 lm(this).val() == 1 ||
				 lm(this).val() == "2" ||
				 lm(this).val() == "1" ) {
				lm(document).find("#formonet").show();
				// range_calendar
				
				if (lm(this).val() == 1 || lm(this).val() == "1") {
					lm(document).find("#halfdaysick").show();
				}
			} else {
				lm(document).find("#formonet").hide();
			}
		})
	// end type of leave
	
	// time back in ----pass slip 
		lm(document).on("click","#compbtn",function() {
			var timebackin = lm(document).find("#timebackin").val();
			var timeout    = lm(document).find("#timeout").val();
			
			performajax(['My/computepassslip', {tbi:timebackin,tout:timeout}], function(data) {
				if (data[0]>0) {
					lm(document).find("#hrs_deds").val(data[0]);
				}
				
				if (data[1]>0) {
					lm(document).find("#mins_deds").val(data[1]);
				}
			});
		});
	// end time back in 	
	
	// sent pass slip file to email
		lm(document).on("click","#sendps",function(){
			
		});
	// end sending
	
	// calendar type ====================================================================================
	var themonths = [
		"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"
	];
	
	var thefulldate = null;
	
	lm(document).find("#dates_calendar").jqxDateTimeInput({ width: "100%", height: 25,  selectionMode: 'range' });
    lm(document).find("#dates_calendar").on('close', function (event) {
				
				var selection = lm(document).find("#dates_calendar").jqxDateTimeInput('getRange');
                if (selection.from != null) {
					    var range = lm(document).find("#dates_calendar").jqxDateTimeInput('getRange');
						
						var dates = lm(document).find("#dates_calendar").jqxDateTimeInput("getDate");
						
						
						var from  = range.from;
						var to    = range.to;
							
						var monthfrom = themonths[from.getMonth()];
						var monthto   = themonths[to.getMonth()];
						
						var datefrom  = from.getDate();
						var dateto    = to.getDate();
						
						var a_months = monthfrom , 
							a_days   = datefrom+","+dateto+" " , 
							a_year   = from.getFullYear();
						
						if (datefrom == dateto) {
							a_days = datefrom +", ";
						}
						
						if (monthfrom != monthto) {
							a_months = monthfrom+" "+datefrom;
							a_months += " - ";
							a_months += monthto+" "+dateto;
							
							a_days = ", ";
						}
						
						thefulldate = a_months +" "+ a_days +""+ a_year;
							
						var start 		= from,
							end 		= to,
							currentDate = new Date(start),
							between 	= []
						;

						while (currentDate <= end) {
							var d  = new Date(currentDate),
								m  = themonths[d.getMonth()];
								_d = d.getDate();
								y  = d.getFullYear();
								
							between.push( m + " " + _d +", "+ y );
							currentDate.setDate(currentDate.getDate() + 1);
						}
						
						thefulldate = between;
						
						var count = thefulldate.length;
						
						if (dedtype == "leave") {
							lm(document).find("#days_deds").val(count);
							lm(document).find("#hrs_deds").val("").atr("disabled","disabled");
						} else {
							lm(document).find("#days_deds").val("");
							lm(document).find("#hrs_deds").val("").removeAttr("disabled");
							if (thefulldate.length > 1) {
								alert("multiple days for PS, UT, UNDERTIME and CTO is not allowed");
								thefulldate = null;
								return;
							}
						}
					//	console.log(thefulldate);
						
                    }
                });
	// end calendar type ====================================================================================
	
	var empid_credit = null;
	
	lm(document).on("click",".addleave", function() {
		empid_credit = lm(this).data("empid");
		
		lm(document).find("#modal_addleave").show();	
	})
	
		// printing =======================================================
			const print = document.querySelector("#printlink");
			
			if (print != undefined) {
				print.addEventListener("click", e => {
					let printwindow = window.open('','PRINT','height=800,width=1300');
					
					let toprint   	= "<table> "+document.querySelector(".ledgertbl").innerHTML+"</table>";
					// console.log("<table>"+toprint+"</table>");
					
					printwindow.document.write("<html><head><title> Printing </title>");
						var base = typeof BASE_URL !== 'undefined' ? BASE_URL : window.location.origin + '/';
					//	printwindow.document.write("<link rel='stylesheet' href='" + base + "v2includes/style/leavemgt.style.css'/>");
						printwindow.document.write("<link rel='stylesheet' href='" + base + "v2includes/style/printwindow.css'/>");
					printwindow.document.write("</head>");
					printwindow.document.write("<body>");
						printwindow.document.write(toprint);
					printwindow.document.write("</body></html>");
					
					setTimeout(function(){
						printwindow.print();
					},1500);
				//	printwindow.close();
					
				})
			}
		// end ============================================================
	
	var arrow_class = null;
	var arrow_show  = null;
	var marg_left   = 0;
	
	lm(document).on("click","#closeemps", function() {
		arrow_class = lm(this).attr('class');
	
		if (arrow_class == "close_to_left") {
			marg_left  = "-286px";
			widthl 	   = "0%";
			arrow_show = "open_to_right";
		} else {
			marg_left  = "0px";	
			arrow_show = "close_to_left";
			widthl 	   = "88.3%";
		}
		
		lm(document).find(".content_wrapper .leftcontent").animate({
			//"margin-left": marg_left
			"width": widthl
		},300, function() {
			lm(document).find("#closeemps").removeClass(arrow_class).addClass(arrow_show);
		});
	})


	lm(document).on("click","#save_leave_credit", function() {
		
		if (thefulldate == null) {
			if ( lm(document).find("#formonetselect").val() == "1" || 
				 lm(document).find("#formonetselect").val() == 1 ) {
				// thefulldate = "Dec 27, 2019"; // eclipse
			} else {
				alert("It's important to select the date.");
				return;
			}
			
		}
		
		/*
		var doms = ["#days_deds","#hrs_deds","#mins_deds"];
		for(var i = 0; i <= doms.length-1; i++) {
			if (lm(document).find(doms[i]).val().length == 0) {
				alert("It's important that all fields are filled up.");
				return;
			}
		}
		*/
		
		var details		      = new Object();
			details.type      = dedtype;
			details.days      = lm(document).find("#days_deds").val();
			details.hrs       = lm(document).find("#hrs_deds").val();
			details.mins      = lm(document).find("#mins_deds").val();
			details.empid     = empid_credit;
			details.dates     = thefulldate;
			details.formonet  = 0;
			
			if (dedtype == "leave") {
				details.leavetype = lm(document).find("#typeofleave").val();
				
				if ( lm(document).find("#formonetselect").val() == "1" || 
					 lm(document).find("#formonetselect").val() == 1 ) {
					 details.formonet = 1;
				} else {
					details.formonet = 0;
				}
			}
			
			if (dedtype == "ps") {
				details.pstype     = lm(document).find("#ps_type_select").val();
				details.timeout    = lm(document).find("#timeout").val();
				details.timebackin = lm(document).find("#timebackin").val();
			}
			
			if (dedtype == "cto" ) {
				//details.cto_start = lm(document).find("#cto_start").jqxDateTimeInput('getDate');
				//details.cto_end   = lm(document).find("#cto_end").jqxDateTimeInput('getDate');
				cs 				  = lm(document).find("#cto_start").jqxDateTimeInput('getDate');
					cs_hour 	  = $.jqx.dataFormat.formatdate(cs, 'hh');
					cs_mins 	  = $.jqx.dataFormat.formatdate(cs, 'mm');
					cs_ampm 	  = $.jqx.dataFormat.formatdate(cs, 'tt');
				details.cto_start = cs_hour +":"+cs_mins + " " + cs_ampm;
				
				ce 				  = lm(document).find("#cto_end").jqxDateTimeInput('getDate');
					ce_hour 	  = $.jqx.dataFormat.formatdate(ce, 'hh');
					ce_mins  	  = $.jqx.dataFormat.formatdate(ce, 'mm');
					ce_ampm 	  = $.jqx.dataFormat.formatdate(ce, 'tt');
				details.cto_end   = ce_hour +":"+ce_mins + " " + ce_ampm;
				
			//	console.log(details.cto_start);
			//	console.log(details.cto_end);
			//	return;
			}
		// console.log(details); return;
		performajax(["Leave/addleave_mgt",{data:details}], function(data) {
			if(data == true || data == "true") {
				alert("Data has been saved");		
				window.location.reload();
			} else if (data == null) {
				alert("No available leave credits.");
			}
		});
		
		/*
		performajax(['Leave/getleavecredits', {empid:empid_credit}], function(data) {
			var in_lmgt = new leavemgt();
				in_lmgt.print_to_leavetable(data,"#leavetable");
				
				lm(document).find("#modal_addleave").fadeOut("fast");
		})
		*/
	})
	
	lm(document).on("click","#modal_cancel", function() {
		lm(document).find("#modal_addleave").fadeOut("fast");
	})
		
	var iscoc = false;
	// add COC balance
	lm(document).on("click","#otbalance",function() {
		lm(document).find("#otbalance_row").fadeIn();
		lm(document).find(".vlslflspl").fadeOut();
		lm(document).find("#otherdeds").fadeIn();
		lm(this).fadeOut();
		
		iscoc = true;
	})
	
	// other deductions 
	lm(document).on("click","#otherdeds",function() {
		lm(document).find("#otbalance_row").fadeOut();
		lm(document).find(".vlslflspl").fadeIn();
		lm(document).find("#otbalance").fadeIn();
		lm(this).fadeOut();
		
		iscoc = false;
	})
	
	// forward balance
		lm(document).on("click","#forwardbalance", function() {
			
				var fdo 		 = new Object();
					fdo.empid 	 = empid_credit;
					fdo.vlbal 	 = lm(document).find("#vl_balance").val();
					fdo.slbal    = lm(document).find("#sl_balance").val();
					fdo.flbal    = lm(document).find("#fl_balance").val();
					fdo.splbal   = lm(document).find("#spl_balance").val();
					fdo.coc      = lm(document).find("#coc_balance").val();
					fdo.dateasof = null;
					
					var selecteddate = lm(document).find("#addbalance_cal").jqxDateTimeInput('getDate');
						
						var month    = selecteddate.getMonth()+1;
						var day      = selecteddate.getDate(); // deprecated, getDate
						var year     = selecteddate.getFullYear();
						fdo.dateasof = month+"/"+day+"/"+year;
						
						// console.log(fdo); return;
				if (iscoc != true) {
					performajax(["Leave/forwardbalance",{f_details:fdo}], function(data) {
						if (data == true || data == "true") {
							lm(document).find("#modal_addleave").fadeOut("fast");
							performajax(['Leave/getleavecredits', {empid:empid_credit}], function(data) {
								alert("Balance has been forwarded");
								window.location.reload();
								var in_lmgt = new leavemgt();
									in_lmgt.print_to_leavetable(data,"#leavetable");
							})
						}
					});
				} else {
					// coc_ot_balance
					var coc 		= new Object();
						coc.empid 	= empid_credit;
						coc.thedate = fdo.dateasof;
						coc.cocval 	= lm(document).find("#coc_ot_balance").val();
						
						performajax(["Leave/saveto_OT",{ cocdet : coc }],function(data) {
							if (data['saved']) {
								alert("Overtime is saved.");
								window.location.reload();
								jQuery("#modal_addleave").modal("hide");
							}
						})
				}
			
		})
	// end forward balance
	
	// is half day 
		lm(document).on("change","#ishalfday",function(){
			var a = lm(this).val();
			
			if (a == "yes") {
				lm(document).find("#days_deds").val("");
				lm(document).find("#hrs_deds").val("4"); //.attr("disabled","disabled");
			} else if (a == "no") {
				// lm(document).find("#hrs_deds").val("").removeAttr("disabled");
				lm(document).find("#hrs_deds").val("");
				lm(document).find("#days_deds").val(thefulldate.length);
			}

		})
	// end half day 
	
	// Overtime date

	var saveas 		 = new Object();
		saveas.dates = [];
	lm(document).find("#ot_date").jqxDateTimeInput({ width: "100%", height: 25 });
	lm(document).find("#ot_date").on('close', function (event) {
		t_date 			   = lm('#ot_date').jqxDateTimeInput('value'); 
		
		var ot_month       = t_date.getMonth()+1;
		var ot_day         = t_date.getDate();
		var ot_year        = t_date.getFullYear();
		
		saveas.dates = [];
		saveas.dates.push( ot_month+"/"+ot_day+"/"+ot_year );
		
	})

	// am pm td 
		var is_am = false;
		var is_pm = false;
		
		lm(document).on("click","#am_chck",function(e) {
			if (is_am == false) {
				is_am = true;
				lm(document).find("#am_td").addClass("td_check")
			} else {
				is_am = false;
				lm(document).find("#am_td").removeClass("td_check")
			}
		})
		
		lm(document).on("click","#pm_chck",function() {
			if (is_pm == false) {
				is_pm = true;
				lm(document).find("#pm_td").addClass("td_check")
			} else {
				is_pm = false;
				lm(document).find("#pm_td").removeClass("td_check")
			}
			
		})
	// end 
	
	// Overtime Time
	lm(document).find(".timeinput_ot").jqxDateTimeInput({ 
		width:"100%",
		formatString: 't', 
		showTimeButton: true, 
		showCalendarButton:false 
	});
	
	var ot_tasktype = null;
	lm(document).on("click","#rw",function(e) {
		if( e.target.id == "rw") {
			// rw = mult x 1
			ot_tasktype = 1;
			saveas.mult		 = "1";
		}
	})

	lm(document).on("click","#st",function(e) {
		if( e.target.id == "st") {
			// rw = mult x 1
			ot_tasktype = 2;
			saveas.reason_rw = "";
			saveas.mult		 = "1.5";
		}
	})	
	

	
	// saving OT mark OT
	// saving of OT from the admin 
		lm(document).on("click","#saveot_btn", function() {
				saveas.typemode       = "OT";
				saveas.tasktobedone   = "";
				
				var in_  = null;
				var out_ = null;
				
				if (is_am == true) {
					saveas.isam 		  = true;
					saveas.am_in 		  = lm(document).find("#am_in").val(); // AM in
					saveas.am_out 	      = lm(document).find("#am_out").val(); // PM out
					
					if (is_pm != true) {
						in_  = saveas.am_in;
						out_ = saveas.am_out;
					}
				}
				
				if (is_pm == true) {
					saveas.ispm			  = true;
					saveas.pm_in 		  = lm(document).find("#pm_in").val(); // AM in
					saveas.pm_out 	      = lm(document).find("#pm_out").val(); // PM out
					
					if (is_pm == true) {
						in_  = saveas.am_in;
						out_ = saveas.pm_out;
					}
				}
				
				saveas.tasktype 	  = ot_tasktype;
				saveas.remarks_ot 	  = "";
					
				saveas.division_chief_id = 0;
				saveas.dbm_chief_id 	 = 0;
				
				/*
				saveas.mult				 = 1.5;
				if (saveas.ot_tasktype == 1) {
					saveas.reason_rw = "";
					saveas.mult		 = 1;
				}
				*/
				
				// in used
					saveas.timein 		  = in_;
					saveas.timeout 	      = out_;
				// end 
				
				saveas.calc_elc 	 = true;
				saveas.empid 		 = empid_credit;
				
				// console.log(saveas);
				
				performajax(['Leave/fileot',{ details : saveas }], function(data){
					window.location.reload();
				})
				
		})
	// end saving OT
	
	// awarding of credits 
		lm(document).on("click","#addleavecredit", function() {
			empid_credit  = lm(this).data("empid");
			performajax(["Leave/award_credit",{ a_emp_id : empid_credit }], function(data) {
				console.log(data);
				if (data == true){
					performajax(['Leave/getleavecredits', {empid:empid_credit}], function(data) {
						var in_lmgt = new leavemgt();
							in_lmgt.print_to_leavetable(data,"#leavetable");
					})
				} else {
					alert("Not added")
				}
			});
		})
	// end of awarding
	
	// remaining fl listen to click
	/*
		lm(document).on("click", "#rem_fl", function() {
			var emp_id = lm(this).data("empid");
			lm(document).find("#remaining_number").html("<i class='fa fa-spinner fa-spin' style='font-size:24px'></i>");
			performajax(['My/remaining',
							{ 
							  empid : emp_id , 
							  type : "FL" 
							}
						], function(data) {
							lm(document).find("#remaining_number").text(data);
						})
			lm(document).find("#modal_remaining").show();
		})
	*/
	// end 
	
	// remaining SPL listen to click
		/*
		lm(document).on("click", "#rem_spl", function() {
			var emp_id = lm(this).data("empid");
			lm(document).find("#remaining_number").html("<i class='fa fa-spinner fa-spin' style='font-size:24px'></i>");
			performajax(['My/remaining',
							{ 
							  empid : emp_id , 
							  type : "SPL" 
							}
						], function(data) {
							lm(document).find("#remaining_number").text(data);
						})
			lm(document).find("#modal_remaining").show();
		})
		*/
	// end
	
	// close remaining window 
		lm(document).on("click","#close_rem_win", function() {
			lm(document).find("#modal_remaining").hide();
		})
	// end
	
})

function leavemgt() {
	var empslist = null;
	this.init_ = function() {
		var in_lmgt = new leavemgt();
			// get the list of employees 
			performajax(['Leave/get_emps'], function(data) {
				
				if (data.length != 0) {
					empslist = data;
					in_lmgt.list(data,"#emps_list");
				} else {
					// no record retrieved
				}
			})
			// end get
			
			// search method
			lm("#searchemp").on("keyup", function() {
				lm(document).find("#emps_list").children().remove();
				
				var search = lm(this).val().toUpperCase();
				
				for(var i = 0; i <= empslist.length-1; i++) {
					var text  = String(empslist[i].f_name);
					var index = text.indexOf(search);
					if (index != -1) {
						lm("<li data-empid='"+empslist[i].employee_id+"'>"+empslist[i].f_name+"</li>").appendTo("#emps_list")
					}
				}
			})
			// end search
			
			// li power
			lm(document).on("click","#emps_list li", function() {
				
				lm(document).find(".content_wrapper .rightbox").show().animate({
					"width":"99.93333%"
				}, 300).css({"float":"right"});
				
				// "margin-left":"-286px"
				// "left" : "230px"
				lm(document).find(".content_wrapper .leftcontent").animate({
					// "margin-left":"-286px"
					"width":"0%"
				},300, function() {
					var curr_class = lm(document).find("#closeemps").attr("class");
					var hide_class = null;
					
						if ( curr_class == "close_to_left" ) {
							hide_class = "open_to_right";
						} else if ( curr_class == "open_to_right" ) {
							hide_class = "close_to_left";
						}
						
						lm(document).find("#closeemps").removeClass(curr_class).addClass(hide_class);
				});
								
				var empid   = lm(this).data("empid");
				
				lm(this).addClass("selectedname").siblings().removeClass("selectedname");
				
				// for top label
				lm(document).find("#name_div_box").addClass("margin_left__");
				
					lm(document).find("#spl_span_val").html("N/A");
					performajax(['My/remaining',
									{ 
									empid : empid , 
									type : "SPL" 
									}
								], function(data) {
									lm(document).find("#spl_span_val").text(data);
								})
				
					lm(document).find("#fl_span_val").html("N/A");
					performajax(['My/remaining',
									{ 
									  empid : empid , 
									  type : "FL" 
									}
								], function(data) {
									lm(document).find("#fl_span_val").text(data);
								})
				
				var empname = lm(this).text().toLowerCase();
				// end for top label
				
				//lm(document).find("#employeename").html("<div class='btn-group' style='float:right;'><button class='btn btn-default addleave btn-sm' id='show_addleave' data-empid='"+empid+"'> <i class='fa fa-plus-square' aria-hidden='true'></i> Record Leave / Add Balance </button> <button class='btn btn-primary btn-sm' id='addleavecredit' data-empid='"+empid+"'> <i class='fa fa-trophy' aria-hidden='true'></i> Award Credit </button></div>"+empname);
				lm(document).find("#the_name_of_emp").html("<div class='btn-group' style='float:right;'><button class='btn btn-default addleave btn-sm' id='show_addleave' data-empid='"+empid+"'> <i class='fa fa-plus-square' aria-hidden='true'></i> Record Leave / Add Balance </button> <button class='btn btn-primary btn-sm' id='addleavecredit' data-empid='"+empid+"'> <i class='fa fa-trophy' aria-hidden='true'></i> Award Credit </button></div>"+empname);
				lm("<div class='btn-group' style='float:right;'><button class='btn btn-default btn-sm' id='rem_fl' data-empid='"+empid+"'>Remaining FL</button><button class='btn btn-primary btn-sm' id='rem_spl' data-empid='"+empid+"'>Remaining SPL</button></div>").prependTo("#employeename");
				// leavetable 
				
				// mark here 1 :: leave/management -> get rid 
				// https://office.minda.gov.ph:9003//my/ledger
				var lll   = window.location.href,
					newll = lll.substr( 0, lll.length-17 );
				
				lm(document).find("#ctoottbl").attr({ "href" : newll+"my/ledger/coc/"+empid });
				
				//  <i class='fa fa-plus' aria-hidden='true'></i>
				// <i class='fa fa-pencil' aria-hidden='true'></i>
				performajax(['Leave/getleavecredits', {empid:empid}], function(data) {
					in_lmgt.print_to_leavetable(data,"#leavetable");
				})
			})
		
	},
	
	this.list = function(thelist, holder) {
		// holder should be UL
		lm(document).find(holder).children().remove(); 
		for(var i=0;i<=thelist.length-1;i++) {
			lm("<li data-empid='"+thelist[i].employee_id+"'>"+thelist[i].f_name+"</li>").appendTo(holder);
		}
	},
	
	this.print_to_leavetable = function(data) {	
		lm("#leavetable").children().remove();
		var in_lmgt = new leavemgt();
		
		if (data.length == 0) {
			lm("<p style='padding: 35px; text-align: center; font-size: 20px; color: #9a9494; text-shadow: 0px 1px 0px #fff; font-family: calibri light;'> Silence is always golden. </p>").appendTo("#leavetable");
		}
		
		// row_contents
		// var here = lm(".row_contents");
		
		var grp_id 	  = null;
		var elc_id 	  = null;
		var elc_empid = null;
		
		var elc_obj    = new Object();
			elc_obj.id = [];
			
		var red_first_bal = "red_first_bal"; // used in the first row
		
		for(var i=0;i<=data.length-1;i++) {	
			grp_id    = data[i].grp_id;
			elc_id    = data[i].elc_id;
			elc_empid = data[i].emp_id;
		
			elc_obj.id.push( data[i].elc_id );
			
			var here = "row_id_"+i;
			lm("<div>", {class:"row_contents", id:here}).appendTo("#leavetable");
				var a = "hello";
				
				lm("<div>").attr({class:"periodbox floatdiv", "data-elc_id":elc_id})
					.append("<p class='leavemgt_option'>"+
							data[i].period_date +
							"<span class='earned_span'> "+data[i].type_of_credit+" </span>"+
							"</p>")
						.appendTo("#"+here).on("click", function() {
							
							if (!isadmin || isadmin == false) {
								return;
							}
							
							var elc_id = lm(this).data("elc_id");
							
							lm(document).find(".option_div_pop").remove();
							
							lm("<div>").attr({ class:"option_div_pop"}).appendTo( lm(this) );
							lm("<ul data-elc_id ='"+elc_id+"'>")
									.append("<li id='recallbtn'> <i class='fa fa-history' aria-hidden='true'></i> Recall </li>")
										.on("click", function() {
											var del_elc = lm(this).data("elc_id");
											var conf = confirm("Are you sure want to recall this?");
											
											//if (conf) { // mark elc
												var a = in_lmgt.return_the_value(elc_empid, elc_obj, del_elc);
												if (grp_id != null) {
													performajax(['Leave/cancel_leave',{ group_id : grp_id }], function(ret) {		
														if (ret == true) {
															alert("The application has been recalled.");
														}
													})	
												}
												/*
												else {
													performajax(['Leave/delete_from_elc',{ elcid : elc_id }], function(ret) {
														if (ret == true) {
															var a = return_the_value(elc_empid, elc_ids);
														}
													})
												}
												*/
											//}
										})
								.appendTo( ".option_div_pop" );
							lm(document).find(".option_div_pop").animate({
								"margin-left":"0px"
							},300);
						});				
				// particulars 
				// var typeofdeductions = (data[i].leave_code==null)?"&nbsp;":data[i].leave_code;
				var typeofdeductions = data[i].particulars.type_of_credit;
				lm("<div>", { class:"particularsbox floatdiv" })
					.append("<div class='particular-small-heads'>"+
								"<div class='particular-header'>"+
									"<div class='particular-small-head-det floatdiv'>"+
										"<p> "+typeofdeductions+" </p>"+
									"</div>"+
									"<div class='particular-small-head-det floatdiv'>"+
									  "<p> &nbsp; </p>"+
									"</div>"+
									"<div class='particular-small-head-det floatdiv'>"+ 
									  "<p> "+data[i].particulars.days+" </p>"+ // days of deduction
									"</div>"+
									"<div class='particular-small-head-det floatdiv'>"+
									  "<p> "+data[i].particulars.hrs+" </p>"+	// hrs of deduction
									"</div>"+
									"<div class='particular-small-head-det floatdiv'>"+
									  "<p> "+data[i].particulars.mins+" </p>"+	// mins of deduction
									"</div>"+
								"</div>"+
							"</div>")
						.appendTo("#"+here);
				// end of particulars
				
				lm("<div>", { class:"vacationleavebox floatdiv" })
					.append("<div class='vacationleave-small-heads'>"+
							  "<div class='vl-small-head-det floatdiv'>"+
								"<p> "+ data[i].vacation.earned +" </p>"+ // earned leave
							  "</div>"+
							  "<div class='vl-small-head-det floatdiv'>"+
								"<p> "+ data[i].vacation.abs_w_pay +" </p>"+				   // ABS/UT with pay
							  "</div>"+
							  "<div class='vl-small-head-det floatdiv'>"+
								//"<p> "+ vacation.new_balance +" </p>"+	 // Balance
								"<p class='redbalance "+red_first_bal+" '> "+ data[i].vacation.balance +" </p>"+	 // Balance
							  "</div>"+
							  "<div class='vl-small-head-det floatdiv'>"+
								"<p> "+ data[i].vacation.abs_wo_pay +" </p>"+				   // ABS/UT without pay
							  "</div>"+
							"</div>")
						.appendTo("#"+here);
				// end vacation leave
				
				// sick leave 	
				lm("<div>", { class:"sickleavebox floatdiv" })
					.append("<div class='sickleavebox-small-heads'>"+
							  "<div class='earnedsl sl-small-head-det floatdiv'>"+
								"<p> "+ data[i].sick.earned +" </p>"+
							  "</div>"+
							  "<div class='absutwithpaysl sl-small-head-det floatdiv'>"+
								"<p> "+ data[i].sick.abs_w_pay +" </p>"+
							  "</div>"+
							  "<div class='balancesl sl-small-head-det floatdiv'>"+
								"<p class='redbalance "+red_first_bal+" '> "+ data[i].sick.balance +" </p>"+
							  "</div>"+
							  "<div class='absutwopaysl sl-small-head-det floatdiv'>"+
								"<p> "+ data[i].sick.abs_wo_pay +" </p>"+
							  "</div>"+
							"</div>")
						.appendTo("#"+here);
				// end sick leave
				red_first_bal = "";
		}
	
	},
	
	this.return_the_value = function(empid, elc_ids, delete_id) {
	//	console.log(elc_ids);
		
		performajax(["Leave/return_the_values",
						{ 
						  elcids : elc_ids, 
						  emp_id : empid , 
						  delete_ : delete_id 
						}
					],function(data){
						
						var grp_id = null;
						
						
						if (data[0] == true) {
							grp_id = data[1];
							
							if (grp_id != null) {
								performajax(['Leave/cancel_leave',{ group_id : grp_id }], function(ret) {
									//if (ret == true) {
										performajax(['Leave/getleavecredits', {empid:empid}], function(data) {
												var in_lmgt = new leavemgt();
												in_lmgt.print_to_leavetable(data,"#leavetable");
										})
									//}
								})
							} else {
								performajax(['Leave/getleavecredits', {empid:empid}], function(data) {
									var in_lmgt = new leavemgt();
									in_lmgt.print_to_leavetable(data,"#leavetable");
								})
							}
							
						}
						
		})

	},
	
		this.countString = function(hay, needle) {
			var count = 0;
			for(var i = 0 ; i<=hay.length-1;i++) {
				if (hay[i]==needle) {
					count++;
				} 
			}
			return count;
		}
		
	}