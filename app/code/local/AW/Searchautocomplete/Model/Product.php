<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Searchautocomplete
 * @version    3.2.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AW_Searchautocomplete_Model_Product extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection {

    public function addUrlRewrites() {
        $this->getSelect()->joinLeft(array('urwr' => $this->getTable('core/url_rewrite')), '(urwr.product_id=e.entity_id) AND (urwr.store_id=' . $this->getStoreId() . ')', array('request_path'));
        return $this;
    }

    /**
     * Selecting products from multiple categories
     * @param string $categories categories list separated by commas
     * @return AW_Autorelated_Model_Product_Collection
     */
    public function addCategoriesFilter($categories, $includeSubCategories = false) {
        if (!is_array($categories))
            $categories = @explode(',', $categories);
        $sqlCategories = array();
        if ($includeSubCategories) {
            foreach ($categories as $categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $sqlCategories = array_merge($sqlCategories, $category->getAllChildren(true));
            }
        } else {
            $sqlCategories = $categories;
        }
        $sqlCategories = array_unique($sqlCategories);
        if (is_array($sqlCategories))
            $categories = @implode(',', $sqlCategories);
        $alias = 'cat_index';
        $categoryCondition = $this->getConnection()->quoteInto(
                $alias . '.product_id=e.entity_id' . ($includeSubCategories ? '' : ' AND ' . $alias . '.is_parent=1') . ' AND ' . $alias . '.store_id=? AND ', $this->getStoreId());
        $categoryCondition.= $alias . '.category_id IN (' . $categories . ')';
        $this->getSelect()->joinInner(
                array($alias => $this->getTable('catalog/category_product_index')), $categoryCondition, array('position' => 'position')
        );
        $this->_categoryIndexJoined = true;
        $this->_joinFields['position'] = array('table' => $alias, 'field' => 'position');
        return $this;
    }

    public function addFilterByIds($ids) {
        if ($ids) {
            $whereString = '(e.entity_id IN (';
            $whereString .= implode(',', $ids);
            $whereString .= '))';
            $this->getSelect()->where($whereString);
        }
        return $this;
    }

    /**
     * Covers bug in Magento function
     * @return Varien_Db_Select
     */
    public function getSelectCountSql() {
        $catalogProductFlatHelper = Mage::helper('catalog/product_flat');
        if ($catalogProductFlatHelper && $catalogProductFlatHelper->isEnabled())
            return parent::getSelectCountSql();
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        return $countSelect->reset()->from($this->getSelect(), array())->columns('COUNT(*)');
    }

}