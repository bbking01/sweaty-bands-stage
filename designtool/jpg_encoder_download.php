<?php
$absolutepath = dirname(__FILE__);
if(isset($_GET['delname']))
{
	$returnVars = array();
	$returnVars['write'] = "yes";
	$returnString = http_build_query($returnVars);
	$absolutepath=dirname(dirname(__FILE__));
	//echo "Deleting FileName: ".$_GET['delname'];
	if( file_exists($absolutepath."/".$_GET['delname']) )
	{
		unlink($absolutepath."/".$_GET['delname']);
	}
}

else if ( isset ( $GLOBALS["HTTP_RAW_POST_DATA"] )) 
{
	

	 $im = $GLOBALS["HTTP_RAW_POST_DATA"];
	
	$filename=$_GET['name'];
	$fullFilePath=$absolutepath."/saveimg/".$filename;
		
	$handle=fopen($fullFilePath,"w");
	
	fwrite($handle,$im);
	fclose($handle);
	
	$returnVars = array();
	$returnVars['write'] = "yes";
	$returnString = http_build_query($returnVars);

//send variables back to Flash
	echo $returnString;


}
else echo 'An error occured2.';

?>
