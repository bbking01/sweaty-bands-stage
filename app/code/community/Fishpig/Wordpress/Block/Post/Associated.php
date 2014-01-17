<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Post_Associated extends Mage_Core_Block_Template
{
	/**
	 * Cache for post collection
	 *
	 * @param null|Fishpig_Wordpress_Model_Resource_Post_Collection
	 */
	protected $_postCollection = null;

	/**
	 * Retrieve the association entity type
	 *
	 * @param string
	 */
	public function getEntity()
	{
		if (!$this->hasEntity()) {
			$this->setEntity('product');
		}
		
		return $this->_getData('entity');
	}

	/**
	 * Retrieve the current Magento entity object
	 *
	 * @return false|Mage_Core_Model_Abstract
	 */
	public function getObject()
	{
		if (!$this->hasObject()) {
			if (!$this->getEntity()) {
				$this->setObject(false);
				return false;
			}
			
			if ($this->getEntity()==='cms_page') {
				$this->setObject(Mage::getSingleton('cms/page'));
			}
			else {
				$this->setObject(Mage::registry($this->getEntity()));
			}
		}
		
		return $this->_getData('object');
	}
	
	/**
	 * Retrieve the posts collection
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Post_Collection
	 */
	public function getPostCollection()
	{
		if (is_null($this->_postCollection)) {
			$this->_postCollection = false;
			
			if (!$this->getObject()) {
				return false;
			}
			
			$helper = $this->helper('wordpress/associations');

			if ($this->getObject() instanceof Mage_Catalog_Model_Product) {
				$this->_postCollection = $helper->getAssociatedPostsByProduct($this->getObject());
			}
			else if ($this->getObject() instanceof Mage_Cms_Model_Page) {
				$this->_postCollection = $helper->getAssociatedPostsByCmsPage($this->getObject());
			}
		}

		return $this->_postCollection;
	}
}
