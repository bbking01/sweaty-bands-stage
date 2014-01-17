<?php			

require 'app/Mage.php';

Mage::app("default");

$resource = Mage::getSingleton('core/resource');

$read = $resource->getConnection('core_read');

$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);



$proxy = new SoapClient($path.'api/soap?wsdl');			

$sessionId = $proxy->login('naincy', '123456');

	

if(isset($_GET['cid']))

{

	$cid = $_GET['cid'];	

	$filters = array('font_category_id'=>$cid, 'status'=>1);

}else

{

	$filters = array('status'=>1);

}


$result = $proxy->call($sessionId, 'font.list', array($filters));


$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><allFonts>';



foreach($result as $res)		

{

	$xmlString .= '	<fonts>';

	$xmlString .= '	<fontName>'.$res['font_name'].'</fontName>';

	$xmlString .= '	<fontID>'.$res['font_id'].'</fontID>';

	$xmlString .= '	<parentID>'.$res['font_category_id'].'</parentID>';

	$xmlString .= '	<filePath>'.$path.'/media/font/'.$res['font_file'].'</filePath>';		

	$xmlString .= '	<fontImage>'.$path.'/media/font/images/'.$res['font_image'].'</fontImage>';

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