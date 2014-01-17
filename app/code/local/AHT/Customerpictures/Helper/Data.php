<?php

class AHT_Customerpictures_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getCustomer(){
		return Mage::getSingleton('customer/session');
	}
	
	public function reSize($url, $name, $type, $customerId, $width, $height){ 
		//$customer = $this->getCustomer();
		$_imageUrl = $url;
		if($type == 'avatars'){
			$imageResized = Mage::getBaseDir('media').DS."customerpictures".DS."avatars".DS.$customerId.DS."resize".DS.$name; 
		}
		else{
			$imageResized = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$customerId.DS."resize".DS.$width.'x'.$height.DS.$name; 
		}
		if (!file_exists($imageResized)&&file_exists($_imageUrl)){
			$imageObj = new Varien_Image($_imageUrl); 
			$imageObj->constrainOnly(TRUE); 
			$imageObj->keepFrame(TRUE); 
			$imageObj->backgroundColor(array(255, 255, 255));
			$imageObj->keepAspectRatio(TRUE); 
			$imageObj->quality(100);
			$imageObj->resize($width, $height); 
			$imageObj->save($imageResized); 
		}
		if($type=='images'){
			return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'customerpictures/images/'.$customerId.'/resize/'.$width.'x'.$height.'/'.$name;
		}
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'customerpictures/'.$type.'/'.$customerId.'/resize/'.$name;
	}
	
	public function getFacebookLike($id){
		$src = Mage::getUrl('customerpictures/index/like').'id/'.$id;
		$html = '<iframe src="'.$src.'" width="20px" height="15px" frameborder="0" marginheight="0" marginwidth="0" scrolling="no" align="top"><p>Your browser does not support iframes.</p></iframe>';
		echo $html;
	}
}