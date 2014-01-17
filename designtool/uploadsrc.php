<?php
if(!empty($_FILES)){
	$filename = $_REQUEST['filename'];
	$tmpfile = $_FILES['Filedata']['tmp_name'];
	$targetfile = "uploads/".$filename;
	move_uploaded_file($tmpfile,$targetfile);
	echo "uploaded";
}
?>