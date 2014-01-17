<?php			
require '../app/Mage.php';		
$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
Mage::getSingleton("core/session", array("name" => "frontend"));
$session = Mage::getSingleton("customer/session");

$customer_email = $session->getCustomer()->getEmail();
$customer_id = $session->getCustomerId();

$collection  = Mage::getModel('design/savedesign')
				->getCollection()
				->AddFieldToFilter('customer_id',$customer_id)
				->AddFieldToFilter('design_name',array('nlike'=>'share%'));
 
 if($customer_email != "")
{
	$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><AllUserDesign>';
	foreach($collection as $res)		
	{
		$xmlString .= '	<Userdesign>';	
		$xmlString .= '	<UserDesignName>'.$res->getDesignName().'</UserDesignName>';
		$xmlString .= '	<UserDesignID>'.$res->getDesignId().'</UserDesignID>';
		
		$xmlString .= '	<UserFrontImagePath>'.$path.'designtool/saveimg/'.$res->getFrontImage().'</UserFrontImagePath>';
		
		if($res->getBackImage()!= "")
			$xmlString .= '	<UserBackImagePath>'.$path.'designtool/saveimg/'.$res->getBackImage().'</UserBackImagePath>';
		
		if($res->getLeftImage()!= "")
			$xmlString .= '	<UserLeftImagePath>'.$path.'designtool/saveimg/'.$res->getLeftImage().'</UserLeftImagePath>';
		
		if($res->getLeftImage()!= "")	
			$xmlString .= '	<UserRightImagePath>'.$path.'designtool/saveimg/'.$res->getRightImage().'</UserRightImagePath>';
				
		$xmlString .= '	<UserXmlData>'.$res->getSaveString().'</UserXmlData>';
		$xmlString .= '	</Userdesign>';	
	}
	$xmlString .= '</AllUserDesign>';
	
	header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
	header ("Cache-Control: no-cache, must-revalidate");
	header ("Pragma: no-cache");
	header ("Content-type: text/xml");
	header ("Content-Description: PHP/INTERBASE Generated Data" );
	echo $xmlString;
}else{
	echo "Please check the login detail";
}

?>