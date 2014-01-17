<?php
/**
 * QuickBuy
 */

class Itoris_QuickBuy_Helper_Product extends Mage_Core_Helper_Abstract {

	public function addProductOptionsToArray($productData, $productModel) {
		$options = $productModel->getOptions();
		/** @var $option Mage_Catalog_Model_Product_Option */
		foreach ($options as $option) {
			$values = $option->getValues();
			$preparedValues = array();
			foreach ($values as $value) {
				switch ($value->getPriceType()) {
					case 'percent': $valuePrice = ($productModel->getPrice()/100) * $value->getPrice();
						break;
					default: $valuePrice = $value->getPrice();
					break;
				}
				$preparedValues[] = array(
					'id' => $value->getId(),
					'title' => $value->getTitle(),
					'price' => $valuePrice,
					'sort' => $value->getSortOrder(),
				);
			}

			switch ($option->getPriceType()) {
				case 'percent': $optionPrice = ($productModel->getPrice()/100) * $option->getPrice();
					break;
				default: $optionPrice = $option->getPrice();
				break;
			}

			$productData['options'][] = array(
				'id' => $option->getId(),
				'title' => $option->getTitle(),
				'type' => $option->getType(),
				'required' => $option->getIsRequire(),
				'price' => $optionPrice,
				'values' => $preparedValues,
				'file_extension' => $option->getFileExtension(),
				'image_size_x' => (int)$option->getImageSizeX(),
				'image_size_y' => (int)$option->getImageSizeY(),
			);
		}
		$type = $productModel->getTypeInstance();//->getConfigurableAttributesAsArray());
		if ($type instanceof Mage_Catalog_Model_Product_Type_Grouped) {
			$productData['type'] = 'grouped';
			$associatedProducts = $type->getAssociatedProducts();

			/** @var $item Mage_Catalog_Model_Product */
			foreach ($associatedProducts as $item) {
				$tierPricesData = $item->getPriceModel()->getTierPrice(null, $item);
				$tierPrices = array();
				foreach ($tierPricesData as $tierPrice) {
					$tierPrices[] = array(
						'price' => $tierPrice['price'],
						'qty'   => (int)$tierPrice['price_qty'],
					);
				}
				$productData['super_group'][] = array(
					'id' => $item->getId(),
					'name' => $item->getName(),
					'sku'  => $item->getSku(),
					'price' => $item->getPriceModel()->getFinalPrice(null, $item),
					'tier_prices' => $tierPrices,
					'tax_class_id' => $item->getTaxClassId(),
					'use_config_qty_increments' => $item->getStockItem() ? (bool)$item->getStockItem()->getUseConfigQtyIncrements() : false,
					'use_config_enable_qty_inc' => $item->getStockItem() ? (bool)$item->getStockItem()->getUseConfigEnableQtyInc() : false,
					'enable_qty_increments'     => $item->getStockItem() ? (bool)$item->getStockItem()->getEnableQtyIncrements() : false,
					'qty_increments'            => $item->getStockItem() ? (float)$item->getStockItem()->getQtyIncrements() : 0,
					'min_qty'                   => $item->getStockItem() ? (float)$item->getStockItem()->getMinSaleQty() : 0,
				);
			}
		} else if ($type instanceof Mage_Catalog_Model_Product_Type_Configurable) {
			$productData['type'] = 'configurable';
			$attributes = $type->getConfigurableAttributes()->getItems();
			$config = Mage::app()->getLayout()->createBlock('catalog/product_view_type_configurable');
			$config->setProduct($productModel);
			$configArray = Zend_Json::decode($config->getJsonConfig());
			/** @var $attribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
			foreach ($attributes as $attribute) {
				$productData['super_attribute'][] = array(
					'id' => $attribute->getAttributeId(),
					'label' => $attribute->getLabel(),
					'required' => $attribute->getProductAttribute()->getIsRequired(),
					'options' => $configArray['attributes'][$attribute->getAttributeId()]['options'],
				);
			}
		} else if ($type instanceof Mage_Bundle_Model_Product_Type) {
			$productData['type'] = 'bundle';
			$bundleOptions = $type->getOptions();
			$bundleProducts = $type->getSelectionsCollection($type->getOptionsIds());
			/** @var $bundleOption Mage_Bundle_Model_Option */
			foreach ($bundleOptions as $bundleOption) {
				$products = array();
				if (!$bundleOption->getRequired() && !$bundleOption->isMultiSelection()) {
					$products[] = array(
						'id' => 0,
						'name' => $this->__('none'),
						'price' => 0,
					);
				}
				foreach ($bundleProducts as $bundleProduct) {
					if ($bundleProduct->isSalable() && $bundleProduct->getOptionId() == $bundleOption->getId() && $bundleProduct) {
						$canChangeQty = $bundleProduct->getData('selection_can_change_qty');
						$products[] = array(
							'id' => $bundleProduct->getSelectionId(),
							'name' => $bundleProduct->getName(),
							'price' => $productModel->getPriceModel()->getSelectionFinalPrice($productModel, $bundleProduct, 1, 1),
							'tax_class_id' => $bundleProduct->getTaxClassId(),
						);
					}
				}
				if ($canChangeQty && $bundleOption->isMultiSelection()) {
					$canChangeQty = 0;
				}
				$productData['bundle_options'][] = array(
					'id' => $bundleOption->getId(),
					'label' => $bundleOption->getDefaultTitle(),
					'required' => $bundleOption->getRequired(),
					'multiselection' => $bundleOption->isMultiSelection(),
					'products' => $products,
					'can_change_qty' => $canChangeQty,
				);


			}
		} else if ($type instanceof Mage_Downloadable_Model_Product_Type) {
			$productData['type'] = 'downloadable';
			$productData['links_title'] = $type->getProduct()->getLinksTitle();
			/** @var $link Mage_Downloadable_Model_Link */
			foreach($type->getLinks() as $link) {
				$productData['links'][] = array(
					'id' => $link->getId(),
					'title' => $link->getTitle(),
					'price' => $link->getPrice(),
				);
			}
		}
		return $productData;
	}

	public function getTierPrices(Mage_Catalog_Model_Product $product) {
		$prices = array();
		if (($tierPrices = $product->getFormatedTierPrice()) && is_array($tierPrices)) {
			foreach ($tierPrices as $tierPrice) {
				if (isset($tierPrice['price_qty'])) {
					$prices[(int)$tierPrice['price_qty']] = $product->getTierPrice($tierPrice['price_qty']);
				}
			}
		}

		return $prices;
	}
}
?>
