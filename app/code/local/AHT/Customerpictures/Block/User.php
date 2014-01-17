<?php
class AHT_Customerpictures_Block_User extends Mage_Core_Block_Template
{
	private function _getCustomer(){
		return Mage::getSingleton('customer/session');
	}
	
	public function getTermAction(){
		return $this->getUrl('customerpictures/user/accept');
	}
	
	public function getAvatarPath(){
		$customer = $this->_getCustomer();
		$user = Mage::getModel('customerpictures/users')->load($customer->getId());
		if($user->getAvatar()!=''){
			return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'customerpictures/avatar/'.$customer->getId().'/'.$user->getAvatar();
		}
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'customerpictures/avatar/no-avatar.gif';
	}
	
	public function getTermAndConditions(){
		return Mage::getStoreConfig('customerpictures/user/term');
	}
	
}