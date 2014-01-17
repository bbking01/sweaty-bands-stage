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

class Itoris_QuickBuy_IndexController extends Mage_Core_Controller_Front_Action {

	/**
	 * QuickBuy page
	 */
	public function indexAction() {
		$settingsModel = new Itoris_QuickBuy_Model_Settings();
		$websiteId = Mage::app()->getWebsite()->getId();
		$storeId = Mage::app()->getStore()->getId();
		$settingsModel->load($websiteId, $storeId);
		if ($settingsModel->getEnable() == Itoris_QuickBuy_Model_Settings::DISABLED) {
			$this->_redirect('noRoute');
		} else {
			$this->loadLayout();
			$this->renderLayout();
		}
	}

	/**
	 * Accelerates the process of adding products to the cart.
	 *
	 * By default, after adding product to the cart there is a redirection to the cart page.
	 * It's possible to disable but only for one product because the redirection will be enabled
	 * after a product adding.
	 *
	 * @deprecated
	 */
	public function emptyAction() {
		$this->getResponse()->setBody('<div id="success"></div>');
	}

	public function uploadFileAction() {
		$file = $this->getRequest()->getPost('option');
		readfile($_FILES[$file]['tmp_name']); exit;
		$this->getResponse()->setBody('<input id="file" type="text" value="' . file_get_contents($_FILES[$file]['tmp_name']) . '" />');
	}

	public function saveInSessionAction() {
		$storeId = Mage::app()->getStore()->getId();
		Mage::getSingleton('core/session')->addData(array(
			'products' . $storeId => Mage::app()->getRequest()->getPost('products'),
			'selected_products' . $storeId => Mage::app()->getRequest()->getPost('selected_products'),
		));
		exit;
	}
}
 
?>