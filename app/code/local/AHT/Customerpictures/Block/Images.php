<?php
class AHT_Customerpictures_Block_Images extends Mage_Core_Block_Template
{
	public function _getCustomer(){
		return Mage::getSingleton('customer/session');
	}
	
	public function getAvatarPath(){
		if($this->getRequest()->getControllerName()=='index'){
			if($this->getRequest()->getActionName()=='user')
				$customer = Mage::getModel('customer/customer')->load($this->getRequest()->getParam('id'));
			else
				$customer = Mage::getModel('customer/customer')->load($this->getImage()->getUserId());
		}
		else{
			$customer = $this->_getCustomer();
		}
		$user = Mage::getModel('customerpictures/users')->load($customer->getId());
		if($user->getAvatar()!=''){
			$url = Mage::getBaseDir('media').DS."customerpictures".DS."avatars".DS.$customer->getId().DS.$user->getAvatar(); 
			$path = $this->reSize($url, $user->getAvatar(), 'avatars', $customer->getId(), 115, 130); 
			return $path;
		}
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'customerpictures/avatars/no-avatar.gif';
	}
	
	public function getImageUrl($name, $userId){
		$width = $this->getWidth();
		$height = $this->getHeight();
		
		$url = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$userId.DS."thumb".DS.$name; 
		return $this->reSize($url, $name, 'images', $userId, $width, $height);
	}

	public function reSize($url, $name, $type, $customerId, $width, $height){ 
		return Mage::helper('customerpictures/data')->reSize($url, $name, $type, $customerId, $width, $height);
	}
	
	public function getUploadUrl($type){
		if($type=='avatars')
			return $this->getUrl('customerpictures/user/avatar');
		else
			return $this->getUrl('customerpictures/user/images');
	}
	
	public function getImage(){
		return Mage::getModel('customerpictures/images')->load($this->getRequest()->getParam('id'));
	}
	
	public function getTempDetailUrl(){
		return $this->getUrl('customerpictures/user/temp');
	}
	
	public function getEditImageAction(){
		return $this->getUrl('customerpictures/user/edit');
	}
	
	public function getCancelImage($image){
		return $this->getUrl('customerpictures/user/delete').'?image='.$image;
	}
	
	public function getWidth(){
		return Mage::getStoreConfig('customerpictures/view/width');
	}
	
	public function getHeight(){
		return Mage::getStoreConfig('customerpictures/view/height');
	}
	
	public function getViewAllUrl(){
		$image = $this->getImage();
		if($image->getWinnerTime()!='')
			return $this->getUrl('customerpictures/index/winner');
		else
			return $this->getUrl('customerpictures/index/user').'id/'.$image->getUserId();
	}
	
	public function getViewAllTitle(){
		$image = $this->getImage();
		if($image->getWinnerTime()!='')
			return $this->__('View all winners pictures');
		else
			return $this->__('View all username pictures');
	}
	
	public function getWinner(){
		$collection = Mage::getModel('customerpictures/images')
			->getCollection()
			->addFieldToFilter('status', 2)
			->addFieldToFilter('winner_time', array('neq' => ''))
			->addFieldToFilter('user_status', 0)
			->setOrder('winner_time', 'DESC')
			->setPageSize(1);
		return $collection;
	}
	
	public function getFacebookCommentWidth(){
		return Mage::getStoreConfig('customerpictures/facebook/width');
	}
	
	public function getFacebookCommentNum(){
		return Mage::getStoreConfig('customerpictures/facebook/num');
	}
	
	public function getFacebookKey(){
		return Mage::getStoreConfig('customerpictures/facebook/appkey');
	}
	
	public function getFacebookSrc($id){
		$facebookUrl = 'http://www.facebook.com/plugins/like.php?api_key='.$this->getFacebookKey().'&href='.$this->getUrl('customerpictures/index/view').'id/'.$id.'&layout=button_count&node_type=link&sdk=joey&show_faces=1&action=like&font=arial&colorscheme=light';
		return $facebookUrl;
	}
}