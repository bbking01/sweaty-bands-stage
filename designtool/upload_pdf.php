<?php
require '../app/Mage.php';

Mage::app("default");
$resource = Mage::getSingleton('core/resource');
$read = $resource->getConnection('core_read');

	$item_id = $_REQUEST['item_id'];
	$pdf = Mage::getModel('sales/order_item')->load($item_id);
	$pdfFile =	$pdf->getpdf_file();
	
if( $pdfFile != '')
{
	if(file_exists('pdf_files/'. $pdfFile))
		unlink('pdf_files/'.$pdfFile);	
}

if(isset($_FILES['pdf_file']) && $_FILES['pdf_file']['name'] != '')
{
	$file_parts = explode(".", $_FILES['pdf_file']['name']);
	$file_parts_rev = array_reverse($file_parts);
	$file_extension = $file_parts_rev[0];
	
	$filename = 'order-'.$pdf[0]['order_id'].'-'.$item_id.".".$file_extension;		
	copy($_FILES["pdf_file"]["tmp_name"], 'pdf_files/'.$filename);

	$pdf->setpdf_file($filename);
	$pdf->save();
}
if ($_SERVER['HTTP_REFERER'] != "")
	header("Location:".$_SERVER[HTTP_REFERER]);
	
?> 