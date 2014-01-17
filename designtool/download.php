<?php  session_start();
require '../app/Mage.php';
Mage::app();
$resource = Mage::getSingleton('core/resource');
$read = $resource->getConnection('core_read');

$item_id = $_REQUEST['item_id'];
$file_name = $_REQUEST['file_name'];
$fla_name = $_REQUEST['fla_name'];

$image_name = $_REQUEST['image_name'];
$printjpg = $_REQUEST['printjpg'];

$clipart_file_name = $_REQUEST['clipart'];
$clipartcat_file_name = $_REQUEST['clipartcat'];
$printcolor_file_name = $_REQUEST['printcolor'];
$font_file_name = $_REQUEST['font'];
$fontcat_file_name = $_REQUEST['fontcat'];

if(isset($item_id) && $item_id != '')
{
	$pdf_query = 'select pdf_file from sales_flat_order_item where item_id = '.$item_id;	
	$pdf = $read->fetchAll($pdf_query);
	
	//$dir   = "../pdf_files/";
	$dir   = "pdf_files/";
	$file = $pdf[0]['pdf_file'];
}else if(isset($file_name) && $file_name != '')
{
	$dir   = "./xml_files/";
	$file = $file_name;	
}
else if(isset($fla_name) && $fla_name != '')
{
	$dir   = "./fla/";
	$file = $fla_name;	
}//Code added by Bhagyashri Starts
else if(isset($image_name) && $image_name != '')
{
	$file = 'uploads/'.$image_name;	
}
else if(isset($printjpg) && $printjpg != '')
{
	$dir   = "./saveimg/";
	$file = $_REQUEST['printjpg'];
}
else if(isset($clipart_file_name) && $clipart_file_name != '')
{
	$dir   = "../media/clipart/import/";
	$file = $clipart_file_name;	
}
else if(isset($clipartcat_file_name) && $clipartcat_file_name != '')
{
	$dir   = "../media/clipart/import/";
	$file = $clipartcat_file_name;	
}
else if(isset($printcolor_file_name) && $printcolor_file_name != '')
{
	$dir   = "../media/printcolor/import/";
	$file = $printcolor_file_name;	
}
else if(isset($font_file_name) && $font_file_name != '')
{
	$dir   = "../media/font/import/";
	$file = $font_file_name;	
}
else if(isset($fontcat_file_name) && $fontcat_file_name != '')
{
	$dir   = "../media/font/import/";
	$file = $fontcat_file_name;	
}


if ((isset($file))&&(file_exists($dir.$file))) {
	
   header("Content-type: application/force-download");
   header('Content-Disposition: inline; filename="' . $dir.$file . '"');
   header("Content-Transfer-Encoding: Binary");
   header("Content-length: ".filesize($dir.$file));
   header('Content-Type: application/octet-stream');
   header('Content-Disposition: attachment; filename="' . $file . '"');
    
   readfile("$dir$file");
  

} else {
   echo "No file selected";
} //end if 

/*if ($_SERVER['HTTP_REFERER'] != "")
{	
	header("Location:".$_SERVER[HTTP_REFERER]);
}
*/?> 