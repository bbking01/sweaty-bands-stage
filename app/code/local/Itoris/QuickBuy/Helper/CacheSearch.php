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
 * @modifications  d. charles sweet 2013 12
 */

class Itoris_QuickBuy_Helper_CacheSearch extends Itoris_QuickBuy_Helper_Data {

	protected $products = null;
	protected $productsSearch = array();
	protected $totalRows = 0;
	protected $limitFrom = 0;
	protected $limit = 0;
	protected $searchingWords = array();
	protected $searchingText = '';
	/** @var null | Mage_Catalog_Model_Resource_Category_Collection */
	protected $categories = null;

	const DEFAULT_LIMIT = 5;
	const DEFAULT_LIMIT_FROM = 0;
	const DEFAULT_ORDER = 'asc';
	const DEFAULT_ORDER_BY = 'product_name';

	public function getProducts() {
		 
		if (is_null($this->products)) {
			$this->loadProducts();
		}
		
		return $this->products;
	}

	public function getTotalRows() {
		return $this->totalRows;
	}

	/**
	 * @return Itoris_Quickbuy_Helper_Cache
	 */
	public function getCache() {
		return Mage::helper('itoris_quickbuy/cache');
	}

	public function loadProducts($onlyCreateCache = false) {
		 		
		$storeId = Mage::app()->getStore()->getId();
		$customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		$filePostfix = '_gr' . $customerGroupId . '_s' . $storeId;
		$cache = $this->getCache();
		if (!$cache->isFileExists('products', $filePostfix)) {
					 	
			$collection = $this->getProductsRawSql();
					 	
			if (count($collection)) {
				foreach ($collection as &$product) {
					$product['description'] = strip_tags(preg_replace('/\r|\n/', '', $product['description']));
				}
			}
 		 
			$cache->saveCacheInFile($collection, 'products', $filePostfix);

			$this->products = $collection;
		} else {
			if (!$onlyCreateCache) {
				$this->products = $cache->loadCacheFromFile('products', $filePostfix);
			}
		}
	}

	protected function getProductsRawSql() {
				 
		/** @var $resource Mage_Core_Model_Resource */
		$resource = Mage::getSingleton('core/resource');
		/** @var $connection Varien_Db_Adapter_Pdo_Mysql */
		$connection = $resource->getConnection('read');
		$storeId = (int)Mage::app()->getStore()->getId();
		$websiteId = (int)Mage::app()->getWebsite()->getId();
		$customerGroupId = (int)Mage::getSingleton('customer/session')->getCustomer()->getGroupId();
		$rootCategoryId = (int)Mage::app()->getStore()->getRootCategoryId();

		$tableCatalogProductEntity = $resource->getTableName('catalog_product_entity');
		$tableEavAttribute = $resource->getTableName('eav_attribute');
		$tableEavEntityType = $resource->getTableName('eav_entity_type');
		$tableCatalogProductIndexPrice = $resource->getTableName('catalog_product_index_price');
		$tableCatalogCategoryProductIndex = $resource->getTableName('catalog_category_product_index');
		$tableTag = $resource->getTableName('tag');
		$tableTagRelation = $resource->getTableName('tag_relation');
		$tableCataloginventoryStockItem = $resource->getTableName('cataloginventory_stock_item');
		$tableCatalogCategoryProduct = $resource->getTableName('catalog_category_product');
		$entityAttributeTables = array(
			'int'     => $resource->getTableName('catalog_product_entity_int'),
			'varchar' => $resource->getTableName('catalog_product_entity_varchar'),
			'text'    => $resource->getTableName('catalog_product_entity_text'),
		);

		$attributes = $connection->fetchAll("select e.attribute_id, e.attribute_code, e.backend_type from {$tableEavAttribute} as e
				where e.entity_type_id = (select entity_type.entity_type_id from {$tableEavEntityType} as entity_type where entity_type.entity_type_code = 'catalog_product')
					and e.attribute_code in('name', 'small_image', 'thumbnail', 'image', 'description', 'visibility', 'status')
		");

		$attributesToSelect = array();
		$attributesJoin = array();
		foreach ($attributes as $attribute) {
			if (isset($entityAttributeTables[$attribute['backend_type']])) {
				$tableAlias = 't_' . $attribute['attribute_code'];
				$attributesJoin[] = "left join {$entityAttributeTables[$attribute['backend_type']]} as {$tableAlias}_def
					on {$tableAlias}_def.entity_id = e.entity_id and {$tableAlias}_def.attribute_id = {$attribute['attribute_id']} and {$tableAlias}_def.store_id = 0
					left join {$entityAttributeTables[$attribute['backend_type']]} as {$tableAlias}
					on {$tableAlias}.entity_id = e.entity_id and {$tableAlias}.attribute_id = {$attribute['attribute_id']} and {$tableAlias}.store_id = {$storeId}
				";
				$attributesToSelect[] = "if({$tableAlias}.store_id > 0, {$tableAlias}.value, {$tableAlias}_def.value) as {$attribute['attribute_code']}";
			}
		}

		$visibilityValues = array(
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
		);
		if ($this->getSettings()->getShowNotVisibleProducts()) {
			$visibilityValues[] = Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE;
		}
		$visibilityValues = 'in(' . implode(',', $visibilityValues) . ')';
		$visibilityCondition = 'visibility ' . $visibilityValues;

		$attributesToSelect = implode(', ', $attributesToSelect);
		$attributesJoin = implode(' ', $attributesJoin);

		$useConfigColumn = 'use_config_enable_qty_inc';
		try {
			$connection->fetchAll("select {$useConfigColumn} from {$tableCataloginventoryStockItem} limit 1");
		} catch (Exception $e) {
			$useConfigColumn = 'use_config_enable_qty_increments';
		}

		$sql = "select e.entity_id, e.sku, e.type_id, e.required_options, e.has_options, if(price_index.tier_price is not null, LEAST(price_index.min_price, price_index.tier_price), price_index.min_price) as min_price,
			price_index.final_price, price_index.tax_class_id, group_concat(DISTINCT tag.name) as tags, group_concat(DISTINCT p_category.category_id SEPARATOR ',') as category_ids,
			if (price_index.entity_id and p_stock.is_in_stock, 1, 0) as is_in_stock, p_stock.use_config_qty_increments, p_stock.{$useConfigColumn} as use_config_enable_qty_inc, p_stock.enable_qty_increments, p_stock.qty_increments, p_stock.min_sale_qty,
			{$attributesToSelect} from {$tableCatalogProductEntity} as e
			inner join {$tableCatalogCategoryProductIndex} as cat_index
				on cat_index.product_id=e.entity_id and cat_index.store_id={$storeId} and cat_index.visibility {$visibilityValues} AND cat_index.category_id={$rootCategoryId}
			left join {$tableCatalogProductIndexPrice} as price_index
				on price_index.entity_id = e.entity_id and price_index.website_id = {$websiteId} and price_index.customer_group_id = {$customerGroupId}
			left join {$tableCataloginventoryStockItem} as p_stock
				on p_stock.product_id=e.entity_id
			{$attributesJoin}
			left join {$tableTagRelation} as tag_rel
				on tag_rel.product_id = e.entity_id and tag_rel.active = 1
			left join {$tableTag} as tag
				on tag.tag_id = tag_rel.tag_id and tag.status=1
			left join {$tableCatalogCategoryProduct} as p_category
				  on p_category.product_id = e.entity_id
			group by e.entity_id
			having {$visibilityCondition} and status = 1
		";

		return $connection->fetchAll($sql);
	}

	/**
	 * Add category name and path to products
	 *
	 * @param $products
	 * @return Itoris_QuickBuy_Helper_Search
	 */
	protected function applyCategoriesToProducts($products) {
		
				 	
		/** @var $categories Mage_Catalog_Model_Resource_Category_Collection */
		$categories = Mage::getModel('catalog/category')->getCollection();
		$categories->addAttributeToSelect('name')
			->addFieldToFilter('level', array('gt' => 1));

		$preparedCategories = array();
		if ($categories->getSize()) {
			foreach ($categories as $category) {
				$categoryData = array(
					'id'   => $category->getId(),
					'name' => $category->getName(),
				);
				$pathIds = $category->getPathIds();
				$path = array();
				foreach ($pathIds as $categoryId) {
					$pathCategory = $categories->getItemById($categoryId);
					if ($pathCategory) {
						$path[] = $pathCategory->getName();
					}
				}
				$categoryData['path'] = implode(' / ', $path);
				$preparedCategories[$category->getId()] = $categoryData;
			}

			$categoryIds = $categories->getAllIds();
			$categoryIdsCondition = '(' . implode(',', $categoryIds) . ')';
			$resource = Mage::getSingleton('core/resource');
			$connection = $resource->getConnection('read');
			$tableCatalogCategoryProduct = $resource->getTableName('catalog_category_product');
			$result = $connection->fetchAll("select category_id, product_id from {$tableCatalogCategoryProduct} where category_id in {$categoryIdsCondition}");

			$productCategories = array();
			foreach ($result as $row) {
				if (isset($preparedCategories[$row['category_id']])) {
					if (!isset($productCategories[$row['product_id']])) {
						$productCategories[$row['product_id']] = array();
					}
					$productCategories[$row['product_id']][] = $preparedCategories[$row['category_id']];
				}
			}

			if (!empty($productCategories)) {
				foreach ($products as $product) {
					if (isset($productCategories[$product->getId()])) {
						$product->setCategories($productCategories[$product->getId()]);
					}
				}
			}
		}
		return $this;
	}

	/**
	 * Search products by parameters
	 *
	 * @param $text
	 * @param array $selectedProducts
	 * @param int $limit
	 * @param int $limitFrom
	 * @param string $order
	 * @param string $orderBy
	 */
	public function searchProducts($text, $limit = Itoris_QuickBuy_Helper_Search::DEFAULT_LIMIT, $limitFrom = Itoris_QuickBuy_Helper_Search::DEFAULT_LIMIT_FROM,
								   $order = Itoris_QuickBuy_Helper_Search::DEFAULT_ORDER, $orderBy = Itoris_QuickBuy_Helper_Search::DEFAULT_ORDER_BY
	) {
		
						 	
		if ($this->getSettings()->getUseCatalogSearchTerms()) {
			$query = $this->getQuery($text);
			if ($query->getRedirect()) {
				return array('redirect' => $query->getRedirect());
			} else if ($query->getSynonymFor()) {
				$text = $query->getSynonymFor();
			}
		}
		$products = array();
		/** @var $inventoryHelper Mage_CatalogInventory_Helper_Data */
		$inventoryHelper = Mage::helper('cataloginventory');
		$canShowOutOfStock = $inventoryHelper->isShowOutOfStock();

		$request = Mage::app()->getRequest();
		$result = array(
			'limit'     => (int)$request->getParam('limit', Itoris_QuickBuy_Helper_Search::DEFAULT_LIMIT),
			'limitFrom' => (int)$request->getParam('limitFrom', Itoris_QuickBuy_Helper_Search::DEFAULT_LIMIT_FROM),
			'order'     => $request->getParam('order', Itoris_QuickBuy_Helper_Search::DEFAULT_ORDER),
			'orderBy'   => $request->getParam('orderBy', Itoris_QuickBuy_Helper_Search::DEFAULT_ORDER_BY),
		);
		$this->getProducts();
		$showGroupedInsteadOfSimple = $this->getSettings()->getShowGroupedInsteadOfSimple();
		$groupedSkus = array();
		$usedSkus = array();
		foreach ($this->products as $key => $product) {
			if (!$canShowOutOfStock && !$product['is_in_stock']) {
				unset($this->products[$key]);
				continue;
			}
			if ($this->isMatchCategory($text, $product) || $this->isMatch($text, $product, array('name', 'sku', 'description', 'tags'))) {
				if ($showGroupedInsteadOfSimple && $product['type_id'] == 'simple') {
					$parentSkus = $this->getParentGroupedProductSkus($product['entity_id']);
					if (!empty($parentSkus)) {
						$groupedSkus = array_merge($groupedSkus, $parentSkus);
						continue;
					}
				}
				$products[] = $this->convertProductData($product);
				$usedSkus[] = $product['sku'];
			}
			if (!$showGroupedInsteadOfSimple) {
				unset($this->products[$key]);
			}
		}
		if ($showGroupedInsteadOfSimple && !empty($groupedSkus)) {
			foreach ($this->products as $product) {
				if (in_array($product['sku'], $groupedSkus) && !in_array($product['sku'], $usedSkus)) {
					//add category path
					$this->isMatchCategory($text, $product, true);
					$products[] = $this->convertProductData($product);
					$usedSkus[] = $product['sku'];
				}
			}
		}
		unset($this->products);
		$this->products = array();
		return $this->prepareProductsData($products, $result);
	}
/*=========================================================================================*/
	protected function convertProductData($product) {
/*=========================================================================================*/		
/*
 * Modification: adjust price to logged in clients wholesale group
 * author:  d charles sweet
 * date:    2013 12 2013 -- 2013 
 * 001-10-0144-01
 * DCS:
 * 
 */	

$pricing= Mage::helper('itoris_quickbuy')->getGroupPrice($product['sku']);
 
		return array(
			'entity_id'            => $product['entity_id'],
			'product_id'           => $product['entity_id'],
			'product'              => $product['name'],
			'sku'                  => $product['sku'],
			//'sku'                  => $pricing,
			//'sku'                  => $groupName,
			//'min_price'            => $product['min_price'],
			'min_price'            => $pricing,
			//'final_price'          => $product['final_price'],
			'final_price'          => $pricing,
			'small_image'          => $product['small_image'],
			'thumbnail'            => $product['thumbnail'],
			'description'          => $product['description'],
			'price'                =>$pricing,
			//'price'                => $product['customer_groups_price'],
			//'price'                => $product['price'],

			
			
			'type'                 => $product['type_id'],
			'has_options'          => $product['has_options'],
			'required_options'     => $product['required_options'],
			'out_of_stock'         => !((int)$product['is_in_stock']),
			'tax_class_id'         => $product['tax_class_id'],
			'use_config_qty_increments' => (bool)$product['use_config_qty_increments'],
			'use_config_enable_qty_inc' => (bool)$product['use_config_enable_qty_inc'],
			'enable_qty_increments'     => (bool)$product['enable_qty_increments'],
			'qty_increments'            => (float)$product['qty_increments'],
			'min_qty'                   => (float)$product['min_sale_qty'],
			'category_id'               => $product['category_id'],
			'category'                  => $product['category'],
			'category_path'             => $product['category_path'],
			'visibility'                => $product['visibility'],
		);
	}//END_convertProductData








	protected function prepareProductsData($products, $result) {
			
		$result['totalRows'] = count($products);
		
		
		$size = $result['limit'] - $result['limitFrom'];
		$products = $this->sortProducts($products, $result['orderBy'], $result['order']);
		if (count($products) > $size) {
			$products = array_slice($products, $result['limitFrom'], $result['limit']);
		}

		foreach ($products as $key => $product) {
			
			
			
			
			$productModel = Mage::getModel('catalog/product')->addData($product);
				/** @var $image Mage_Catalog_Helper_Image */
			$image = Mage::helper('catalog/image');
			if ($productModel->getSmallImage()) {
				$image->init($productModel, 'small_image')->resize(85,85);
			} else {
				$image->init($productModel, 'thumbnail')->resize(85,85);
			}
			$products[$key]['image_url'] = $image->__toString();
			$products[$key]['product_url'] = $productModel->getProductUrl();
		}

		$result['products'] = $products;

		return $result;
	}

	protected function sortProducts($products, $orderBy, $dir) {
		switch ($orderBy) {
			case 'sku':
				return $this->sortByAbc($products, 'sku', $dir);
			case 'category_name':
				return $this->sortByCategory($products, $dir);
			case 'price':
				return $this->sortByNumber($products, 'min_price', $dir);
			case 'product_name':
			default:
				return $this->sortByAbc($products, 'product', $dir);
		}
	}

	protected function sortByCategory($products, $dir) {
		$withoutCategory = array();
		$withCategory = array();
		$noCategoryLabel = $this->__('no category');
		foreach ($products as $product) {
			if ($product['category'] == $noCategoryLabel) {
				$withoutCategory[] = $product;
			} else {
				$withCategory[] = $product;
			}
		}
		$withCategory = $this->sortByAbc($withCategory, 'category', $dir);
		$withoutCategory = $this->sortByAbc($withoutCategory, 'name', $dir);
		foreach ($withoutCategory as $product) {
			$withCategory[] = $product;
		}
		return $withCategory;
	}

	protected function sortByAbc($data, $index, $dir = 'asc') {
		$fieldValues = array();
		foreach ($data as $key => $row) {
			$fieldValues[$key] = strtolower(trim($row[$index]));
		}
		if ($dir == 'asc') {
			asort($fieldValues);
		} else {
			arsort($fieldValues);
		}
		$sortedData = array();
		foreach ($fieldValues as $key => $value) {
			$sortedData[] = $data[$key];
		}

		return $sortedData;
	}

	protected function sortByNumber($data, $index, $dir = 'asc') {
		for ($i = 0; $i < count($data); $i++) {
			for ($j = 0; $j < count($data) - 1; $j++) {
				if (isset($data[$j + 1]) && (
					($dir == 'asc' && (float)$data[$j][$index] > (float)$data[$j + 1][$index])
					|| ($dir == 'desc' && (float)$data[$j][$index] < (float)$data[$j + 1][$index])
				)) {
					$temp = $data[$j];
					$data[$j] = $data[$j + 1];
					$data[$j + 1] = $temp;
				}
			}
		}

		return $data;
	}

	/**
	 * Is match some param from $searchIn in $data for the keyword
	 *
	 * @param $keyword
	 * @param $data
	 * @param $searchIn
	 * @return bool
	 */
	protected function isMatch($keyword, $data, $searchIn) {
		$keywords = explode(' ', $keyword);
		foreach ($searchIn as $searchType) {
			if (isset($data[$searchType])) {
				foreach ($keywords as $_keyword) {
					if (stripos($data[$searchType], $_keyword) === false) {
						continue 2;
					}
				}
				return true;
			}
		}

		return false;
	}

	protected function isMatchCategory($keyword, &$product, $withoutCheck = false) {
		$product['category_id'] = null;
		$product['category'] = $this->__('no category');
		$product['category_path'] = $this->__('no category');
		$categoryIds = explode(',', $product['category_ids']);

		foreach ($categoryIds as $categoryId) {
			$category = $this->getAllCategories()->getItemById($categoryId);
			if ($category) {
				$product['category_id'] = $categoryId;
				$product['category'] = $category->getName();
				$product['category_path'] = $this->getCategoryPath($category);
				if (!$withoutCheck && stripos($category->getName(), $keyword) !== false) {
					return true;
				}
			}
		}
		return false;
	}

	public function getCategoryPath($category) {
		/** @var $category Mage_Catalog_Model_Category */
		$parentIds = $category->getPathIds();
		$parentNames = array();
		foreach ($parentIds as $id) {
			if ($id == 1) {
				continue;
			}
			$category = $this->getAllCategories()->getItemById($id);
			if ($category && $category->getLevel() > 1) {
				$parentNames[] = $category->getName();
			}
		}
		return implode(' / ', $parentNames);
	}
//DCS: Use this?
	protected function getAllCategories() {
		if (is_null($this->categories)) {
			$this->categories = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('name');
		}

		return $this->categories;
	}

	protected function isMatchTag($keyword, $product) {
		$tags = $product['tags'];
		if (!empty($tags)) {
			foreach ($tags as $tag) {
				if (stripos($tag, $keyword) !== false) {
					return true;
				}
			}
		}

		return false;
	}

	public function getSelectedProducts() {
		
		
		$products = Mage::getSingleton('core/session')->getData('products');
		$selectedProducts = Mage::getSingleton('core/session')->getData('selected_products');
		if (!$products) {
			$products = 'null';
			$selectedProducts = 'null';
		} else {
			$products = Zend_Json::encode($products);
			$selectedProducts = Zend_Json::encode($selectedProducts);
		}
		return array(
			'products' => $products,
			'selected_products' => $selectedProducts,
		);
	}

	public function getDefaultProducts($page = 1, $limit = 5, $order = 'product_name', $dir = 'asc') {
		$productIds = $this->getSettings()->getDefaultProductIds();
		$productIds = explode(',', $productIds);
		$productIds = array_map('intval', $productIds);
		$productIds = array_unique($productIds);
		$products = array();
		$result = array(
			'products' => array(),
			'total'    => 0,
		);
		if (count($productIds)) {
			/** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
			$collection = Mage::getResourceModel('catalog/product_collection')
				->setStoreId(Mage::app()->getStore()->getId())
				->addFieldToFilter('entity_id', array('in' => $productIds))
				->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
				->addMinimalPrice()
				->addFinalPrice()
				->addTaxPercents()
				->addUrlRewrite();
			$addLimit = false;
			switch ($order) {
				case 'sku':
					$collection->addAttributeToSort('sku', $dir);
					$addLimit = true;
					break;
				case 'product_name':
					$addLimit = true;
				default:
					$collection->addAttributeToSort('name', $dir);
			}
			if ($addLimit) {
				$collection->setPage($page, $limit);

			}
			Mage::getModel('cataloginventory/stock')->addItemsToProducts($collection);
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

			$productHelper = Mage::helper('itoris_quickbuy/product');
			if ($collection->getSize()) {
				/** @var $product Mage_Catalog_Model_Product */
				foreach ($collection as $product) {
					/** @var $image Mage_Catalog_Helper_Image */
					$image = Mage::helper('catalog/image');
					if ($product->getSmallImage()) {
						$image->init($product, 'small_image')->resize(85,85);
					} else {
						$image->init($product, 'thumbnail')->resize(85,85);
					}
					/** @var Mage_CatalogInventory_Model_Stock_Item $stockItem */
					$stockItem = $product->getStockItem();
					$category = null;
					$categoryIds = $product->getCategoryIds();
					if (isset($categoryIds[0])) {
						$category = $this->getAllCategories()->getItemById($categoryIds[0]);
					}

					$productConfig = array(
						'entity_id'            => $product->getEntityId(),
						'product_id'           => $product->getEntityId(),
						'product'              => $product->getName(),
						'sku'                  => $product->getSku(),
						'min_price'            => $product->getMinPrice(),
						'final_price'          => $product->getFinalPrice(),
						'small_image'          => $product->getSmallImage(),
						'thumbnail'            => $product->getThumbnail(),
						'image_url'            => $image->__toString(),
						'product_url'          => $product->getProductUrl(),
						'description'          => $product->getDescription(),
						'price'                => $product->getFinalPrice(),
						'type'                 => $product->getTypeId(),
						'has_options'          => $product->getHasOptions(),
						'required_options'     => $product->getRequiredOptions(),
						'out_of_stock'         => !$product->isSalable(),
						'tax_class_id'         => $product->getTaxClassId(),
						'use_config_qty_increments' => $stockItem ? (bool)$stockItem->getUseConfigQtyIncrements() : false,
						'use_config_enable_qty_inc' => $stockItem ? (bool)$stockItem->getUseConfigEnableQtyIncrements() : false,
						'enable_qty_increments'     => $stockItem ? (bool)$stockItem->getEnableQtyIncrements() : false,
						'qty_increments'            => $stockItem ? (float)$stockItem->getQtyIncrements() : 0,
						'min_qty'                   => $stockItem ? (float)$stockItem->getMinSaleQty() : 0,
						'category_id'               => $category ? $category->getId() : 0,
						'category'                  => $category ? $category->getName() : '',
						'category_path'             => $category ? $this->getCategoryPath($category) : '',
						'tier_prices'               => $productHelper->getTierPrices($product),
					);
					$products[] = $productHelper->addProductOptionsToArray($productConfig, $product);
				}
				switch ($order) {
					case 'category_name':
						$products = $this->sortByCategory($products, $dir);
						break;
					case 'price':
						$products = $this->sortByNumber($products, 'min_price', $dir);
						break;
				}
				if (!$addLimit) {
					$result['total'] = count($products);
					$limitFrom = ($page - 1) * $limit;
					if (count($products) > $limit - $limitFrom) {
						$products = array_slice($products, $limitFrom, $limit);
					}
				} else {
					$result['total'] = $collection->getSize();
				}
				$result['products'] = $products;
			}
		}
		return $result;
	}

}

?>
