<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Autorelated_Model_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    protected $_priceIndexTableJoined = false;

    public function addUrlRewrites()
    {
        $this->getSelect()->joinLeft(array('urwr' => $this->getTable('core/url_rewrite')), '(urwr.product_id=e.entity_id) AND (urwr.store_id=' . $this->getStoreId() . ')', array('request_path'));
        return $this;
    }

    /**
     * Selecting products from multiple categories
     * @param string $categories categories list separated by commas
     * @return AW_Autorelated_Model_Product_Collection
     */
    public function addCategoriesFilter($categories, $includeSubCategories = false)
    {
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
        $categoryCondition .= $alias . '.category_id IN (' . $categories . ')';
        $this->getSelect()->joinInner(
            array($alias => $this->getTable('catalog/category_product_index')), $categoryCondition, array('position' => 'position')
        );
        $this->_categoryIndexJoined = true;
        $this->_joinFields['position'] = array('table' => $alias, 'field' => 'position');
        return $this;
    }

    public function addFilterByIds($ids)
    {
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
    public function getSelectCountSql()
    {
        $catalogProductFlatHelper = Mage::helper('catalog/product_flat');
        if ($catalogProductFlatHelper && $catalogProductFlatHelper->isEnabled())
            return parent::getSelectCountSql();
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        return $countSelect->reset()->from($this->getSelect(), array())->columns('COUNT(*)');
    }


    public function joinCategoriesByProduct()
    {
        $conditions = array(
            'cat_index.product_id=e.entity_id',
            $this->getConnection()->quoteInto('cat_index.store_id = ?', $this->getStoreId())
        );
        $conditions[] = $this->getConnection()
            ->quoteInto('cat_index.visibility IN(?)', array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
        ));
        $this->getSelect()->join(
            array('cat_index' => $this->getTable('catalog/category_product_index')),
            join(' AND ', $conditions),
            array('cat_index_position' => 'position')
        );

        $categoryIds = Mage::getModel('catalog/category')->load(Mage::app()->getStore($this->getStoreId())->getRootCategoryId())->getAllChildren();
        if ($categoryIds) {
            $this->getSelect()->join(
                array('root_category' => $this->getTable('catalog/category_product')),
                "cat_index.product_id = root_category.product_id AND cat_index.category_id = root_category.category_id AND root_category.category_id IN({$categoryIds})",
                array()
            );
        }
        return $this;
    }

    public function addPriceAttributeToFilter($field, $condition)
    {
        if (!$this->_priceIndexTableJoined) {
            $this->addPriceData();
            $this->_priceIndexTableJoined = true;
        }
        $this->getSelect()->where($this->_getConditionSql('price_index.' . $field, $condition));
        return $this;
    }
}
