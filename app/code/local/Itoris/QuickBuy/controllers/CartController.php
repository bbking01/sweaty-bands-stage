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

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . '/CartController.php';

class Itoris_QuickBuy_CartController extends Mage_Checkout_CartController {

	public function addAction() {
		$this->_getSession()->getMessages(true);
		$fileErrors = array();
		$productModel = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product'));
		if (count($_FILES)) {
			if ($productModel->getId()) {
				$options = $productModel->getOptions();
				/** @var $option Mage_Catalog_Model_Product_Option */
				foreach ($options as $option) {
					if ($option->getFileExtension()) {
						$extensions = str_word_count($option->getFileExtension(), 1);
						$pattern = '/.[' . implode('|', $extensions) . ']$/';
						if (isset($_FILES['options_' . $option->getId() . '_file'])) {
							if (!preg_match($pattern, $_FILES['options_' . $option->getId() . '_file']['name'])) {
								$fileErrors[] = $this->__('Some file has not allowed extension');
								break;
							}
						}
					}
				}
			}
		}
		if ($productModel->getStockItem()) {
			$minimumQty = $productModel->getStockItem()->getMinSaleQty();
			//If product was not found in cart and there is set minimal qty for it
			if ($minimumQty && $minimumQty > 0 && $this->getRequest()->getParam('qty') < $minimumQty
				&& !$this->_getQuote()->hasProductId($productModel->getId())
			){
				$item = $this->_getQuote()->getItemByProduct($productModel);
				if (!$item || ($item->getTotalQty() + $this->getRequest()->getParam('qty') < $minimumQty)) {
					$fileErrors[] = PHP_EOL . $this->__('%s: The minimum quantity allowed for purchase is %d', $productModel->getName(), $minimumQty);
				}
			}
		}
		if ($this->getRequest()->getParam('super_group')) {
			$groupProducts = $this->getRequest()->getParam('super_group');
			if (is_array($groupProducts)) {
				foreach ($groupProducts as $key => $groupProductQty) {
					$groupProductModel = Mage::getModel('catalog/product')->load($key);
					if ($groupProductModel->getId() && $groupProductModel->getStockItem()) {
						$minimumQty = $groupProductModel->getStockItem()->getMinSaleQty();
						$maximumQty = $groupProductModel->getStockItem()->getMaxSaleQty();
						$groupProductQty = (float)$groupProductQty;
						$item = $this->_getQuote()->getItemByProduct($groupProductModel);
						if ($minimumQty && $minimumQty > 0 && $groupProductQty < $minimumQty
							&& !$this->_getQuote()->hasProductId($groupProductModel->getId())
						){
							if (!$item || ($item->getTotalQty() + $groupProductQty < $minimumQty)) {
								$fileErrors[] = PHP_EOL . $this->__('%s (%s): The minimum quantity allowed for purchase is %d', $productModel->getName(), $groupProductModel->getName(), $minimumQty);
							}
						}
						if ($maximumQty
							&& ($maximumQty < $groupProductQty || ($item && $item->getTotalQty() + $groupProductQty > $maximumQty))
						) {
							$fileErrors[] = PHP_EOL . $this->__('%s (%s): The maximum quantity allowed for purchase is %d', $productModel->getName(), $groupProductModel->getName(), $maximumQty);
						}
						if ($groupProductModel->getStockItem()->getEnableQtyIncrements()) {
							$qtyIncrements = (float)$groupProductModel->getStockItem()->getQtyIncrements();
							$totalQty = $groupProductQty;
							if ($item) {
								$totalQty += $item->getTotalQty();
							}
							if ($totalQty % $qtyIncrements) {
								$fileErrors[] = PHP_EOL . $this->__('%s (%s) is available for purchase in increments of %d only.', $productModel->getName(), $groupProductModel->getName(), $maximumQty);
							}
						}
 					}
				}
			}
		}
		if (empty($fileErrors)) {
			parent::addAction();
		}
		$this->getResponse()->clearHeaders();
		$this->getResponse()->setHttpResponseCode(200);
		$successMessages = $this->_getSession()->getMessages()->getItems('success');
		$errorMessages = $this->_getSession()->getMessages()->getItems('error');
		foreach ($errorMessages as $errorMessage) {
			if (strlen($errorMessage->getText())) {
				$fileErrors[] = PHP_EOL . $productModel->getName() . ': ' . $errorMessage->getText();
			}
		}
		if (!empty($fileErrors) || empty($successMessages)) {
			$html = '<div id="error">' . implode('', $fileErrors) . '</div>';
		} else {
			$html = '<div id="success"></div>';
		}
		$this->_getSession()->getMessages(true);
		$this->getResponse()->setBody($html);
	}
}
?>