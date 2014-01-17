<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_IndexController extends Fishpig_Wordpress_Controller_Abstract
{
	/**
	 * Used to do things en-masse
	 * eg. include canonical URL
	 *
	 * @return Varien_Object|Fishpig_Wordpress_Model_Page
	 */
	public function getEntityObject()
	{
		if (Mage::registry('wordpress_page')) {
			return Mage::registry('wordpress_page');
		}

		return new Varien_Object(array(
			'url' => Mage::helper('wordpress')->getUrl(),
		));
	}
	
	/**
	 * Ensure that this controller can run
	 *
	 * @return $this
	 */
	public function preDispatch()
	{
		parent::preDispatch();
		
		if (!Mage::getStoreConfigFlag('wordpress_blog/layout/ignore_custom_homepage')) {
			if ($this->getRequest()->getActionName() === 'index') {
				if (!Mage::registry('wordpress_page')) {
					if (Mage::helper('wordpress')->getWpOption('show_on_front')) {
						if ($pageId = Mage::helper('wordpress')->getWpOption('page_on_front')) {
							$page = Mage::getModel('wordpress/page')->load($pageId);
							
							if ($page->getId()) {
								Mage::register('wordpress_page', $page);
								$this->_forceForwardViaException('view', 'page');
								return false;
							}
						}
					}
				}
			}
		}
				
		return $this;	
	}

	/**
	 * Display the blog homepage
	 *
	 */
	public function indexAction()
	{
		$this->_addCustomLayoutHandles(array(
			'wordpress_homepage',
			'wordpress_homepage_index',
		));
		
		$this->_initLayout();

		$this->_rootTemplates[] = 'template_homepage';
		
		$this->renderLayout();
	}
	
	/**
	 * Display the main blog RSS feed
	 *
	 */
	public function feedAction()
	{
		$this->getResponse()
			->setHeader('Content-Type', 'text/xml; charset=' . Mage::helper('wordpress')->getWpOption('blog_charset'), true)
			->setBody($this->getLayout()->createBlock('wordpress/feed_home')->toHtml());
	}
	
	/**
	 * Display the blog robots.txt file
	 *
	 */
	public function robotsAction()
	{
		if (($path = Mage::helper('wordpress')->getWordPressPath()) !== false) {
			$robotsFile = $path . 'robots.txt';

			if (is_file($robotsFile) && is_readable($robotsFile)) {
				if ($robotsTxt = file_get_contents($robotsFile)) {
					$this->getResponse()->setHeader('Content-Type', 'text/plain;charset=utf8');
					$this->getResponse()->setBody($robotsTxt);
				}
			}
		}
		
		if (!$this->getResponse()->getBody()) {
			$this->_forward('noRoute');
		}
	}
	
	/**
	 * Redirect the user to the WordPress Admin
	 *
	 */
	public function wpAdminAction()
	{
		return $this->_redirectTo(Mage::helper('wordpress')->getAdminUrl());
	}
	
	/**
	 * Forward requests to the WordPress installation
	 *
	 */
	public function forwardAction()
	{
		$queryString = $_SERVER['QUERY_STRING'];
		
		$forwardTo = rtrim(Mage::helper('wordpress')->getWpOption('siteurl'), '/') . '/index.php?' . $queryString;

		$this->_redirectUrl($forwardTo);
	}
	
	/**
	 * Forward requests for images
	 *
	 */
	public function forwardFileAction()
	{
		$url = rtrim(Mage::helper('wordpress')->getWpOption('siteurl'), '/');
		
		$forwardTo = $url . '/' . ltrim(Mage::helper('wordpress/router')->getBlogUri(), '/');

		$this->_redirectUrl($forwardTo);
	}	
	
	/**
	 * Set the post password and redirect to the referring page
	 *
	 */
	public function applyPostPasswordAction()
	{
		$password = $this->getRequest()->getPost('post_password');
		
		Mage::getSingleton('wordpress/session')->setPostPassword($password);
		
		$this->_redirectReferer();
	}
		
	/**
	 * Forces a redirect to the given URL
	 *
	 * @param string $url
	 * @return bool
	 */
	protected function _redirectTo($url)
	{
		return $this->getResponse()->setRedirect($url)->sendResponse();
	}

	/**
	 * Redirect the old sitemap to the Magento sitemap
	 *
	 */
	public function sitemapAction()
	{
		$this->_redirectUrl(Mage::helper('wordpress')->getBaseUrl('index.php/' . basename($this->getRequest()->getRequestUri())));
	}
}
