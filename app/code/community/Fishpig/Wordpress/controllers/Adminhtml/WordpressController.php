<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Adminhtml_WordpressController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Display the form for auto-login details
	 *
	 */
	public function autologinAction()
	{
		$user = Mage::getModel('wordpress/admin_user')->load(0, 'store_id');
			
		if ($user->getId()) {
			Mage::register('wordpress_admin_user', $user);
		}
		
		$this->loadLayout();
		$this->_setPageTitle('WP Login Details');
		$this->_setActiveMenu('wordpress');
		$this->renderLayout();
	}
	
	/**
	 * Save the auto-login details
	 *
	 */
	public function autologinpostAction()
	{
		if ($data = $this->getRequest()->getPost()) {
			try {
				$data['user_id'] = Mage::getSingleton('admin/session')->getUser()->getUserId();
				$autologin	= Mage::getModel('wordpress/admin_user');
				$autologin->setData($data)->setId($this->getRequest()->getParam('id'));

				$autologin->save();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Your Wordpress Auto-login details were successfully saved.'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);				
			}
			catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
			}
		}
		else {
			Mage::getSingleton('adminhtml/session')->addError($this->__('There was an error while trying to save your Wordpress Auto-login details.'));
		}
		
        $this->_redirect('*/*/autologin');
	}
	
	/**
	 * Redirect the user to the addons page
	 *
	 */
	public function addonsAction()
	{
		$this->_redirectUrl('http://fishpig.co.uk/magento-extensions.html?ref=adn');
	}

	/**
	 * Attempt to login to the WordPress Admin action
	 *
	 */
	public function loginAction()
	{
		try {
			$user = Mage::getModel('wordpress/admin_user')->load(0, 'store_id');
				
			if (!$user->getId()) {
				throw new Exception('WordPress Auto-Login details not set. Login failed.');
			}
			
			$destination = $this->_getAutologinDestinationPage();

			Mage::helper('wordpress/system')->loginToWordPress($user->getUsername(), $user->getPassword(), $destination);

			$this->_redirectUrl($destination);
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);

			$this->addError('Set your Wordpress Admin login details below. Once you have done this you will be able to login to Wordpress with 1 click by selecting Wordpress Admin from the top menu.')
				->addNotice($this->__('Having problems logging in to the WordPress Admin? The following article contains tips and advice on how to solve auto-login issues: %s', 'http://fishpig.co.uk/wordpress-integration/docs/wp-admin-auto-login.html'));
			
			$this->_redirect('adminhtml/wordpress/autologin');
		}
	}
	
	/**
	 * Retrieve the destination page
	 *
	 * @return string
	 */
	protected function _getAutologinDestinationPage()
	{
		$routes = (array)Mage::app()->getConfig()->getNode()->wordpress->autologin->urls;

		$key = $this->getRequest()->getParam('wp_page', 'default');
		
		if (!isset($routes[$key])) {
			$key = 'default';
		}
		
		$url = Mage::helper('wordpress')->getAdminUrl($routes[$key]);

		if (substr($url, 0, strlen('/wp-admin/')) !== '/wp-admin/') {
			return $url;
		}
		
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('wordpress')->__('Unable to connect to your WordPress database.'));
	}

	
	/**
	 * Set the page title
	 *
	 * @param string $title
	 * @param bool $includePostFIx
	 * @return $this
	 */
	protected function _setPageTitle($title, $includePostFIx = true)
	{
		if ($includePostFIx) {
			$title .= ' | WordPress Integration by FishPig';
		}
		
		if ($headBlock = $this->getLayout()->getBlock('head')) {
			$headBlock->setTitle($title);
		}
		
		return $this;
	}

	/**
	 * Gets the Adminhtml session
	 * @return Mage_Adminhtml_Model_Session
	 */
	public function getSession()
	{
		return Mage::getSingleton('adminhtml/session');
	}
	
	/**
	 * Retrieve the URI path to WordPress config
	 *
	 * @return string
	 */
	public function getWordpressConfigPath()
	{
		return 'adminhtml/system_config/edit/section/wordpress';
	}

	/**
	 * Add an error to the session
	 *
	 * @param string $msg
	 */	
	public function addError($msg)
	{
		return Mage::getSingleton('adminhtml/session')->addError($msg);
	}
	
	/**
	 * Add a notice to the session
	 *
	 * @param string $msg
	 */
	public function addNotice($msg)
	{
		return Mage::getSingleton('adminhtml/session')->addNotice($msg);
	}
}
