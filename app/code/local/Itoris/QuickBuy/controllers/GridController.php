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

 class Itoris_QuickBuy_GridController extends Mage_Core_Controller_Front_Action {

	 /**
	  * Get json products config
	  */
	public function productsAction() {
		/** @var $search Itoris_QuickBuy_Helper_Search */
		$search = Mage::helper('itoris_quickbuy/search');
		$text = $this->getRequest()->getParam('t');
		$data = array();
		if ($text) {
			$limit = $this->getRequest()->getParam('limit', Itoris_QuickBuy_Helper_Search::DEFAULT_LIMIT);
			$limitFrom = $this->getRequest()->getParam('limitFrom', Itoris_QuickBuy_Helper_Search::DEFAULT_LIMIT_FROM);
			$order = $this->getRequest()->getParam('order', Itoris_QuickBuy_Helper_Search::DEFAULT_ORDER);
			$orderBy = $this->getRequest()->getParam('orderBy', Itoris_QuickBuy_Helper_Search::DEFAULT_ORDER_BY);
			$selectedProducts = $this->getRequest()->getParam('selectedProducts');
			$selectedProducts = explode(',', $selectedProducts);
			foreach ($selectedProducts as $index => $id) {
				$selectedProducts[$index] = (int)$id;
			}
			try {
				if ($this->getDataHelper()->getSettings()->getUseCatalogSearchTerms()) {
					$query = $this->getDataHelper()->getQuery($text);
					if ($query->getRedirect()) {
						$this->getResponse()->setBody(Zend_Json::encode(array('redirect' => $query->getRedirect())));
						return;
					} else if ($query->getSynonymFor()) {
						$text = $query->getSynonymFor();
					}
				}
				$search->searchProducts($text, $selectedProducts, $limit, $limitFrom, $order, $orderBy);
			} catch (Exception $e) {
				Mage::logException($e);
			}
			/** @var $taxHelper Mage_Tax_Helper_Data */
			$taxHelper = Mage::helper('tax');
			$data = array(
						'products'  => $search->getProducts(),
						'limitFrom' => $search->getLimitFrom(),
						'limit'     => $limit,
						'order'     => $order,
						'orderBy'   => $orderBy,
			);
			$productMapOfSerchingMatches = array();
			$productModels = array();
			/** @var $inventoryHelper Mage_CatalogInventory_Helper_Data */
			$inventoryHelper = Mage::helper('cataloginventory');
			foreach ($data['products'] as $i => $product) {
				$data['products'][$i]['min_qty'] = (float)$product['min_sale_qty'];
				$data['products'][$i]['use_config_enable_qty_inc'] = (bool)$product['use_config_enable_qty_inc'];
				$data['products'][$i]['use_config_qty_increments'] = (bool)$product['use_config_qty_increments'];
				$data['products'][$i]['enable_qty_increments'] = (bool)$product['enable_qty_increments'];
				$data['products'][$i]['qty_increments'] = (float)$product['qty_increments'];

				/** @var $productModel Mage_Catalog_Model_Product */
				$productModel = Mage::getModel('catalog/product')->load($product['product_id']);
				$productModel->setOutOfStock(!$productModel->isSalable());
				if (!$inventoryHelper->isShowOutOfStock() && $productModel->getOutOfStock()) {
					continue;
				}
				$productMapOfSerchingMatches[$i] = $search->getSearchingMatches($productModel, $product['category']);
				if ($productMapOfSerchingMatches[$i]['not_match']) {
					continue;
				}
				$productModels[$productModel->getId()] = $productModel;
			}
			//$data['products'] = $search->sortProductBySearchRelevance($productMapOfSerchingMatches, $data['products']);
			$data['products'] = $search->deleteNotMatchProducts($productMapOfSerchingMatches, $data['products']);
			$data['totalRows'] = $search->getTotalRows();
			foreach ($data['products'] as $i => $product) {
				$productModel = $productModels[$product['product_id']];
				$productPrice = $taxHelper->getPrice($productModel, $productModel->getPrice());
				$taxPercent = $productModel->getTaxPercent();
				if (!$taxPercent) {
					$taxPercent = 0;
					if (method_exists($productModel->getPriceModel(), 'getPricesDependingOnTax')) {
						$minPrice = $productModel->getPriceModel()->getPricesDependingOnTax($productModel, 'min');
						$minPriceInclTax = $productModel->getPriceModel()->getPricesDependingOnTax($productModel, 'min', true);
						$taxPercent = $minPrice ? round($minPriceInclTax * 100 / $minPrice - 100, 3) : 0;
					}
				}
				$data['products'][$i]['price'] = (string)$productModel->getPriceModel()->getFinalPrice(1, $productModel);
				$data['products'][$i]['tax_percent'] = (string)$taxPercent;
				$data['products'][$i]['tax_class_id'] = (int)$productModel->getTaxClassId();
				/** @var $image Mage_Catalog_Helper_Image */
				$image = Mage::helper('catalog/image');
				$image->init($productModel, 'image')->resize(85,85);
				$data['products'][$i]['image_url'] = $image->__toString();
				$data['products'][$i]['product_url'] = $productModel->getProductUrl();
				$data['products'][$i]['out_of_stock'] = $productModel->getOutOfStock();
				$data['products'][$i]['category_path'] = $search->getCategoryPath(Mage::getModel('catalog/category')->load($data['products'][$i]['category_id']));
				$data['products'][$i]['tier_prices'] = $this->prepareTierPrices($productModel);
				if ($productModel->getOutOfStock()) {
					continue;
				}
				$data['products'][$i] = $this->addOptionToProduct($data['products'][$i], $productModel);
			}
		}
		$this->getResponse()->setBody(Zend_Json::encode($data));
	}

	 /**
	  * @param $productData
	  * @param $productModel Mage_Catalog_Model_Product
	  */
	 protected function addOptionToProduct($productData, $productModel) {
		return Mage::helper('itoris_quickbuy/product')->addProductOptionsToArray($productData, $productModel);
	}

	public function loadProductConfigAction() {
		$productId = $this->getRequest()->getParam('id');
		$result = array();
		try {
			$product = Mage::getModel('catalog/product')->load($productId);
			if ($product->getId()) {
				$result['product'] = array();
				$result['product'] = $this->addOptionToProduct($result['product'], $product);
				$result['product']['tier_prices'] = $this->prepareTierPrices($product);
			} else {
				$result['error'] = 'no such product';
			}
		} catch (Exception $e) {
			$result['error'] = $e->getMessage();
		}

		$this->getResponse()->setBody(Zend_Json::encode($result));
	}

	 public function loadDefaultProductsAction() {
		 $result = array();
		 $limit = $this->getRequest()->getParam('limit', Itoris_QuickBuy_Helper_Search::DEFAULT_LIMIT);
		 $limitFrom = $this->getRequest()->getParam('limitFrom', Itoris_QuickBuy_Helper_Search::DEFAULT_LIMIT_FROM);
		 $order = $this->getRequest()->getParam('order', Itoris_QuickBuy_Helper_Search::DEFAULT_ORDER);
		 $orderBy = $this->getRequest()->getParam('orderBy', Itoris_QuickBuy_Helper_Search::DEFAULT_ORDER_BY);
		 try {
			 $page = ceil(($limitFrom + 1) / $limit);
			 $products = Mage::helper('itoris_quickbuy/cacheSearch')->getDefaultProducts($page, $limit, $orderBy, $order);
			 $result['products'] = $products['products'];
			 $result['totalRows'] = $products['total'];
			 $result['limitFrom'] = $limitFrom;
			 $result['limit'] = $limit;
			 $result['order'] = $order;
			 $result['orderBy'] = $orderBy;
		 } catch (Exception $e) {
			 $result['error'] = $e->getMessage();
		 }

		 $this->getResponse()->setBody(Zend_Json::encode($result));
	 }

	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @return array
	 */
	 private function prepareTierPrices(Mage_Catalog_Model_Product $product) {
		 return Mage::helper('itoris_quickbuy/product')->getTierPrices($product);
	}

	 /**
	  * @return Itoris_QuickBuy_Helper_Data
	  */
	 protected function getDataHelper() {
		 return Mage::helper('itoris_quickbuy');
	 }
}
?>