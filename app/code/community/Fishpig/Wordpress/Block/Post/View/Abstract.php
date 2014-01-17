<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

abstract class Fishpig_Wordpress_Block_Post_View_Abstract extends Mage_Core_Block_Template
{
	/**
	 * Returns the currently loaded post model
	 *
	 * @return Fishpig_Wordpress_Model_Post_Abstract
	 */
	public function getPost()
	{
		if (!$this->hasData('post')) {
			if ($postId = $this->_getData('post_id')) {
				$post = Mage::getModel('wordpress/post')->load($postId);
				
				if ($post->getId() == $postId) {
					$this->setPost($post);
				}
			}
			else {
				$this->setPost(Mage::registry('wordpress_post'));
			}
		}
		
		return $this->_getData('post');
	}

	/**
	 * Returns the ID of the currently loaded post
	 *
	 * @return int
	 */
	public function getPostId()
	{
		if ($post = $this->getPost()) {
			return $post->getId();
		}
	}
	
	/**
	 * Returns true if comments are enabled for this post
	 */
	protected function canComment()
	{
		if ($post = $this->getPost()) {
			return $post->getCommentStatus() == 'open';
		}
		
		return false;
	}
	
	public function canDisplayPreviousNextLinks()
	{
		if (!$this->hasDisplayPreviousNextLinks()) {
			$this->setDisplayPreviousNextLinks(Mage::getStoreConfigFlag('wordpress_blog/posts/display_previous_next'));
		}
		
		return $this->_getData('display_previous_next_links');
	}
}
