<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Model_Resource_Post_Category_Collection extends Fishpig_Wordpress_Model_Resource_Term_Collection
{
	public function _construct()
	{
		$this->_init('wordpress/post_category');
	}
	
	/**
	 * Perform the joins necessary to create a full category record
	 */
	protected function _initSelect()
	{
		parent::_initSelect();
		
		$this->getSelect()->where('taxonomy.taxonomy=?', 'category');

		if (Mage::helper('wordpress')->isPluginEnabled('taxonomy-terms-order', false)) {
			$this->getSelect()->order('main_table.term_order ASC');
		}
		else {
			$this->getSelect()->order('main_table.term_id ASC');
		}
		
		return $this->getSelect();
	}
}
