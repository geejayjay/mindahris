<?php 
	$this->load->view("pds/head.blade.php");
	//	echo view("head"); 
?>
	<h3> Personal Data Sheet <a href='<?php echo base_url(); ?>/pds'> 
		<i class="fa fa-home thehome" aria-hidden="true"></i> </a> 
		<?php 
			$this->load->model("pdsmodel/Mainprocs");
			$id = $this->Mainprocs->get_pidn();
			
			if ($id != null) {
				echo "<a href='" . base_url() . "accounts/logout' class='thehome' style='font-size: 16px; margin-top: 6px; margin-right: 12px;'>Logout</a>";
			}
		?>
	</h3>

		<div class='row inheight'>
			<div class='col-md-2 leftbg'>
				<?php 
					echo $this->load->view("pds/side.blade.php",$tabs, true);
					//echo view("side")->with($tabs); 
				?>
			</div>
			<div class='col-md-10 rightbg removepadleft'>
				<div class='bigtabs'>
					<?php 
						echo $this->load->view("pds/rightside.blade.php",$tabs, true);
						// echo view("rightside")->with($tabs); 
					?>
				</div>
				<div class='contenttabs'>
					<?php 
						echo $this->load->view($content,$tabs, true);
					?>
				</div>
			</div>
		</div>
<?php 
	echo $this->load->view("pds/foot.blade.php",'',true);
// echo view("foot"); 
?>