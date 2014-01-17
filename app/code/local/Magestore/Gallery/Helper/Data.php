<?php

class Magestore_Gallery_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function checkEmailDuplicationAjaxUrl()
    {
        return $this->_getUrl('gallery/account/checkemailduplication');
    } 
	public function getGalleryproduct()
	{
		$products = Mage::getModel('catalog/product')->getcollection()->addAttributeToFilter('name','Idea Gellery Product');
		$product_data = $products->getData();
		return $product_data[0]['entity_id'];
	}
	
}