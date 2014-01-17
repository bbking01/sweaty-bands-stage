<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Model_Post_Tag extends Fishpig_Wordpress_Model_Term
{
	public function _construct()
	{
		$this->_init('wordpress/post_tag');
	}
	
	/**
	 * Retrieve the taxonomy type
	 *
	 * @return string
	 */
	public function getTaxonomy()
	{
		return 'post_tag';
	}
	
	/**
	 * Loads a category model based on a post ID
	 * 
	 * @param int $postId
	 */
	public function loadByPostId($postId)
	{
		$this->load($postId, 'object_id');
		return $this;
	}
	
	/**
	 * Loads the posts belonging to this category
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */    
    public function getPostCollection()
    {
    	if (!$this->hasPostCollection()) {
			$posts = Mage::getResourceModel('wordpress/post_collection')
    			->addIsPublishedFilter()
    			->addTagIdFilter($this->getId());
    			
    		$this->setPostCollection($posts);
    	}
    	
    	return $this->_getData('post_collection');
    }

	/**
	 * Gets the category URL
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return Mage::helper('wordpress')->getUrl(trim(Mage::helper('wordpress/router')->getTagBase(), '/') . '/' . $this->getSlug()) . '/';
	}
}
