<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

abstract class Fishpig_Wordpress_Model_Post_Abstract extends Fishpig_Wordpress_Model_Abstract
{
	/**
	 * Entity meta infromation
	 *
	 * @var string
	 */
	protected $_metaTable = 'wordpress/post_meta';	
	protected $_metaTableObjectField = 'post_id';
	
	/**
	 * Inject string 'Protected: ' on password protected posts
	 *
	 * @return string
	 */
	public function getPostTitle()
	{
		if ($this->getPostPassword() !== '') {
			return Mage::helper('wordpress')->__('Protected: %s', $this->_getData('post_title'));
		}
	
		return $this->_getData('post_title');
	}
	
	/**
	 * Load a page by a URI slug (post_name)
	 * This is useful for loading pages based on the URL
	 *
	 * @param string slug
	 * @return Fishpig_Wordpress_Model_Post_Abstract
	 */
	public function loadBySlug($slug)
	{
		return $this->load($slug, 'post_name');
	}
	
	/**
	 * Retrieve the URL for the comments feed
	 *
	 * @return string
	 */
	public function getCommentFeedUrl()
	{
		return rtrim($this->getPermalink(), '/') . '/feed/';
	}
	 
	/**
	 * Gets the post content
	 *
	 * @return string
	 */
	public function getPostContent()
	{
		if (!$this->hasFilteredPostContent()) {
			$this->setFilteredPostContent(Mage::helper('wordpress/filter')->applyFilters($this->_getData('post_content'), array('object' => $this)));
		}
		
		return $this->_getData('filtered_post_content');
	}

	/**
	 * Returns a collection of comments for this post
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Comment_Collection
	 */
	public function getComments()
	{
		if (!$this->hasData('comments')) {
			$this->setData('comments', $this->getResource()->getPostComments($this));
		}
		
		return $this->getData('comments');
	}

	/**
	 * Returns a collection of images for this post
	 * 
	 * @return Fishpig_Wordpress_Model_Mysql4_Image_Collection
	 *
	 * NB. This function has not been thoroughly tested
	 *        Please report any bugs
	 */
	public function getImages()
	{
		if (!$this->hasData('images')) {
			$this->setImages(Mage::getResourceModel('wordpress/image_collection')->setParent($this->getData('ID')));
		}
		
		return $this->getData('images');
	}

	/**
	 * Returns the featured image for the post
	 *
	 * This image must be uploaded and assigned in the WP Admin
	 *
	 * @return Fishpig_Wordpress_Model_Image
	 */
	public function getFeaturedImage()
	{
		if (!$this->hasData('featured_image')) {
			$this->setFeaturedImage($this->getResource()->getFeaturedImage($this));
		}
	
		return $this->getData('featured_image');	
	}
	
	/**
	 * Get the model for the author of this post
	 *
	 * @return Fishpig_Wordpress_Model_Author
	 */
	public function getAuthor()
	{
		return Mage::getModel('wordpress/user')->load($this->getAuthorId());	
	}
	
	/**
	 * Returns the author ID of the current post
	 *
	 * @return int
	 */
	public function getAuthorId()
	{
		return $this->getData('post_author');
	}
	
	/**
	 * Returns the post date formatted
	 * If not format is supplied, the format specified in your Magento config will be used
	 *
	 * @return string
	 */
	public function getPostDate($format = null)
	{
		if ($this->getData('post_date_gmt') && $this->getData('post_date_gmt') != '0000-00-00 00:00:00') {
			return Mage::helper('wordpress')->formatDate($this->getData('post_date_gmt'), $format);
		}
	}
	
	/**
	 * Returns the post date formatted
	 * If not format is supplied, the format specified in your Magento config will be used
	 *
	 * @return string
	 */
	public function getPostModifiedDate($format = null)
	{
		if ($this->getData('post_modified_gmt') && $this->getData('post_modified_gmt') != '0000-00-00 00:00:00') {
			return Mage::helper('wordpress')->formatDate($this->getData('post_modified_gmt'), $format);
		}
	}
	
	/**
	 * Returns the post time formatted
	 * If not format is supplied, the format specified in your Magento config will be used
	 *
	 * @return string
	 */
	public function getPostTime($format = null)
	{
		if ($this->getData('post_date_gmt') && $this->getData('post_date_gmt') != '0000-00-00 00:00:00') {
			return Mage::helper('wordpress')->formatTime($this->getData('post_date_gmt'), $format);
		}
	}
	
	/*
	 * Submit a comment for this post
	 *
	 * @param string $name
	 * @param string $email
	 * @param string $url
	 * @param string $comment
	 * @param array $extra = null - allows for adding custom comment data for plugins
	 * @return Fishpig_Wordpress_Model_Post_Comment
	 */
	public function postComment($name, $email, $url, $comment, $extra = null)
	{
		return $this->getResource()->postComment($this, $name, $email, $url, $comment, $extra);
	}
	
	/**
	 * Retrieve the META description for a Post
	 *
	 * @return string
	 */
	public function getMetaDescription()
	{
		if (!$this->hasMetaDescription()) {
			$this->setMetaDescription(false);

			if (($desc = trim($this->getPostExcerpt(false))) !== '') {
				$desc = preg_replace('/<script(.*)>[^<]{1,}<\/script>/iU', '', $desc);
				$desc = preg_replace("/[\n\r\t]{1,}/", '', $desc);
		
				$this->setMetaDescription(strip_tags($desc));
			}
		}
		
		return $this->_getData('meta_description');
	}

	/**
	 * Determine whether the post has been published
	 *
	 * @return bool
	 */
	public function isPublished()
	{
		return $this->getPostStatus() == 'publish';
	}

	/**
	 * Determine whether the post has been published
	 *
	 * @return bool
	 */
	public function isPending()
	{
		return $this->getPostStatus() == 'pending';
	}

	
	/**
	 * Retrieve the preview URL
	 *
	 * @return string
	 */
	public function getPreviewUrl()
	{
		if ($this->isPending()) {
			return Mage::helper('wordpress')->getUrl('?p=' . $this->getId() . '&preview=1');
		}
		
		return '';
	}
	
	/**
	 * Determine whether the current user can view the post/page
	 * If visibility is protected and user has supplied wrong password, return false
	 *
	 * @return bool
	 */
	public function isViewableForVisitor()
	{
		return $this->getPostPassword() === '' 
			|| Mage::getSingleton('wordpress/session')->getPostPassword() == $this->getPostPassword(); 
	}
}
