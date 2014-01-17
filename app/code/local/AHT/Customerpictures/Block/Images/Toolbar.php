<?php
class AHT_Customerpictures_Block_Images_Toolbar extends Mage_Core_Block_Template
{
	private function _getCustomer(){
		return Mage::getSingleton('customer/session');
	}
	
	public function getListImages(){
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
		return $imageCollection;
	}
	
	public function getTotalNum(){
		return count($this->getListImages());
	}
	
	public function getPerPage(){
		if($this->getRequest()->getControllerName()=='user')
			return explode(",", Mage::getStoreConfig('customerpictures/user/perpage'));
		else
			return explode(",", Mage::getStoreConfig('customerpictures/page/perpage'));
	}
	
	public function getPagesUrl(){
		$perpage = $this->getPerPage();
		
		if($this->getRequest()->getControllerName()=='user'){
			$url = Mage::getBaseUrl().'customerpictures/user/index';
		}
		else{
			if($this->getRequest()->getActionName()=='user'){
				$url = Mage::getBaseUrl().'customerpictures/index/user/id/'.$this->getRequest()->getParam('id');
			}
			else{
				if($this->getRequest()->getActionName()=='winner'){
					$url = Mage::getBaseUrl().'customerpictures/index/winner';
				}
				else{
					$url = Mage::getBaseUrl().'customerpictures/index/index';
				}
			}
		}
			
		if($view = $this->getRequest()->getParam('view'))
			$url.='?view='.$view;
		else
			$url.='?view='.$perpage[0];
			
		if($sort = $this->getRequest()->getParam('sort'))
			$url.='&sort='.$sort;
		else
			$url.='&sort=viewed';
			
		return $url;
	}
	
	public function divPage($currentPage = 0, $div = 8){
		$perPage = $this->getPerPage();
		if($this->getRequest()->getParam('view'))
			$rows = $this->getRequest()->getParam('view');
		else
			$rows = $perPage[0];
		
		$total = $this->getTotalNum();
		$currentPage = $this->getRequest()->getParam('p');
		$url = $this->getPagesUrl();
			
		if(!$total || !$rows || !$div || $total<=$rows) 
			return false;
		$nPage = floor($total/$rows) + (($total%$rows)?1:0);
		$nDiv  = floor($nPage/$div) + (($nPage%$div)?1:0);
		$currentDiv = floor($currentPage/$div) ;
		$sPage = '<ol>';
		if($currentDiv&&$currentDiv>0) {
			$sPage .= '<li><a href="'.$url.'&p='.($currentDiv*$div-1).'">Prev</a></li>';
		}
		$count =($nPage<=($currentDiv+1)*$div)?($nPage-$currentDiv*$div):$div;
		for($i=1;$i<=$count;$i++){
	   		$page = ($currentDiv*$div + $i);
	    	$sPage .= '<li '.(($page==$currentPage)?'class="current"':'').'>'.(($page==$currentPage)?'':'<a href="'.$url.'&p='.($currentDiv*$div + $i ).'">').($page).(($page==$currentPage)?'':'</a>').'</li>';
		}
		if($currentDiv < $nDiv - 1){        
			$sPage .= '<li><a href="'.$url.'&p='.(($currentDiv+1)*$div).'">Next</a></li>';
		}
		$sPage .= '</li></ol>';
		return $sPage;
	}
	
	public function getPagesHtml(){
		if($this->getRequest()->getControllerName()=='user'){
			$url = Mage::getBaseUrl().'customerpictures/user/index';
		}
		else{
			if($this->getRequest()->getActionName()=='user'){
				$url = Mage::getBaseUrl().'customerpictures/index/user/id/'.$this->getRequest()->getParam('id');
			}
			else{
				if($this->getRequest()->getActionName()=='winner'){
					$url = Mage::getBaseUrl().'customerpictures/index/winner';
				}
				else{
					$url = Mage::getBaseUrl().'customerpictures/index/index';
				}
			}
		}
			
			$url.='?view=9000';
			
		if($sort = $this->getRequest()->getParam('sort'))
			$url.='&sort='.$sort;
		else
			$url.='&sort=viewed';
		
		if($p = $this->getRequest()->getParam('p'))
			$url.='&p='.$p;
		else
			$url.='&p=1';
			
		
		$pages = $this->getPerPage();
		$patterns = array();
		
		foreach($pages as $value){
			$patterns[] = str_replace("9000", $value, $url);
		}
		
		if($p = $this->getRequest()->getParam('p'))
			$url.='&p='.$p;
		$html ='<div class="limiter">';
		$html.='<strong>'.$this->__('View: ').'</strong>';
		$html.='<ol>';
		foreach($pages as $key=>$page){
			$html.='<li';
			if($this->getRequest()->getParam('view')==$page){
				$html.=' class="current"';
				$html.='>'.$page.'</li>';
			}
			else{
				$html.='><a href="'.$patterns[$key].'">'.$page.'</a></li>';
			}
				
			
		}
		$html.='</ol></div>';
		return $html;
	}
	
	public function getSortBy(){
		
		$perpage = $this->getPerPage();
		
		if($this->getRequest()->getControllerName()=='user'){
			$url = Mage::getBaseUrl().'customerpictures/user/index';
		}
		else{
			if($this->getRequest()->getActionName()=='user'){
				$url = Mage::getBaseUrl().'customerpictures/index/user/id/'.$this->getRequest()->getParam('id');
			}
			else{
				if($this->getRequest()->getActionName()=='winner'){
					$url = Mage::getBaseUrl().'customerpictures/index/winner';
				}
				else{
					$url = Mage::getBaseUrl().'customerpictures/index/index';
				}
			}
		}
		
		if($view = $this->getRequest()->getParam('view'))
			$url.='?view='.$view;
		else
			$url.='?view='.$perpage[0];
			
			$url.='&sort=defaultsort';
		
		if($p = $this->getRequest()->getParam('p'))
			$url.='&p='.$p;
		else
			$url.='&p=1';
	
		$html='<label>'.$this->__('Sort By:').'</label>';
		$html.='<ul>';
		
		$arrSort = array('viewed'=>'Most view', 'customerpictures_image_id'=>'Most popular');
		$i =0;
		foreach($arrSort as $key=>$_sort){
			$i++;
			$html.='<li class="';
			if($i==1)
				$html.='first';
			if($this->getRequest()->getParam('sort')==$key){
				$html.=' current';
				$html.='">'.$_sort.'</li>';
			}
			else{
				$html.='"><a href="'.str_replace('defaultsort', $key, $url).'">'.$_sort.'</a></li>';
			}
		}
		
		$html.='</ul>';
		
		
		return $html;
	}
}