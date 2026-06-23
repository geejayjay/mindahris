
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header">Import Employee</h3>
        </div>



        <div id="jqxLoader"></div>

         <div id="jqxPopover" style="display:none;">
            <center>


                <div class="alert alert-warning" id="warning_msg" >
                    <p style="text-align: justify;">In order to prevent errors and accurate information. <br>You need to ensure to match the csv fields from data fields. <br>If you are sure with the matching fields above select <strong>CONTINUE</strong> if not <strong>CANCEL</strong> then  you need to match the right fields.</p>
                    <p style="text-align: justify;"><b>All duplicated data will be automatically ignored!</b></p>
                </div>
                <button id="btn_continue_import" class="btn btn-sm btn-warning">Continue import</button>
                <button id="btn_cancel_import" class="btn btn-sm btn-danger">Cancel</button>

            </center>
        </div>



        <div class="col-lg-12">
	    	<div class="form-group">

		    			<a href="<?php echo base_url(); ?>personnel/employee" class="btn btn-sm btn-default"><< Back</a>
		
	    	</div>
        </div>
       
         <div class="col-lg-12">
			<div class="panel panel-default">
			    <div class="panel-heading">
			    	Import Data
			    </div>
			    <div class="panel-body">

			    	<div class="form-group">
				    	<label class="label label-default">SELECT AREA</label>
				    	<div style="margin-top:10px; margin-bottom:10px;" id='jqxcomboarea'></div>

				    </div>


			        <div class="form-group">
                        <label  class="label label-default">UPLOAD EMPLOYEES</label>
                        <p style="margin-top:5px; font-size:12px; font-style:italic;">note: Only supports Comma Separated Value (.csv) format only. </p>

                        <div style="margin-top:10px; margin-bottom:10px;" id="jqxemployeeupload"></div>
                        <div id="log"></div>
                    </div>

                    <div class="form-group">
                    		<label  id="label_fields" style="display:none;" class="label label-default">MATCH FIELDS</label>
                    		<div style="margin-top:10px; margin-bottom:10px;" id="jqxgridfields"></div>
                    </div>

                    <div class="form-group">
                     	<button style="display:none;" id="jqxbtnimport" class="btn btn-success"> Start import</button>                	
                     </div>   


                     	 <div class="row" style="display:none;" id="import_msg_row">
		                     <div class="col-md-6">
			                     <div class="form-group">
			                     	<div class="alert alert-success" id="import_msg" >
			                                Successfully import attendance logs.....
			                            </div>
			                     </div>
		                     </div>
	                     </div>                 

    
			    </div>

			</div>
        </div>


    </div>
</div>
<!-- /#page-wrapper -->

<script type="text/javascript">

		var BASE_URL = '<?php echo base_url(); ?>';

        $(document).ready(function () {



        	 /*AREA  LIST */

			var area = <?php echo json_encode($areas);?>;

			var UPLOADEDCSV = ""; 

            var source =
            {
                datatype: "json",
                datafields: [
                    { name: 'area_id' },
                    { name: 'area_name' }
                ],
                localdata: area,
     
            };

            var dataAdapter = new $.jqx.dataAdapter(source);
            // Create a jqxComboBox
            $("#jqxcomboarea").jqxComboBox({ searchMode: 'containsignorecase' , selectedIndex: 0, source: dataAdapter, displayMember: "area_name", valueMember: "area_id", width: 200});

            $("#jqxcomboarea").on('select', function (event) {
                if (event.args) {
                    var item = event.args.item;
                    if (item) {
                        
                       var area_id = item.value;
                       var area_name = item.label;
                    }
                }
            });

            /*END AREA LIST*/


           $('#jqxPopover').jqxPopover({
                width: 600,
                height: 220,
                offset: {left: 0, top:0},
                showArrow: true,
                showCloseButton: true,
                isModal: true,
                selector: $("#jqxbtnimport"),
                title: 'Import Data Warning'
            });

           $('#btn_cancel_import').on('click',function(){

           	 $('#jqxPopover').jqxPopover('close');

           });            


            $("#jqxLoader").jqxLoader({ isModal: true, width: 100, height: 60, imagePosition: 'top' });

            $('#jqxemployeeupload').jqxFileUpload({
			    theme: 'energyblue',
			    width: 300,
			    uploadUrl: BASE_URL + 'personnel/uploademployees',
			    fileInputName: 'fileInput',
			    multipleFilesUpload: false,
			    accept : '.csv',
			    browseTemplate: 'success', uploadTemplate: 'primary',  cancelTemplate: 'danger'
			});


            $('#jqxemployeeupload').on('uploadStart', function (event) {
			    var fileName = event.args.file;
			    $('#log').prepend('<label class="label label-info">filename: <strong>' + fileName + '</strong></label>');
			    $('#label_fields').show();
			});			


			$('#jqxemployeeupload').on('uploadEnd', function (event) {
			    var args = event.args;
			    var fileName = args.file;
			    var serverResponce = args.response;

			    try {
			        obj = JSON.parse(serverResponce);
			    } catch (e) {
			        $('#import_msg_row').show();
			        var className = $('#import_msg').attr('class');
			        $('#import_msg').removeClass(className);
			        $('#import_msg').addClass('alert alert-danger');
			        $('#import_msg').html('Invalid server response.');
			        $('#label_fields').hide();
			        return;
			    }

			    if (obj.status === 'error') {
			        $('#import_msg_row').show();
			        var className = $('#import_msg').attr('class');
			        $('#import_msg').removeClass(className);
			        $('#import_msg').addClass('alert alert-danger');
			        $('#import_msg').html(obj.message || 'Upload failed.');
			        $('#label_fields').hide();
			        return;
			    }

			    employeefields = obj['employeefields']




			    csvfields = obj['csvfields'];
			    uploadedda = obj['data'];

				var arr = [];

			   	for (i in csvfields){
			   		arr.push( { 'field' : csvfields[i]  , 'test' : csvfields[i]} );
			   	}


			 	var csvsource =
	            {
	                 datatype: "array",
	                 datafields: [
	                     { name: 'field', type: 'string' }
	                 ],
	                 localdata: arr
	            };

	            var csvAdapter = new $.jqx.dataAdapter(csvsource, {
	                autoBind: true
	            });


	            var source =
	            {
	                localdata: employeefields,
	                datatype: "array",
	            };

	            var dataAdapter = new $.jqx.dataAdapter(source, {
	                loadComplete: function (data) { },
	                loadError: function (xhr, status, error) { }      
	            });


	            $("#jqxgridfields").jqxGrid(
	            {
	                source: dataAdapter,
	                autoheight:true,
	                editable: true,
	                selectionmode: 'singlecell',
	                width:500,
	                columns: [
	                  { text: 'Data fields' , datafield: 'columns' ,width: 200 ,  align: 'left' , cellsalign: 'left' , editable: false},
                      {
                         text: 'Csv fields', datafield: 'fields' , columntype: 'combobox',
                         createeditor: function (row, value, editor) {
                             editor.jqxComboBox({ source: csvAdapter, displayMember: 'field' , valueMember: 'field'});
                         }
                      } 

	                ]
	            });


	            $('#jqxbtnimport').show();

	            $('#btn_continue_import').on('click' , function(){


	            	var area_id = $('#jqxcomboarea').val();

	            	var fieldmap = [];
	            	var checkmap = new Array();
 

					var rows = $('#jqxgridfields').jqxGrid('getrows');
					var result = "";



					var allow = 0;

				    for(var i = 0; i < rows.length; i++)
				    {

						 var row = rows[i];

						 	 if (typeof(rows[i].fields) =='undefined' || rows[i].fields == '')
						 	 { 
						 	 	allow = 0; 
						 	 	break; 
						 	
						 	 } else {

						 	 	fieldmap.push( { 'checkinout' : row.columns  , 'csv' : row.fields } );
						 	 	allow = 1;
						 	 }

				    }



				    if(allow === 1){

		  					try
		  					{

								 $.ajax({
							   		 type: 'POST',
							   		 url: BASE_URL + '/personnel/savecsvdata',
							   		 data: { 'fieldmap' : fieldmap , 'uploadedda' :  JSON.stringify(uploadedda) , 'area_id' : area_id },
							   		 dataType : 'json',
							   		 beforeSend: function(){

							   		 	$('#jqxPopover').jqxPopover('close');
	            						$('#jqxLoader').jqxLoader('open');
	            						$('.jqx-loader-text').html('Importing Employees...Please Wait...');		

							   		 },
							   		 success: function (data) {

							   		 	$('#import_msg_row').show();
							   		 	$('#jqxLoader').jqxLoader('close');
							   		 	var className = $('#import_msg').attr('class');
							   		 	$('#import_msg').removeClass(className);
				    					 $('#import_msg').addClass('alert alert-success');
							   		 	$('#jqxbtnimport').prop('disabled', true);
							   		 	$('#import_msg').html(' Successfully import attendance logs.....');
							   		 	$('#jqxPopover').jqxPopover('close');

							   		
							   		 }
								 });


		                    }
		                    catch(e)
		                    { 	
				                 $('#import_msg_row').show();
				                 $('#jqxLoader').jqxLoader('close');
						    	 var className = $('#import_msg').attr('class');
						    	 $('#import_msg').removeClass(className);
						    	 $('#import_msg').addClass('alert alert-danger');
						    	 $('#import_msg').html('Ooops! Something went wrong with your import. Please try again.');
						    	 $('#jqxPopover').jqxPopover('close');
						    

		                    }	

				    }else{

				    	 $('#import_msg_row').show();
				    	 $('#jqxLoader').jqxLoader('close');
				    	 var className = $('#import_msg').attr('class');
				    	 $('#import_msg').removeClass(className);
				    	 $('#import_msg').addClass('alert alert-danger');
				    	 $('#import_msg').html('You need to set all fields');
				    	 $('#jqxPopover').jqxPopover('close');
				    	
				    	

				    }


				});

			}); /*end jqxattendanceupload event */



        });


</script>