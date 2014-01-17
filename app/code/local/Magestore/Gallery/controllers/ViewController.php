<?php
class Magestore_Gallery_ViewController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		if (!Mage::getStoreConfig('gallery/info/enabled')) $this->_redirect('no-route');
		 $this->_redirect('gallery');
    }
	public function albumAction() {
			if (!Mage::getStoreConfig('gallery/info/enabled')) $this->_redirect('no-route');
			$album_id = $this->getRequest()->getParam('id');		
			if($album_id != null && $album_id != '')	{
					$album = Mage::getModel('gallery/album')->load($album_id);
			} else {
					$album = null;
			}	
			if ($album) {
				Mage::register('current_album', $album);
					$this->loadLayout();     
					$this->renderLayout();
			} else {
				$this->_forward('noRoute');
			}
	}
}