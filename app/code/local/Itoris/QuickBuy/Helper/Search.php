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

class Itoris_QuickBuy_Helper_Search extends Mage_Core_Helper_Abstract {

	private $products = array();
	private $totalRows = 0;
	private $limitFrom = 0;
	private $limit = 0;
	private $searchingWords = array();
	private $searchingText = '';
	const DEFAULT_LIMIT = 5;
	const DEFAULT_LIMIT_FROM = 0;
	const DEFAULT_ORDER = 'asc';
	const DEFAULT_ORDER_BY = 'product_name';

	public function getProducts() {
		return $this->products;
	}

	public function getTotalRows() {
		return $this->totalRows;
	}

	public function getLimitFrom() {
		return $this->limitFrom;
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
	public function searchProducts($text, array $selectedProducts, $limit = Itoris_QuickBuy_Helper_Search::DEFAULT_LIMIT, $limitFrom = Itoris_QuickBuy_Helper_Search::DEFAULT_LIMIT_FROM,
			$order = Itoris_QuickBuy_Helper_Search::DEFAULT_ORDER, $orderBy = Itoris_QuickBuy_Helper_Search::DEFAULT_ORDER_BY
	) {
		$this->limit = (int)$limit;
		$this->limitFrom = (int)$limitFrom;
		if (strcmp($order,'desc')) {
			$order = 'asc';
		}
		$selectedProducts = implode(',', $selectedProducts);
		$websiteId = (int)Mage::app()->getWebsite()->getId();
		/** @var $resource Varien_Db_Adapter_Pdo_Mysql */
		$resource = Mage::getSingleton('core/resource')->getConnection('core_write');
		/** @var $resourceModel Mage_Core_Model_Resource */
		$resourceModel = Mage::getSingleton('core/resource');
		$tableCataloginventoryStockItem = $resourceModel->getTableName('cataloginventory_stock_item');
		$tableCatalogProductEntity = $resourceModel->getTableName('catalog_product_entity');
		$tableCatalogEntityInt = $resourceModel->getTableName('catalog_product_entity_int');
		$tableEavAttribute = $resourceModel->getTableName('eav_attribute');
		$tableCatalogCategoryEntity = $resourceModel->getTableName('catalog_category_entity');
		$tableCatalogCategoryEntityVarchar = $resourceModel->getTableName('catalog_category_entity_varchar');
		$tableCatalogCategoryProduct = $resourceModel->getTableName('catalog_category_product');
		$tableTag = $resourceModel->getTableName('tag');
		$tableTagRelation = $resourceModel->getTableName('tag_relation');
		$tableCatalogProductEntityVarchar = $resourceModel->getTableName('catalog_product_entity_varchar');
		$tableCatalogProductEntityText = $resourceModel->getTableName('catalog_product_entity_text');
		$tableCatalogProductIndexPrice = $resourceModel->getTableName('catalog_product_index_price');
		$tableCatalogProductWebsite = $resourceModel->getTableName('catalog_product_website');

		$this->searchingWords = $this->parseStrToWords($text);
		$this->searchingText = $text;
		$serchingWords = implode('|', $this->searchingWords);
		$serchingWords = $resource->quote($serchingWords);

		$ordering = array(
				'sku'           => 'pe.sku',
				'category_name' => 'cev.value',
				'price'         => 'pip.min_price',
				'product_name'  => 'pev.value',
		);
		$orderBy = $ordering[$orderBy];
		$useConfigColumn = 'use_config_enable_qty_inc';
		try {
			$resource->fetchAll("select {$useConfigColumn} from {$tableCataloginventoryStockItem} limit 1");
		} catch (Exception $e) {
			$useConfigColumn = 'use_config_enable_qty_increments';
		}
		/** @var $helper Itoris_QuickBuy_Helper_Data */
		$helper = Mage::helper('itoris_quickbuy');
		$visibilityValues = array(2,3,4);
		if ($helper->getSettings()->getShowNotVisibleProducts()) {
			$visibilityValues[] = 1;
		}
		$visibilityValues = implode(',', $visibilityValues);
		$query = "select sql_calc_found_rows pw.website_id, e.product_id, pe.has_options, pe.required_options, pev.value as product, pe.sku, cev.value as category, pip.price, pip.min_price, pip.max_price, pip.tier_price, ce.entity_id as category_id,
					e.{$useConfigColumn} as use_config_enable_qty_inc, e.enable_qty_increments, e.qty_increments, e.min_sale_qty, e.use_config_qty_increments, pei_v.value as visibility, pe.type_id as type
				  from {$tableCataloginventoryStockItem} as e

				  inner join {$tableCatalogProductEntity} as pe
				  on pe.entity_id = e.product_id

				  inner join {$tableCatalogProductWebsite} as pw
				  on pe.entity_id = pw.product_id and pw.website_id = {$websiteId}

				  inner join {$tableEavAttribute} as ea
				  on ea.entity_type_id = pe.entity_type_id and ea.attribute_code = 'status'
				  inner join {$tableCatalogEntityInt} as pei
				  on pei.entity_id = e.product_id and pei.value = 1 and pei.attribute_id = ea.attribute_id

				  inner join {$tableEavAttribute} as ea_v
				  on ea_v.entity_type_id = pe.entity_type_id and ea_v.attribute_code = 'visibility'
				  inner join {$tableCatalogEntityInt} as pei_v
				  on pei_v.entity_id = e.product_id and pei_v.attribute_id = ea_v.attribute_id and pei_v.value in ({$visibilityValues})

				  inner join {$tableEavAttribute} as ea_n
				  on ea_n.entity_type_id = pe.entity_type_id and ea_n.attribute_code = 'name'
				  inner join {$tableCatalogProductEntityVarchar} as pev
				  on pev.entity_id = e.product_id and pev.attribute_id = ea_n.attribute_id

				  left join {$tableCatalogCategoryProduct} as cp
				  on cp.product_id = e.product_id
				  left join {$tableCatalogCategoryEntity} as ce
				  on ce.entity_id = cp.category_id
				  left join {$tableEavAttribute} as ea_cn
				  on ea_cn.entity_type_id = ce.entity_type_id and ea_cn.attribute_code = 'name'
				  left join {$tableCatalogCategoryEntityVarchar} as cev
				  on cev.entity_id = ce.entity_id and cev.attribute_id = ea_cn.attribute_id

				  inner join {$tableCatalogProductIndexPrice} as pip
				  on pip.entity_id = e.product_id

				  where e.product_id in (

					select pe.entity_id	from {$tableCatalogProductEntity} AS pe
					where pe.sku REGEXP {$serchingWords}

					union distinct

					select cp.product_id from {$tableCatalogCategoryEntity} as ce
					inner join {$tableEavAttribute} as ea
					on ea.entity_type_id = ce.entity_type_id and ea.attribute_code = 'name'
					inner join {$tableCatalogCategoryEntityVarchar} as cev
					on cev.entity_id = ce.entity_id and cev.attribute_id = ea.attribute_id and cev.value REGEXP {$serchingWords}
					inner join {$tableCatalogCategoryProduct} as cp
					on cp.category_id = ce.entity_id

					union distinct

					select tr.product_id from {$tableTag} as t
					inner join {$tableTagRelation} as tr
					on tr.tag_id = t.tag_id and tr.active = 1
					where t.name REGEXP {$serchingWords}

					union distinct

					select pe.entity_id from {$tableCatalogProductEntity} as pe
					inner join {$tableEavAttribute} as ea
					on ea.entity_type_id = pe.entity_type_id and ea.attribute_code = 'name'
					inner join {$tableCatalogProductEntityVarchar} as pev
					on pev.entity_id = pe.entity_id and pev.attribute_id = ea.attribute_id and pev.value REGEXP {$serchingWords}

					union distinct

					select pe.entity_id from {$tableCatalogProductEntity} as pe
					inner join {$tableEavAttribute} as ea
					on ea.entity_type_id = pe.entity_type_id and ea.attribute_code = 'description'
					inner join {$tableCatalogProductEntityText} as pet
					on pet.entity_id = pe.entity_id and pet.attribute_id = ea.attribute_id and pet.value REGEXP {$serchingWords}
				  )
				  and e.product_id not in ({$selectedProducts})

				  group by e.product_id
				  order by {$orderBy} {$order}";
		$this->products = $resource->fetchAll($query);
		$this->totalRows = $resource->fetchOne("SELECT FOUND_ROWS()");
	}

	/**
	 * Get category path for category
	 *
	 * @param $category
	 * @return string
	 */
	public function getCategoryPath($category) {
		/** @var $category Mage_Catalog_Model_Category */
		$output = '';
		$parentIds = $category->getPathIds();
		$parentCategories = $category->getParentCategories();
		$countIds = count($parentIds);
		for ($i = 0; $i < $countIds; $i++) {
			if (isset($parentCategories[$parentIds[$i]])) {
				$output .= $parentCategories[$parentIds[$i]]->getName();
				if ($i != $countIds - 1) {
					$output .= ' / ';
				}
			}
		}
		return $output;
	}

	private function parseStrToWords($str) {
		$words = str_word_count($str, 1, '0123456789');
		foreach ($words as $key => $word) {
			if (strlen($word) < 3) {
				unset($words[$key]);
			}
		}

		return array_values($words);
	}

	public function getSelectedProducts() {
		$storeId = Mage::app()->getStore()->getId();
		$products = Mage::getSingleton('core/session')->getData('products' . $storeId);
		$selectedProducts = Mage::getSingleton('core/session')->getData('selected_products' . $storeId);
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

	/**
	 * Get matches for product
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return array
	 */
	public function getSearchingMatches(Mage_Catalog_Model_Product $product, $categoryName) {
		$matches = array(
			'all_text'  => false,
			'words'     => array(),
			'relevance' => 0,
		);

		$this->checkMatch($matches, $product->getName(), 1);
		$this->checkMatch($matches, $product->getSku(), 2);
		$this->checkMatch($matches, $product->getDescription(), 3);
		$this->checkMatch($matches, $categoryName, 4);

		$matches['not_match'] = !$matches['all_text'] && count($matches['words']) != count($this->searchingWords);

		return $matches;
	}

	private function checkMatch(&$matches, $text, $relevance) {
		if ($matches['all_text']) {
			return;
		}
		if (stripos($text, $this->searchingText) !== false) {
			$matches['all_text'] = true;
			$matches['relevance'] = $relevance;
			return;
		}

		foreach ($this->searchingWords as $word) {
			if (stripos($text, $word) !== false) {
				if (!in_array($word, $matches['words'])) {
					$matches['words'][] = $word;
				}
			}
		}
		if (count($matches['words']) == count($this->searchingWords)) {
			$matches['relevance'] = $relevance;
		}
	}

	public function sortProductBySearchRelevance($productMapOfSearchMatches, $products) {
		$sortedMap = array();

		foreach ($productMapOfSearchMatches as $index => $match) {
			if ($match['all_text']) {
				$sortedMap[] = $index;
			}
			$productMapOfSearchMatches[$index]['index'] = $index;
		}

		for ($i = 0; $i < count($productMapOfSearchMatches); $i++) {
			for ($j = 0; $j < count($productMapOfSearchMatches); $j++) {
				if (isset($productMapOfSearchMatches[$j + 1]) && $productMapOfSearchMatches[$j + 1]['relevance'] < $productMapOfSearchMatches[$j]['relevance']) {
					$matchWithMoreWords = $productMapOfSearchMatches[$j + 1];
					$productMapOfSearchMatches[$j + 1] = $productMapOfSearchMatches[$j];
					$productMapOfSearchMatches[$j] = $matchWithMoreWords;
				}
			}
		}

		foreach ($productMapOfSearchMatches as $match) {
			if (!$match['all_text'] && !$match['not_match']) {
				$sortedMap[] = $match['index'];
			}
		}

		$sortedProducts = array();

		foreach ($sortedMap as $index) {
			$sortedProducts[] = $products[$index];
		}

		return array_slice($sortedProducts, $this->limitFrom, $this->limit);
	}

	public function deleteNotMatchProducts($productMapOfSearchMatches, $products) {
		$resultProducts = array();

		foreach ($products as $index => $product) {
			if (isset($productMapOfSearchMatches[$index]) && !$productMapOfSearchMatches[$index]['not_match']) {
				$resultProducts[] = $product;
			}
		}

		$this->totalRows = count($resultProducts);

		return array_slice($resultProducts, $this->limitFrom, $this->limit);
	}
}
 
?>