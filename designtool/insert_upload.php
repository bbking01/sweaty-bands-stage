<?php			
require '../app/Mage.php';
Mage::app();
	 
Mage::getSingleton("core/session", array("name" => "frontend"));
$session = Mage::getSingleton("customer/session");

$imageName = $_REQUEST['img'];		
$imageId = $_REQUEST['images_id'];

if($session->isLoggedIn() && $imageName != '')
{
	$customer_id = $session->getCustomerId();
	$model = Mage::getModel('design/userimage');
	$data['customer_id'] = $customer_id;
	$data['imgname'] = $imageName;
	$model->setData($data);
	$model->save();
	echo $model->getId();
	exit;
	

}else if($session->isLoggedIn() && $imageId != '')
{
	$customer_id = $session->getCustomerId();
	$imageDir = Mage::getBaseDir(). DS .'designtool' . DS .'uploads'. DS;
	$collection = Mage::getModel('design/userimage')->getCollection();
	$collection->addFieldToFilter('id',$imageId);
	$collection->addFieldToFilter('customer_id',$customer_id);	
	foreach($collection as $obj){
		if (file_exists($imageDir.$obj->getImgname())){
			unlink($imageDir.$obj->getImgname());
		}
		if (file_exists($imageDir.$obj->getVectorname())){
			unlink($imageDir.$obj->getVectorname());
		}	
		$obj->delete();
	}
	exit;
}
?>
