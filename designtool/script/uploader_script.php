<?php
if ( isset ( $GLOBALS["HTTP_RAW_POST_DATA"] ))
{
$img = $GLOBALS["HTTP_RAW_POST_DATA"];
$file = fopen("../uploads/".$_GET['name'],"w");
fwrite($file,$img);
fclose($file);
} else echo 'An error occured.';

?>