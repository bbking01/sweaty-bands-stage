<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Sidebar_Widget_Yarpp extends Fishpig_Wordpress_Block_Sidebar_Widget_Abstract
{
	/**
	 * Set the posts collection
	 *
	 */
	protected function _beforeToHtml()
	{
		if ($this->helper('wordpress/plugin_yarpp')->isEnabled()) {
			parent::_beforeToHtml();

			$this->setPosts($this->_getPostCollection());
		}

		return $this;
	}
	
	/**
	 * Control the number of posts displayed
	 *
	 * @param int $count
	 * @return $this
	 */
	public function setPostCount($count)
	{
		return $this->setNumber($count);
	}
	
	/**
	 * Retrieve the current post object
	 *
	 * @return Fishpig_Wordpress_Model_Post|false
	 */
	public function getPost()
	{
		if (!$this->hasPost()) {
			return Mage::registry('wordpress_post');
		}
		
		return $this->_getData('post');
	}
	
	/**
	 * Adds on cateogry/author ID filters
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */
	protected function _getPostCollection()
	{
		if ($this->getPost()) {
			return $this->helper('wordpress/plugin_yarpp')
				->getRelatedPostCollection($this->getPost())
				->setPageSize($this->getNumber() ? $this->getNumber() : 5)
				->setCurPage(1)
				->load();
		}
		
		return array();
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return $this->__('Related Posts');
	}
}
