<?php	
require '../app/Mage.php';		
Mage::app();

$resource = Mage::getSingleton('core/resource');
$read = $resource->getConnection('core_read');

$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

if(isset($_GET['pid']))
{
	$pid = $_GET['pid'];			
	
}


$productData = Mage::getModel('catalog/product')->load($pid);
$product = Mage::getModel('design/configarea')->load($pid,'product_id');

	
$no_of_sides = $productData->getAttributeText('no_of_sides'); //getNo_of_sides();	 

  $flashvar = '<?xml version="1.0" encoding="iso-8859-1"?><coordinate>'; 
  
 if($no_of_sides == '1')
 {	 
  $flashvar.="<IMAGES>";
   $flashvar.="<IMAGE PATH='".$path.'media/catalog/product'.$productData->getFront_image()."'>";
  $flashvar.="<COORDINATE>";
  $flashvar.="<X>".$product->getFa_x()."</X>";
  $flashvar.="<Y>".$product->getFa_y()."</Y>";
  $flashvar.="<W>".$product->getFa_width()."</W>";
  $flashvar.="<H>".$product->getFa_height()."</H>";
  $flashvar.="</COORDINATE>";
  $flashvar.="</IMAGE>";
  $flashvar.="</IMAGES>";
 
 }else if($no_of_sides == '2')
 {
  $flashvar.="<IMAGES>";
  
  $flashvar.="<IMAGE PATH='".$path.'media/catalog/product'.$productData->getFront_image()."'>";
  $flashvar.="<COORDINATE>";
  $flashvar.="<X>".$product->getFa_x()."</X>";
  $flashvar.="<Y>".$product->getFa_y()."</Y>";
  $flashvar.="<W>".$product->getFa_width()."</W>";
  $flashvar.="<H>".$product->getFa_height()."</H>";
  $flashvar.="</COORDINATE>";
  $flashvar.="</IMAGE>";
  
  $flashvar.="<IMAGE PATH='".$path.'media/catalog/product'.$productData->getBack_image()."'>";
  $flashvar.="<COORDINATE>";
  $flashvar.="<X>".$product->getBa_x()."</X>";
  $flashvar.="<Y>".$product->getBa_y()."</Y>";
  $flashvar.="<W>".$product->getBa_width()."</W>";
  $flashvar.="<H>".$product->getBa_height()."</H>";
  $flashvar.="</COORDINATE>";
  $flashvar.="</IMAGE>";
  
  $flashvar.="</IMAGES>";
 
 }else if($no_of_sides == '3')
 {

 		$flashvar.="<IMAGES>";
  
	  $flashvar.="<IMAGE PATH='".$path.'media/catalog/product'.$productData->getFront_image()."'>";
	  $flashvar.="<COORDINATE>";
	  $flashvar.="<X>".$product->getFa_x()."</X>";
	  $flashvar.="<Y>".$product->getFa_y()."</Y>";
	  $flashvar.="<W>".$product->getFa_width()."</W>";
	  $flashvar.="<H>".$product->getFa_height()."</H>";
	  $flashvar.="</COORDINATE>";
	  $flashvar.="</IMAGE>";
	  
	  $flashvar.="<IMAGE PATH='".$path.'media/catalog/product'.$productData->getBack_image()."'>";
	  $flashvar.="<COORDINATE>";
	  $flashvar.="<X>".$product->getBa_x()."</X>";
	  $flashvar.="<Y>".$product->getBa_y()."</Y>";
	  $flashvar.="<W>".$product->getBa_width()."</W>";
	  $flashvar.="<H>".$product->getBa_height()."</H>";
	  $flashvar.="</COORDINATE>";
	  $flashvar.="</IMAGE>";
	  
	  
	  $flashvar.="<IMAGE PATH='".$path.'media/catalog/product'.$productData->getLeft_image()."'>";
	  $flashvar.="<COORDINATE>";
	  $flashvar.="<X>".$product->getLe_x()."</X>";
	  $flashvar.="<Y>".$product->getLe_y()."</Y>";
	  $flashvar.="<W>".$product->getLe_width()."</W>";
	  $flashvar.="<H>".$product->getLe_height()."</H>";
	  $flashvar.="</COORDINATE>";
	  $flashvar.="</IMAGE>";
	  
	  $flashvar.="</IMAGES>";
 
 }else if($no_of_sides == '4')
 {
 
 		$flashvar.="<IMAGES>";
  
	  $flashvar.="<IMAGE PATH='".$path.'media/catalog/product'.$productData->getFront_image()."'>";
	  $flashvar.="<COORDINATE>";
	  $flashvar.="<X>".$product->getFa_x()."</X>";
	  $flashvar.="<Y>".$product->getFa_y()."</Y>";
	  $flashvar.="<W>".$product->getFa_width()."</W>";
	  $flashvar.="<H>".$product->getFa_height()."</H>";
	  $flashvar.="</COORDINATE>";
	  $flashvar.="</IMAGE>";
	  
	  $flashvar.="<IMAGE PATH='".$path.'media/catalog/product'.$productData->getBack_image()."'>";
	  $flashvar.="<COORDINATE>";
	  $flashvar.="<X>".$product->getBa_x()."</X>";
	  $flashvar.="<Y>".$product->getBa_y()."</Y>";
	  $flashvar.="<W>".$product->getBa_width()."</W>";
	  $flashvar.="<H>".$product->getBa_height()."</H>";
	  $flashvar.="</COORDINATE>";
	  $flashvar.="</IMAGE>";
	  
	  
	  $flashvar.="<IMAGE PATH='".$path.'media/catalog/product'.$productData->getLeft_image()."'>";
	  $flashvar.="<COORDINATE>";
	  $flashvar.="<X>".$product->getLe_x()."</X>";
	  $flashvar.="<Y>".$product->getLe_y()."</Y>";
	  $flashvar.="<W>".$product->getLe_width()."</W>";
	  $flashvar.="<H>".$product->getLe_height()."</H>";
	  $flashvar.="</COORDINATE>";
	  $flashvar.="</IMAGE>";
	  
	  
	  $flashvar.="<IMAGE PATH='".$path.'media/catalog/product'.$productData->getRight_image()."'>";
	  $flashvar.="<COORDINATE>";
	  $flashvar.="<X>".$product->getRi_x()."</X>";
	  $flashvar.="<Y>".$product->getRi_y()."</Y>";
	  $flashvar.="<W>".$product->getRi_width()."</W>";
	  $flashvar.="<H>".$product->getRi_height()."</H>";
	  $flashvar.="</COORDINATE>";
	  $flashvar.="</IMAGE>";
	  
	  $flashvar.="</IMAGES>";
 }
 
$flashvar .= '</coordinate>';

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $flashvar;
 
?>

