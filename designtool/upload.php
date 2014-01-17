<?php 
if (isset($_FILES['Filedata'])) {
	if ($_FILES['Filedata']['name']) {
		$img = basename($_FILES['Filedata']['name']);
		//$randappend = time();
		//move_uploaded_file($_FILES['Filedata']['tmp_name'],'uploads/'.basename($_FILES['Filedata']['name']));
		move_uploaded_file($_FILES['Filedata']['tmp_name'],'./uploads/'.$_REQUEST['fname']);
		echo "uploaded";
	}	
}
?>