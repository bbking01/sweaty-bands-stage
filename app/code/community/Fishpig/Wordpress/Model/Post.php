<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
class Fishpig_Wordpress_Model_Post extends Fishpig_Wordpress_Model_Post_Abstract
{
	/**
	 * Prefix of model events names
	 *
	 * @var string
	*/
	protected $_eventPrefix = 'wordpress_post';
	
	/**
	 * Parameter name in event
	 *
	 * In observe method you can use $observer->getEvent()->getObject() in this case
	 *
	 * @var string
	*/
	protected $_eventObject = 'post';

	/**
	 * Tag used to identify where to break the post content up for excerpt
	 *
	 * @var const string
	 */
	const TEASER_TAG = '<!--more-->';

	public function _construct()
	{
		$this->_init('wordpress/post');
	}

	/**
	 * Returns the permalink used to access this post
	 *
	 * @return string
	 */
	public function getPermalink()
	{
		if (!$this->hasData('permalink')) {
			$this->setData('permalink', Mage::helper('wordpress/post')->getPermalink($this));
		}
		
		return $this->getData('permalink');
	}

	/**
	 * Wrapper for self::getPermalink()
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->getPermalink();
	}
	
	/**
	 * Retrieve the post excerpt
	 * If no excerpt, try to shorten the post_content field
	 *
	 * @return string
	 */
	public function getPostExcerpt($includeSuffix = true)
	{
		if (!$this->getData('post_excerpt')) {
			if ($this->hasMoreTag()) {
				$excerpt = $this->_getPostTeaser($includeSuffix);
			}
			else {
				$excerpt = $this->getPostContent();
			}

			$this->setPostExcerpt($excerpt);
		}			

		return $this->getData('post_excerpt');
	}
	
	/**
	 * Determine twhether the post has a more tag in it's content field
	 *
	 * @return bool
	 */
	public function hasMoreTag()
	{
		return strpos($this->getPostContent(), self::TEASER_TAG) !== false;
	}
	
	/**
	 * Retrieve the post teaser
	 * This is the data from the post_content field upto to the TEASER_TAG
	 *
	 * @return string
	 */
	protected function _getPostTeaser($includeSuffix = true)
	{
		if (strpos($this->getPostContent(), self::TEASER_TAG) !== false) {
			$content = $this->getPostContent();
			
			$excerpt = substr($content, 0, strpos($content, self::TEASER_TAG));
			
			if ($includeSuffix && $this->_getTeaserAnchor()) {
				$excerpt .= sprintf(' <a href="%s" class="read-more">%s</a>', $this->getPermalink(), $this->_getTeaserAnchor());
			}
			
			return $excerpt;
		}
		
		return null;
	}

	/**
	 * Returns the parent category of the current post
	 *
	 * @return Fishpig_Wordpress_Model_Post_Category
	 */
	public function getParentCategory()
	{
		if (!$this->hasData('parent_category')) {
			$this->setData('parent_category', $this->getParentCategories()->getFirstItem());
		}
		
		return $this->getData('parent_category');
	}
	
	/**
	 * Retrieve a collection of all parent categories
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Category_Collection
	 */
	public function getParentCategories()
	{
		if (!$this->hasData('parent_categories')) {
			$this->setData('parent_categories', $this->getResource()->getParentCategories($this));
		}
		
		return $this->getData('parent_categories');
	}

	/**
	 * Gets a collection of post tags
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Tag_Collection
	 */
	public function getTags()
	{
		if (!$this->hasData('tags')) {
			$this->setData('tags', $this->getResource()->getPostTags($this));
		}
		
		return $this->getData('tags');
	}

	/**
	 * Retrieve the read more anchor text
	 *
	 * @return string|false
	 */
	protected function _getTeaserAnchor()
	{
		$teaserAnchor = trim(Mage::helper('wordpress')->htmlEscape(Mage::getStoreConfig('wordpress_blog/posts/more_anchor')));
		
		return $teaserAnchor ? $teaserAnchor : false;
	}
	
	/**
	 * Retrieve the amount of words to use in the auto-generated excerpt
	 *
	 * @return int
	 */
	public function getExcerptSize()
	{
		if (!$this->_getData('excerpt_size')) {
			$this->setExcerptSize((int)Mage::getStoreConfig('wordpress_blog/posts/excerpt_size'));
		}
		
		return $this->_getData('excerpt_size');
	}
	
	public function getPreviousPost()
	{
		if (!$this->hasPreviousPost()) {
			$this->setPreviousPost(false);
			
			$collection = Mage::getResourceModel('wordpress/post_collection')
				->addIsPublishedFilter()
				->addPostDateFilter(array('lt' => $this->_getData('post_date')))
				->setPageSize(1)
				->setCurPage(1)
				->setOrderByPostDate()
				->load();

			if ($collection->count() > 0) {
				$this->setPreviousPost($collection->getFirstItem());
			}
		}
		
		return $this->_getData('previous_post');
	}
	
	public function getNextPost()
	{
		if (!$this->hasNextPost()) {
			$this->setNextPost(false);
			
			$collection = Mage::getResourceModel('wordpress/post_collection')
				->addIsPublishedFilter()
				->addPostDateFilter(array('gt' => $this->_getData('post_date')))
				->setPageSize(1)
				->setCurPage(1)
				->setOrderByPostDate('asc')
				->load();

			if ($collection->count() > 0) {
				$this->setNextPost($collection->getFirstItem());
			}
		}
		
		return $this->_getData('next_post');
	}
}
