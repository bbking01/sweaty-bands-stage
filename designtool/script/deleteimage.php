<?php
$file = '../uploadthumb/' . $_GET['imgName'];
if(isset($_GET['imgName']))
{
	if(!unlink($file))
	{
		echo "status=failure";
	}
	else
	{
		echo "status=success";
	}	
}
?>