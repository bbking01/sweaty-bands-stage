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


class AW_Autorelated_Model_Blocks extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('awautorelated/blocks');
    }

    /**
     * Unserialize database fields
     * @return AW_Autorelated_Model_Blocks
     */
    protected function _afterLoad()
    {
        if ($this->getData('currently_viewed') && is_string($this->getData('currently_viewed')))
            $this->setData('currently_viewed', @unserialize($this->getData('currently_viewed')));
        if ($this->getData('related_products') && is_string($this->getData('related_products')))
            $this->setData('related_products', @unserialize($this->getData('related_products')));
        if ($this->getData('customer_groups') !== null && is_string($this->getData('customer_groups')))
            $this->setData('customer_groups', @explode(',', $this->getData('customer_groups')));
        if ($this->getData('date_from') && is_empty_date($this->getData('date_from')))
            $this->setData('date_from', '');
        if ($this->getData('date_to') && is_empty_date($this->getData('date_to')))
            $this->setData('date_to', '');
        if ($this->getData('store') !== null && is_string($this->getData('store')))
            $this->setData('store', @explode(',', $this->getData('store')));
        $this->humanizeData();
        return parent::_afterLoad();
    }

    /**
     * Serialize fields for database storage
     * @return AW_Autorelated_Model_Blocks
     */
    protected function _beforeSave()
    {
        if ($this->getData('currently_viewed') && is_array($this->getData('currently_viewed')))
            $this->setData('currently_viewed', @serialize($this->getData('currently_viewed')));
        if ($this->getData('related_products') && is_array($this->getData('related_products')))
            $this->setData('related_products', @serialize($this->getData('related_products')));
        if ($this->getData('customer_groups') !== null && is_array($this->getData('customer_groups')))
            $this->setData('customer_groups', @implode(',', $this->getData('customer_groups')));
        if ($this->getData('date_to') && !is_empty_date($this->getData('date_to')))
            $this->setData('date_to', date(AW_Autorelated_Model_Mysql4_Blocks::MYSQL_DATETIME_FORMAT, strtotime($this->getData('date_to'))));
        else
            $this->setData('date_to', null);
        if ($this->getData('date_from') && !is_empty_date($this->getData('date_from')))
            $this->setData('date_from', date(AW_Autorelated_Model_Mysql4_Blocks::MYSQL_DATETIME_FORMAT, strtotime($this->getData('date_from'))));
        else
            $this->setData('date_from', null);
        if ($this->getData('store') !== null && is_array($this->getData('store')))
            $this->setData('store', @implode(',', $this->getData('store')));
        return parent::_beforeSave();
    }

    /*
     * return block type by id
     * $id int
     */

    public function getTypeById($id)
    {
        $block = $this->load($id);
        return $block->getType();
    }

    /**
     * Creates Varien_Object instances for data stored
     * in currently_viewed and related_products fields
     * @return AW_Autorelated_Model_Blocks
     */
    public function humanizeData()
    {
        if (is_array($this->getData('currently_viewed')))
            $this->setData('currently_viewed', new Varien_Object($this->getData('currently_viewed')));
        if (is_array($this->getData('related_products')))
            $this->setData('related_products', new Varien_Object($this->getData('related_products')));
        return $this;
    }

    public function callAfterLoad()
    {
        return $this->_afterLoad();
    }
}
