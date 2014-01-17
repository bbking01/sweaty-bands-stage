<?php
class AHT_Customerpictures_Block_Images_List extends Mage_Core_Block_Template
{
	private function _getCustomer(){
		return Mage::getSingleton('customer/session');
	}
	
	public function getPerPage(){
		if($this->getRequest()->getControllerName()=='user')
			return explode(",", Mage::getStoreConfig('customerpictures/user/perpage'));
		else
			return explode(",", Mage::getStoreConfig('customerpictures/page/perpage'));
	}
	
	public function getColumnCount(){
		if($this->getRequest()->getControllerName()=='user')
			return Mage::getStoreConfig('customerpictures/user/column');
		else
			return Mage::getStoreConfig('customerpictures/page/column');
	}
	
	public function getListImages(){
		$perPage = $this->getPerPage();
		$imageCollection = Mage::getModel('customerpictures/images')
			->getCollection();
		if($this->getRequest()->getControllerName()=='user'){
			$imageCollection->addFieldToFilter('user_id', $this->_getCustomer()->getId());
		}
		else{
			$imageCollection->addFieldToFilter('status', 2);
			$imageCollection->addFieldToFilter('user_status', 0);
			
			if($this->getRequest()->getActionName()=='winner'){
				$imageCollection->addFieldToFilter('winner_time', array('neq' => ''));
			}
			
			if($this->getRequest()->getActionName()=='user'){
				$imageCollection->addFieldToFilter('user_id', $this->getRequest()->getParam('id'));
			}
		}
		
		if($sort = $this->getRequest()->getParam('sort'))
			$imageCollection->setOrder($sort, 'DESC');
		else
			$imageCollection->setOrder('viewed', 'DESC');
			
		if($this->getRequest()->getParam('view'))
			$view = $this->getRequest()->getParam('view');
		else
			$view = $perPage[0];
		
		if($this->getRequest()->getParam('p'))
			$p = $this->getRequest()->getParam('p');
		else
			$p = 1;
			
		if($view!='All')
			$imageCollection->getSelect()->limitPage($p, $view);
		
		return $imageCollection;
	}
	
	public function getWidth(){
		if($this->getRequest()->getControllerName()=='user'){
			$width = Mage::getStoreConfig('customerpictures/user/width');
		}
		else{
			$width = Mage::getStoreConfig('customerpictures/page/width');
		}
		return $width;
	}
	
	public function getHeight(){
		if($this->getRequest()->getControllerName()=='user'){
			$width = Mage::getStoreConfig('customerpictures/user/height');
		}
		else{
			$width = Mage::getStoreConfig('customerpictures/page/height');
		}
		return $width;
	}
	
	public function getImageLink($id){
		
		if($this->getRequest()->getControllerName()=='user'){
			$image = Mage::getModel('customerpictures/images')->load($id);
			if($image->getWinnerTime()!='')
				return $this->getUrl('customerpictures/index/view').'id/'.$id;
			else
				return $this->getUrl('customerpictures/user/editpicture').'id/'.$id;	
		}
		else{
			return $this->getUrl('customerpictures/index/view').'id/'.$id;
		}
	}
	
	public function getImageUrl($name, $userId){
		$width = $this->getWidth();
		$height = $this->getHeight();
		
		$url = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$userId.DS."thumb".DS.$name; 
		return Mage::helper('customerpictures/data')->reSize($url, $name, 'images',$userId, $width, $height);
	}
	
	public function getDeleteImageUrl($id){
		return $this->getUrl('customerpictures/user/del').'id/'.$id;
	}
	
	public function getHideImageUrl($id){
		return $this->getUrl('customerpictures/user/hide').'id/'.$id;
	}
	
	public function getShowImageUrl($id){
		return $this->getUrl('customerpictures/user/show').'id/'.$id;
	}
	
	public function getStatus($i){
		switch ($i) {
			case 0:
				return $this->__('Pending');
				break;
			case 1:
				return $this->__('Denied');
				break;
			case 2:
				return $this->__('Approved');
				break;
		}
	}
	
	public function getPictureStatus($id){
		$image = Mage::getModel('customerpictures/images')->load($id);
		if($image->getWinnerTime()!=''){
			return $this->__('Winner');
		}
		else{
			return $this->getStatus($image->getStatus());
		}
	}
	
	public function getFacebookKey(){
		return Mage::getStoreConfig('customerpictures/facebook/appkey');
	}
	
	public function getFacebookSrc($id){
		$facebookUrl = 'http://www.facebook.com/plugins/like.php?api_key='.$this->getFacebookKey().'&href='.$this->getUrl('customerpictures/index/view').'id/'.$id.'&layout=button_count&node_type=link&sdk=joey&show_faces=1&action=like&font=arial&colorscheme=light';
		return $facebookUrl;
	}
}