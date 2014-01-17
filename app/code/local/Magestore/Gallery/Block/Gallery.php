<?php
class Magestore_Gallery_Block_Gallery extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getGallery()     
     { 
        if (!$this->hasData('gallery')) {
            $this->setData('gallery', Mage::registry('gallery'));
        }
        return $this->getData('gallery','asc');
        
    }
	public function getNewAlbums()
	{
		$total = $this->getTotalAlbum();

		$collection_new_album = Mage::getModel('gallery/album')->getCollection()	
		->addOrder('main_table.order','ASC')
		->addOrder('album_id','DECS')
		->setPageSize($total);

		return $collection_new_album;
	}
	
	public function getPhotos() {
		$collection = Mage::getModel('gallery/gallery')->getCollection()->addFieldToFilter('status', true);
		if (Mage::registry('current_album')) $collection->addFieldToFilter('album_id' , Mage::registry('current_album')->getId());
		else if ($this->getData('album_id')) {
			$collection->addFieldToFilter('album_id' , $this->getData('album_id'));
			$album = Mage::getModel('gallery/album')->load($this->getData('album_id'));
			if ($album) {
				Mage::register('current_album', $album);
			}
		}
		$collection
		->addOrder('main_table.order','ASC')
		->addOrder('gallery_id','DESC');
		return $collection;
	}

	public function getDescription() {
		if (Mage::registry('current_album')) return Mage::registry('current_album')->getContent();
		return '';
	}

	public function getAlbums() {
		$collection = Mage::getModel('gallery/album')->getCollection()
						->addFieldToFilter('status', true)
						->addOrder('main_table.order','ASC')
						->addOrder('album_id', 'DESC');
		return $collection;
	}
	
	public function getTotalAlbumValue()
	{
		return $this->getTotalAlbum();
	}
	
	public function getUrlRewrite(Mage_Core_Model_Abstract $obj){
		//echo "obj=".$obj;echo "<br>"; 
		
		$rewrite = Mage::getModel('core/url_rewrite')->load($obj->getUrlRewriteId());
		
		return trim(Mage::getBaseUrl(),'/')."/". $rewrite->getRequest_path();
	}
	
}