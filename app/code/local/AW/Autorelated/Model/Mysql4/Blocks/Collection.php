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


class AW_Autorelated_Model_Mysql4_Blocks_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('awautorelated/blocks');
    }

    public function addIdFilter($id)
    {
        $this->getSelect()->where('id = ?', $id);
        return $this;
    }

    public function addPositionFilter($position)
    {
        $this->getSelect()->where('position = ?', $position);
        return $this;
    }

    /**
     * Filters collection by store ids
     * @param $stores
     * @return AW_Autorelated_Model_Mysql4_Blocks_Collection
     */
    public function addStoreFilter($stores = array())
    {
        if (!$stores) {
            $stores = array(Mage::app()->getStore()->getId());
        } else if (is_string($stores) && strlen($stores)) {
            $stores = explode(',', $stores);
        }
        if (!in_array('0', $stores)) {
            $stores[] = '0';
        }
        if ($stores) {
            $conditions = array();
            foreach ($stores as $storeId) {
                $conditions[] = array('finset' => $storeId);
            }
            $this->addFieldToFilter('store', $conditions);
        }
        return $this;
    }

    public function addStatusFilter($enabled = true)
    {
        $this->getSelect()->where('status = ?', $enabled ? 1 : 0);
        return $this;
    }

    public function addCustomerGroupFilter($group)
    {
        $this->getSelect()->where("((FIND_IN_SET('" . Mage_Customer_Model_Group::CUST_GROUP_ALL . "', `customer_groups`)) OR (FIND_IN_SET(?, `customer_groups`)))", $group);
        return $this;
    }

    public function addDateFilter($date = null)
    {
        if ($date === null)
            $date = now(true);
        $this->getSelect()->where('(date_from IS NULL OR date_from <= ?) AND (date_to IS NULL OR date_to >= ?)', $date, $date);
        return $this;
    }

    public function addCategoryBlockTypeFilter()
    {
        return $this->addTypeFilter(AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK);
    }

    public function addProductBlockTypeFilter()
    {
        return $this->addTypeFilter(AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK);
    }

    public function addTypeFilter($type)
    {
        $this->getSelect()->where('type = ?', $type);
        return $this;
    }

    /**
     * Covers bug in Magento function
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        return $countSelect->reset()->from($this->getSelect(), array())->columns('COUNT(*)');
    }

    public function setPriorityOrder($dir = 'ASC')
    {
        $this->setOrder('main_table.priority', $dir);
        return $this;
    }

    protected function _afterLoad()
    {
        foreach ($this->getItems() as $item) {
            $item->callAfterLoad();
        }
        return parent::_afterLoad();
    }
}
