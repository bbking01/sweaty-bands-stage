<?php			
require '../app/Mage.php';
$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
Mage::app();
	 
Mage::getSingleton("core/session", array("name" => "frontend"));
$session = Mage::getSingleton("customer/session");

$resource = Mage::getSingleton('core/resource');
$read = $resource->getConnection('core_read');
	
 $vectorimg = $_REQUEST['filename'];
 $id = $_REQUEST['images_id'];

if($session->isLoggedIn() && $id != '' && $id!='NaN' && $vectorimg !='')
{
	$model = Mage::getModel('design/userimage')->load($id);
	
	$model->setVectorname($vectorimg);
	$model->save();		
	
	echo "true";
}
else
{
	echo "false";
}
?>
