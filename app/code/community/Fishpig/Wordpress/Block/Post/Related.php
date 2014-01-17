<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Post_Related extends Mage_Core_Block_Template
{
	/**
	 * Determine whether Yarpp is enabled
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->getAutoDisplay() && $this->helper('wordpress/plugin_yarpp')->isEnabled();
	}
	
	/**
	 * Retrieve the current post
	 *
	 * @return false|Fishpig_Wordpress_Model_Post
	 */
	public function getPost()
	{
		if (!$this->hasPost()) {
			$this->setPost(false);
			
			if ($this->getPostId()) {
				$post = Mage::getModel('wordpress/post')->load($this->getPostId());
				
				if ($post->getId()) {
					$this->setPost($post);
				}
			}
			else if ($post = Mage::registry('wordpress_post')) {
				$this->setPost($post);
			}
		}
		
		return $this->_getData('post');
	}
	
	/**
	 * Returns the collection of posts
	 *
	 * @return false|array
	 */
	public function getPosts()
	{
		if ($this->isEnabled()) {
			return $this->helper('wordpress/plugin_yarpp')
				->getRelatedPostCollection($this->getPost())
				->load();
		}

		return array();
	}
	
	/**
	 * Retrieve the post excerpt
	 *
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return string
	 */
	public function getPostExcerpt(Fishpig_Wordpress_Model_Post $post)
	{
		if ($excerpt = trim(strip_tags($post->getPostExcerpt()))) {
			$words = explode(' ', $excerpt);
			
			if (count($words) > $this->getExcerptLength()) {
				$words = array_slice($words, 0, $this->getExcerptLength());
			}
			
			return trim(implode(' ', $words), '.,!:-?"\'£$%') . '...';
		}
		
		return '';
	}
	
	/**
	 * Retrieve the HTML content that goes before the related post block
	 *
	 * @return string
	 */
	public function getBeforeBlockHtml()
	{
		return $this->_getData('before_related');
	}

	/**
	 * Retrieve the HTML content that goes after the related post block
	 *
	 * @return string
	 */	
	public function getAfterBlockHtml()
	{
		return $this->_getData('after_related');
	}
	
	/**
	 * Retrieve the HTML content that goes before a related entry
	 *
	 * @return string
	 */
	public function getBeforeEntryHtml()
	{
		return $this->_getData('before_title');
	}

	/**
	 * Retrieve the HTML content that goes after a related entry
	 *
	 * @return string
	 */
	
	public function getAfterEntryHtml()
	{
		return $this->_getData('after_title');
	}

	/**
	 * Retrieve the HTML content that goes before a post excerpt
	 *
	 * @return string
	 */
	public function getBeforeExcerptHtml()
	{
		return $this->_getData('before_post');
	}

	/**
	 * Retrieve the HTML content that goes after a post excerpt
	 *
	 * @return string
	 */	
	public function getAfterExcerptHtml()
	{
		return $this->_getData('after_post');
	}

	/**
	 * Determine whether to show a post excerpt
	 *
	 * @return bool
	 */
	public function canShowExcerpt()
	{
		return $this->_getData('show_excerpt') == '1';
	}
	
	/**
	 * Load the Yarpp options from the WordPress database
	 *
	 * @return $this
	 */
	protected function _beforeToHtml()
	{
		$options = unserialize($this->helper('wordpress')->getWpOption('yarpp'));
		
		if (is_array($options)) {
			if (isset($options['template'])) {
				$options['view'] = $options['template'];
				unset($options['template']);
			}
	
			$this->addData($options);
		}

		return parent::_beforeToHtml();
	}

	/**
	 * Determine whether the thumbnail is 
	 *
	 * @return bool
	 */
	public function isThumbnailView()
	{
		return $this->_getData('view') === 'thumbnails';
	}
	
	public function getThumbnailImage(Fishpig_Wordpress_Model_Post_Abstract $post)
	{
		if (($image = $post->getFeaturedImage()) !== false) {
			return $image->getAvailableImage();
		}
		
		return $this->_getData('thumbnails_default');	
	}
}
