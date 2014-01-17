<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_PostController extends Fishpig_Wordpress_Controller_Abstract
{
	/**
	 * Used to do things en-masse
	 * eg. include canonical URL
	 *
	 * @return false|Mage_Core_Model_Abstract
	 */
	public function getEntityObject()
	{
		return $this->_initPost();
	}

	/**
	 * Display the post view page
	 *
	 */
	public function viewAction()
	{
		$post = Mage::registry('wordpress_post');
		
		$this->_rootTemplates[] = 'template_post_view';

		$this->_checkForPostedComment();
		
		$this->_addCustomLayoutHandles(array(
			'wordpress_post_view_index',
			'wordpress_post_view_' . $post->getId(),
		));
		
		$this->_initLayout();

		$this->_title(strip_tags($post->getPostTitle()));
		
		if (($headBlock = $this->getLayout()->getBlock('head')) !== false) {
			$feedTitle = sprintf('%s &raquo; %s Comments Feed', Mage::helper('wordpress')->getWpOption('blogname'), $post->getPostTitle());
			$headBlock->addItem('link_rel', $post->getCommentFeedUrl(), 'rel="alternate" type="application/rss+xml" title="' . $feedTitle . '"');
			$headBlock->setDescription($post->getMetaDescription());
		}
			
		$this->_addOpenGraphTags();
			
		$this->addCrumb('post', array('label' => $post->getPostTitle()));

		$this->renderLayout();
	}
	
	/**
	 * Display the comment feed
	 *
	 */
	public function feedAction()
	{
		$this->getResponse()
			->setHeader('Content-Type', 'text/xml; charset=' . Mage::helper('wordpress')->getWpOption('blog_charset'), true)
			->setBody($this->getLayout()->createBlock('wordpress/feed_post_comment')->toHtml());
	}

	/**
	 * Initialise the post model
	 * Provides redirects for Guid links when using permalinks
	 *
	 * @return false|Fishpig_Wordpress_Model_Post
	 */
	protected function _initPost()
	{
		if (($post = Mage::registry('wordpress_post')) !== null) {
			return $post;
		}
		
		$postHelper = Mage::helper('wordpress/post');
		$isPreview = $this->getRequest()->getParam('preview', false);
		
		if (!$postHelper->useGuidLinks()) {
			$uri = Mage::helper('wordpress/router')->getBlogUri();

			if (($post = Mage::registry('wordpress_post_temp')) !== null || $post = $postHelper->loadByPermalink($uri)) {
				Mage::app()->getRequest()->setParam('p', $post->getId());
				
				if ($this->getRequest()->getParam($this->getRouterHelper()->getTrackbackVar())) {
					$this->_redirectUrl($post->getUrl());
					$this->getResponse()->sendHeaders();
					exit;
				}

				if ($post->isPublished()) {
					Mage::register('wordpress_post', $post);
					return $post;
				}
				
				return false;
			}

			if ($postId = $postHelper->getPostId()) {
				$post = Mage::getModel('wordpress/post')->load($postId);

				if ($post->getId()) {
					if ($isPreview) {
						Mage::register('wordpress_post', $post);
						return $post;
					}

					if ($post->isPublished()) {
						$this->_redirectUrl($post->getUrl());
						$this->getResponse()->sendHeaders();
						exit;
					}
				}
			}
		}
		else if ($postId = $postHelper->getPostId()) {
			$post = Mage::getModel('wordpress/post')->load($postId);
			
			if ($post->getId()) {
				if ($post->isPublished() || $isPreview) {
					Mage::register('wordpress_post', $post);
					return $post;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * If enabled in the configuration, add OpenGraph tags to the post
	 *
	 */
	protected function _addOpenGraphTags()
	{
		$helper = Mage::helper('wordpress');

		if ($helper->getConfigFlag('wordpress_blog/posts/opengraph')) {
			if ($post = Mage::registry('wordpress_post')) {
				if (($headBlock = $this->getLayout()->getBlock('head')) !== false) {
					$tags = array(
						'site_name' => $helper->getWpOption('blogname'),
						'type' => 'blog',
						'url' => $post->getPermalink(),
						'type' => 'article',
					);
	
					if ($post->getFeaturedImage()) {
						$image = $post->getFeaturedImage();
						
						$tags['image'] = $image->getLargeImage() ? $image->getLargeImage() : $image->getAvailableImage();
					}
					
					$tags['title'] = $post->getPostTitle();
					$tags['description'] = $headBlock->getDescription();
					
					$og = $this->getLayout()->createBlock('core/template')
						->setTemplate('wordpress/post/view/open-graph.phtml')
						->setOpenGraphTags($tags);
					
					$headBlock->setChild('wordpress.post.openGraph', $og);
				}
			}
		}
	}

	/**
	 * Check whether a comment has been posted
	 *
	 */
	protected function _checkForPostedComment()
	{
		if ($response = $this->getRequest()->getParam('cy')) {
			Mage::getSingleton('core/session')->addSuccess($this->__(Mage::getStoreConfig('wordpress_blog/post_comments/success_msg')));
		}
		else if ($response = $this->getRequest()->getParam('cx')) {
			Mage::getSingleton('core/session')->addError($this->__(Mage::getStoreConfig('wordpress_blog/post_comments/error_msg')));
		}

		return $this;
	}
}
