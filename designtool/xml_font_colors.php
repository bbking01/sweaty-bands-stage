<?php
require '../app/Mage.php';
Mage::app("default");
$resource = Mage::getSingleton('core/resource');
$read = $resource->getConnection('core_read');

/*Code updated by bhagyashri to show only enable print color started*/
$collection = Mage::getModel('fontprintcolor/fontprintcolor')->getCollection()->AddFieldToFilter('status', 1);
/*Code updated by bhagyashri to show only enable print color ended*/

$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><colors>';
foreach ($collection as $color)
{
$xmlString .= '	<color>';
	$xmlString .= '	<colorID>'.$color->getFontprintcolor_id().'</colorID>';
	$xmlString .= '	<colorCode>'.$color->getColor_code().'</colorCode>';
	$xmlString .= '	<colorName>'.$color->getColor_name().'</colorName>';
	$xmlString .= '	</color>';	
}
$xmlString .= '</colors>';
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;
?>
