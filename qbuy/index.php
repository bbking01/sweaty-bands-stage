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

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
if (method_exists('Mage', 'init')) {
	Mage::init($mageRunCode, $mageRunType, array(
			'config_model' => 'Itoris_QuickBuy_Model_Core_Config'
		),array(
			'Mage_Core',
			'Mage_Eav',
			'Mage_Dataflow',
			'Mage_Directory',
			'Mage_Customer',
			'Mage_Cms',
			'Mage_Index',
			'Mage_Catalog',
			'Mage_CatalogInventory',
			'Mage_Tag',
			'Mage_Tax',
			'Mage_Rule',
			'Mage_Payment',
			'Mage_Sales',
			'Mage_Checkout',
			'Mage_CatalogSearch',
			'Itoris_QuickBuy',
	));
	Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
} else {
	Mage::app($mageRunCode, $mageRunType, array(
		'config_model' => 'Itoris_QuickBuy_Model_Core_Config'
	));
	if (Mage::getConfig()->getNode('global/helpers/catalog/rewrite')->image) {
		unset(Mage::getConfig()->getNode('global/helpers/catalog/rewrite')->image);
	}
}
if (isset($_GET['store_id'])) {
	$store = Mage::getModel('core/store')->load($_GET['store_id']);
	if ($store->getId()) {
		Mage::app()->setCurrentStore($store);
	}
}
if (isset($_GET['base_path'])) {
	Mage::app()->getRequest()->setBasePath($_GET['base_path']);
}
if (isset($_GET['sid'])) {
	$_COOKIE['frontend'] = $_GET['sid'];
}

Mage::getSingleton('core/session', array('name' => 'frontend'));

/** @var $searchHelper Itoris_Quickbuy_Helper_CacheSearch */
$searchHelper = Mage::helper('itoris_quickbuy/cacheSearch');
$result = array();
try {
	$type = Mage::app()->getRequest()->getParam('type');
	if ($type == 'cache') {
		$searchHelper->loadProducts(true);
		exit;
	} elseif ($type == 'save_in_session') {
		Mage::getSingleton('core/session')->addData(array(
			'products' => Mage::app()->getRequest()->getPost('products'),
			'selected_products' => Mage::app()->getRequest()->getPost('selected_products'),
		));
		exit;
	} elseif ($type == 'cart_summary') {
		/** @var $cartHelper Mage_Checkout_Helper_Cart */
		$cartHelper = Mage::helper('checkout/cart');
		$cartHelper->getCart()->save();
		$count = $cartHelper->getCart()->getSummaryQty();
		if ($count == 1) {
			$text = $cartHelper->__('My Cart (%s item)', $count);
		} elseif ($count > 0) {
			$text = $cartHelper->__('My Cart (%s items)', $count);
		} else {
			$text = $cartHelper->__('My Cart');
		}
		$result = array(
			'link_text' => htmlentities($text),
			'count'     => (int)$count,
		);
	} else {
		$keyword = isset($_GET['t']) ? trim($_GET['t']) : null;
		if (strlen($keyword) >= 2) {
			$result = $searchHelper->searchProducts($keyword);
		} else {
			$result['error'] = 'The keyword is too short';
		}
	}
} catch (Exception $e) {
	$result['error'] = $e->getMessage();
}
echo isset($_GET['callback']) ? $_GET['callback'] . '(' . Zend_Json::encode($result) . ')' : '';
exit;
?>