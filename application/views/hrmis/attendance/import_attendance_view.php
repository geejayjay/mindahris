<div id="jqxLoader"></div>
	
<div id="jqxPopover" style="display:none;">
<center>
    <div class="alert alert-danger" id="warning_msg" >
        <p style="text-align: justify;">In order to prevent errors and accurate information. <br>You need to ensure to match the csv fields from data fields. <br>If you are sure with the matching fields above select <strong>CONTINUE</strong> if not <strong>CANCEL</strong> then  you need to match the right fields.</p>
        <p style="text-align: justify;"><b>All duplicated data will be automatically ignored!</b></p>
    </div>
    <button id="btn_continue_import" class="btn btn-sm btn-warning">Continue import</button>
    <button id="btn_cancel_import" class="btn btn-sm btn-danger">Cancel</button>

</center>
</div>


 <div class="content-wrapper">

       <section class="content-header">
        <h1>
            Import Attendance Checking Data
        </h1>
        <ol class="breadcrumb">
           <li class="active"><img style="margin-top:-14px;" src="<?php echo base_url();?>assets/images/minda/rsz_1minda_logo_text.png" /></li>
        </ol>
     </section>

     <section class="content">
       <div class="row">
         <div class="col-md-6">
           <div class="box">
               <div class="box-header with-border">
                  <h3 class="box-title">Import Time Logs CSV</h3>
                  <div class="box-tools pull-right">
                     <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  </div>
               </div>
               <!-- /.box-header -->
               <div class="box-body">
                  <div class="row">
                     <div class="col-md-12">
                      
					    	<div class="form-group">
						    	<label class="label label-default">SELECT AREA</label>
						    	<div style="margin-top:10px; margin-bottom:10px;" id='jqxcomboarea'></div>

						    </div>

					        <div class="form-group">
		                        <label  class="label label-default">UPLOAD ATTENDANCE LOG</label>
		                        <p style="margin-top:5px; font-size:12px; font-style:italic;">note: Only supports Comma Separated Value (.csv) format only. </p>

		                        <div style="margin-top:10px; margin-bottom:10px;" id="jqxattendanceupload"></div>
		                        <div id="log"></div>
		                    </div>

		                    <div class="form-group">
		                    		<label  id="label_fields" style="display:none;" class="label label-default">MATCH FIELDS</label>
		                    		<div style="margin-top:10px; margin-bottom:10px;" id="jqxgridfields"></div>
		                    </div>
		                     <div class="form-group">
		                     	<button style="display:none;" id="jqxbtnimport" class="btn btn-success"> Start import</button>                	
		                     </div>

                     </div>
                  </div>
                  <!-- /.row -->
               </div>
               <!-- ./box-body -->
               <!-- /.box-footer -->
            </div>
         </div>
        </div>
     </section>

  </div>  


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


             $("#jqxLoader").jqxLoader({ isModal: true, width: 350, height: 60, imagePosition: 'top' });

            /* UPLOAD ATTENDANCE */

            $('#jqxattendanceupload').jqxFileUpload({
			    theme: 'energyblue',
			    width: 300,
			    uploadUrl: BASE_URL + 'attendance/uploadattendance',
			    fileInputName: 'fileInput',
			    multipleFilesUpload: false,
			    accept : '.csv',
			    browseTemplate: 'success', uploadTemplate: 'primary',  cancelTemplate: 'danger'
			});

            $('#jqxattendanceupload').on('uploadStart', function (event) {
			    var fileName = event.args.file;
			    $('#log').prepend('<label class="label label-info">filename: <strong>' + fileName + '</strong></label>');
			    $('#label_fields').show();
			});

			$('#jqxattendanceupload').on('uploadEnd', function (event) {
			    var args = event.args;
			    var fileName = args.file;
			    var serverResponce = args.response;

			    try {
			        obj = JSON.parse(serverResponce);
			    } catch (e) {
			        showmessage('Invalid server response.', 'danger');
			        $('#label_fields').hide();
			        return;
			    }

			    if (obj.status === 'error') {
			        showmessage(obj.message || 'Upload failed.', 'danger');
			        $('#label_fields').hide();
			        return;
			    }

			    checkinoutfields = obj['checkinoutfields'];




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
	                localdata: checkinoutfields,
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
							   		 url: BASE_URL + '/attendance/savecsvdata',
							   		 data: { 'fieldmap' : fieldmap , 'uploadedda' : JSON.stringify(uploadedda) , 'area_id' : area_id },
							   		 dataType : 'json',
							   		 beforeSend: function(){
							   		 	$('#jqxPopover').jqxPopover('close');
	            						$('#jqxLoader').jqxLoader('open');
	            						$('.jqx-loader-text').html('Importing Attendance Checking Data...Please Wait...');
							   		 },
							   		 success: function (data) {

							   		 	$('#jqxLoader').jqxLoader('close');
							   		 	$('#jqxbtnimport').prop('disabled', true);		 
							   		 	showmessage('Successfully import attendance logs.....','success');
							   		 	$('#jqxPopover').jqxPopover('close');

							   		
							   		 }
								 });


		                    }
		                    catch(e)
		                    { 	
				                 $('#jqxLoader').jqxLoader('close');
				                 showmessage('Ooops! Something went wrong with your import. Please try again.' , 'danger');
						    	 $('#jqxPopover').jqxPopover('close');
						    

		                    }	

				    }else{

				    	 $('#jqxLoader').jqxLoader('close');
				    	 showmessage('You need to set all fields. In order to continue. Please try again..' , 'danger');
				    	 $('#jqxPopover').jqxPopover('close');
				    	
				    }


				});

			}); /*end jqxattendanceupload event */




        });



        /* FUNCTIONS ==========================================  */ 


</script>

