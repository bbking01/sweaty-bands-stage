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
class Itoris_QuickBuy_Admin_RegisterController extends Mage_Adminhtml_Controller_Action {

	public function indexAction() {
		if (!Mage::helper('itoris_quickbuy')->registerCurrentStoreHost($this->getRequest()->getParam('sn'))) {
			$this->_getSession()->addSuccess($this->__('The extension has been registered'));
		} else {
			$this->_getSession()->addError($this->__('Wrong s/n!'));
		}
	}
}
?>