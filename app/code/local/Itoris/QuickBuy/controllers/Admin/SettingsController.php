<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_QUICKBUY
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

class Itoris_QuickBuy_Admin_SettingsController extends Itoris_QuickBuy_Controller_Admin_Controller {

	/**
	 * Settings page
	 */
	public function indexAction() {
		$this->_getSession()->setBeforUrl(Mage::helper('core/url')->getCurrentUrl());
		$websiteCode = $this->getRequest()->getParam('website');
		if (!empty($websiteCode)) {
			$website = Mage::app()->getWebsite($websiteCode);
			if (!Mage::helper('itoris_quickbuy')->isRegistered($website)) {
				$error = '<b style="color:red">'
						 . $this->__('The extension is not registered for the website selected. Please register it with an additional S/N.')
						 . '</b>';
				Mage::getSingleton('adminhtml/session')->addError($error);
			}
		}
		$this->loadLayout();
		$settings = $this->getLayout()->createBlock('itoris_quickbuy/admin_config_settings');
		$this->getLayout()->getBlock('content')->append( $settings );
		$this->renderLayout();
	}

	/**
	 * Save settings action
	 */
	public function saveAction() {
		$websiteId = (int)$this->getRequest()->getParam('website_id');
		$storeId = (int)$this->getRequest()->getParam('store_id');
		if ($storeId) {
			$scope = 'store';
			$scopeId = (int)$storeId;
		} elseif ($websiteId) {
			$scope = 'website';
			$scopeId = $websiteId;
		} else {
			$scope = 'default';
			$scopeId = 0;
		}
		$data = $this->getRequest()->getPost();
		if (!isset($data['settings'])) {
			$this->_redirect('*/*');
			return;
		}
		$settings = $data['settings'];
		$model = Mage::getModel('itoris_quickbuy/settings');

		try {
			$model->save($settings, $scope, $scopeId);
			Mage::helper('itoris_quickbuy/cache')->clearAll();
			$this->_getSession()->addSuccess($this->__('Settings have been saved'));
		} catch (Exception $e) {
			$this->_getSession()->addWarning($this->__('Settings have not been saved'));
			Mage::logException($e);
		}
		
		$this->_redirectReferer($this->_getSession()->getBeforUrl());
	}

	protected function _isAllowed() {
		return Mage::getSingleton('admin/session')->isAllowed('admin/system/itoris/itoris_quickbuy');
	}
}
?>
