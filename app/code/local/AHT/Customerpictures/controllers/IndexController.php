<?php
class AHT_Customerpictures_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		if(Mage::getStoreConfig('customerpictures/general/enabled')){
			$this->loadLayout();     
			$head = $this->getLayout()->getBlock('head');
			$head->setTitle(Mage::getStoreConfig('customerpictures/page/title'));
			$head->setKeywords(Mage::getStoreConfig('customerpictures/page/keyword'));
			$head->setDescription(Mage::getStoreConfig('customerpictures/page/desc'));
			$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
			$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Home Page'), 'link'=>Mage::getBaseUrl()));
			$breadcrumbs->addCrumb('customerpictures', array('label'=>'Customer pictures', 'title'=>'Customer pictures'));
			$this->renderLayout();
		}
		else{
			$url = Mage::getUrl('');
			$this->_redirectUrl($url);
			return;
		}
    }
	
	private function _getImage(){
		$id = $this->getRequest()->getParam('id');
		return Mage::getModel('customerpictures/images')->load($id);
	}
	
	public function viewAction()
    {
		$this->loadLayout();     
		$head = $this->getLayout()->getBlock('head');
		$image = $this->_getImage();
		$viewed = $image->getViewed();
		$viewed+=1;
		$image->setViewed($viewed);
		$image->save();
		
		$title = $image->getImageTitle().' | '.Mage::getStoreConfig('customerpictures/page/title');
		$head->setTitle($title);
		$head->setKeywords(Mage::getStoreConfig('customerpictures/page/keyword'));
        $head->setDescription($image->getImageDescription());
		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Home Page'), 'link'=>Mage::getBaseUrl()));
		$breadcrumbs->addCrumb('customerpictures', array('label'=>Mage::helper('cms')->__('Customer pictures'), 'title'=>Mage::helper('cms')->__('Customer pictures'), 'link'=>Mage::getBaseUrl().'customerpictures'));
		$breadcrumbs->addCrumb('name', array('label'=>$image->getImageTitle(), 'title'=>$image->getImageTitle()));
		$this->renderLayout();
    }
	
	public function userAction()
    {
		$this->loadLayout();     
		$head = $this->getLayout()->getBlock('head');
		if($id=$this->getRequest()->getParam('id')){
			$customer=Mage::getModel('customer/customer')->load($id);		
			$head->setTitle($customer->getName().' | '.$this->__('Customer pictures'));
			$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
			$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Home Page'), 'link'=>Mage::getBaseUrl()));
			$breadcrumbs->addCrumb('customerpictures', array('label'=>Mage::helper('cms')->__('Customer pictures'), 'title'=>Mage::helper('cms')->__('Customer pictures'), 'link'=>Mage::getBaseUrl().'customerpictures'));
			$breadcrumbs->addCrumb('name', array('label'=>$customer->getName(), 'title'=>$customer->getName()));
			$this->renderLayout();
		}
		else{
			$url = Mage::getUrl('customerpictures');
			$this->_redirectUrl($url);
			return;
		}
    }
	
	public function winnerAction(){
		$this->loadLayout();     
		$head = $this->getLayout()->getBlock('head');
		$head->setTitle($this->__('Customer pictures - Winners archive'));
		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbs->addCrumb('home', array('label'=>Mage::helper('cms')->__('Home'), 'title'=>Mage::helper('cms')->__('Home Page'), 'link'=>Mage::getBaseUrl()));
		$breadcrumbs->addCrumb('customerpictures', array('label'=>Mage::helper('cms')->__('Customer pictures'), 'title'=>Mage::helper('cms')->__('Customer pictures'), 'link'=>Mage::getBaseUrl().'customerpictures'));
		$breadcrumbs->addCrumb('name', array('label'=>$this->__('Winners archive'), 'title'=>$this->__('Winners archive')));
		$this->renderLayout();
	}
	
	public function likeAction(){
		$id =$this->getRequest()->getParam('id');
		$href = Mage::getUrl('customerpictures/index/view').'id/'.$id;
		$xmlFile = 'https://api.facebook.com/method/fql.query?query=select like_count from link_stat where url="'.$href.'"';
		$xml = simplexml_load_file($xmlFile);
		echo '<span style="font:12px/1.5em Arial,Helvetica,sans-serif">'.$xml->link_stat->like_count.'</span>';
	}
	
	public function percentAction(){
		$height = $this->getRequest()->getParam('h');
		$width = $this->getRequest()->getParam('w');
		$heightResize = $this->getRequest()->getParam('hresize');
		$hdb = $this->getRequest()->getParam('ho');
		$x = $this->getRequest()->getParam('x');
		$y = $this->getRequest()->getParam('y');
		
		$percent = ($heightResize*100)/$height;
		
		$xResize = ($x*$percent)/100;
		$yResize = ($y*$percent)/100;
		$ho = ($hdb*$percent)/100;
		$widthResize = ($width*$percent)/100;
		
		
		
		echo json_encode(array('x'=>$xResize, 'y'=>$yResize, 'w'=>$widthResize, 'h'=>$ho));
	}
}