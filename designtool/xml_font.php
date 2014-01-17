<?php			
require '../app/Mage.php';		
$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
if(isset($_GET['cid']))
	$cid = $_GET['cid'];
else
	$cid='';

$collection = Mage::getModel('fontmanagement/addfont')->getCollection()->AddFieldToFilter('status','1');
if($cid!='')
	$collection->AddFieldToFilter('font_category_id',$cid);

$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><allFonts>';

foreach($collection as $res)		
{

	
	$xmlString .= '	<fonts>';
	$xmlString .= '	<fontName>'.$res->getFont_name().'</fontName>';
	$xmlString .= '	<fontID>'.$res->getFont_id().'</fontID>';
	$xmlString .= '	<parentID>'.$res->getFont_category_id().'</parentID>';
	$xmlString .= '	<filePath>'.$path.'media/font/'.$res->getFont_file().'</filePath>';		
	//$xmlString .= '	<fontImage>'.$path.'/media/font/images/'.$res->getFont_image().'</fontImage>';
	$xmlString .= '	</fonts>';			
}

$xmlString .= '</allFonts>';
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;
?>