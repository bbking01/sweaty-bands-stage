<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Plugin_Yarpp extends Fishpig_Wordpress_Helper_Plugin_Abstract
{
	/**
	 * Prefix for options field in options table
	 *
	 * @var string|null
	 */
	protected $_optionsFieldPrefix = 'yarpp';
	
	/**
	 * Postfix for options field in options table
	 *
	 * @var string|null
	 */
	protected $_optionsFieldPostfix = '';

	/**
	 * Determine whether the plugin has been enabled in the WordPress Admin
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return true;
	}

	/**
	 * Retrieve a collection of related posts
	 *
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	*/
	public function getRelatedPostCollection(Fishpig_Wordpress_Model_Post $post)
	{
		return Mage::getResourceModel('wordpress/post_collection')
			->addIsPublishedFilter()
			->addFieldToFilter('ID', array('in' => $this->getRelatedPostIds($post)));
	}
	
	/**
	 * Retrieve an array of related post ID's
	 *
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return array|false
	*/
	public function getRelatedPostIds(Fishpig_Wordpress_Model_Post $post)
	{
		$helper = Mage::helper('wordpress/database');

		$select = $helper->getReadAdapter()
			->select()
			->from($helper->getTableName('yarpp_related_cache'), 'ID')
			->where('reference_ID=?', $post->getId())
			->where('score > ?', 0)
			->order($this->getOrder() ? $this->getOrder() : 'score DESC')
			->limit($this->getLimit() ? $this->getLimit() : 5);

		try {
			return $helper->getReadAdapter()->fetchCol($select);
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e->getMessage());
		}
		
		return array();
	}
}
