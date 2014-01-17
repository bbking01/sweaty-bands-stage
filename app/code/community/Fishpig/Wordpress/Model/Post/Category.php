<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Model_Post_Category extends Fishpig_Wordpress_Model_Term
{
	public function _construct()
	{
		$this->_init('wordpress/post_category');
	}

	/**
	 * Retrieve the taxonomy type
	 *
	 * @return string
	 */
	public function getTaxonomy()
	{
		return 'category';
	}
	
	/**
	 * Returns the amount of posts related to this object
	 *
	 * @return int
	 */
    public function getPostCount()
    {
    	return $this->getItemCount();
    }

	/**
	 * Retrieve a collection of children terms
	 *
	 * @return Fishpig_Wordpress_Model_Mysql_Term_Collection
	 */
	public function getChildrenCategories()
	{
		return $this->getChildrenTerms();
	}

	/**
	 * Retrieve the URI for the category
	 * This is a wrapper for the parent method and injects
	 * the category base if WordPress is configured to use this
	 *
	 * @return string|false
	 */
	public function getUri()
	{
		$helper = Mage::helper('wordpress/router');
		$uri = substr(parent::getUri(), strlen($this->getTaxonomy())+1);

		if (!$helper->categoryUrlHasBase()) {
			return $uri;
		}

		return $helper->getCategoryBase() . '/' . $uri;
	}
}
