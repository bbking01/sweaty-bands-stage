<?php
class Magestore_Gallery_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		if (!Mage::getStoreConfig('gallery/info/enabled')) $this->_redirect('no-route');	
		$this->loadLayout();     
		$this->renderLayout();

		//$this->_redirect('/');
    }
}