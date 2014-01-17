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
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Model_Campaign extends Mage_Core_Model_Abstract
{
    protected $_profitModel = null;
    protected $_conditionsModel = null;

    public function _construct()
    {
        $this->_init('awaffiliate/campaign');
    }

    public function getProfitModel($reload = false)
    {
        if (is_null($this->_profitModel) || $reload) {
            $this->_profitModel = Mage::getModel('awaffiliate/profit')->loadByCampaignId($this->getId());
        }
        return $this->_profitModel;
    }

    public function getConditionsModel($reload = false)
    {
        if (is_null($this->_conditionsModel) || $reload) {
            $this->_conditionsModel = Mage::getModel('salesrule/rule');
            $this->_conditionsModel->getConditions()->setData($this->getProductSelectionRule());
            $this->_conditionsModel->getActions()->setData($this->getProductSelectionRule());
        }
        return $this->_conditionsModel;
    }

    public function isActive($websiteId = null)
    {
        if ($this->getStatus() != AW_Affiliate_Model_Source_Campaign_Status::ACTIVE) {
            return false;
        }
        if (is_null($websiteId)) {
            $websiteId = Mage::app()->getWebsite()->getId();
        }
        if ($websiteId != 0 && !in_array($websiteId, $this->getStoreIds())) { //website validation
            return false;
        }

        //period validation
        if (!is_null($this->getData('active_from'))) {
            $from = new Zend_Date($this->getData('active_from'), Varien_Date::DATETIME_INTERNAL_FORMAT);
        } else {
            $from = new Zend_Date();
        }
        if (!is_null($this->getData('active_to'))) {
            $to = new Zend_Date($this->getData('active_to'), Varien_Date::DATETIME_INTERNAL_FORMAT);
        } else {
            $to = null;
        }
        $now = new Zend_Date();
        if ($from->compare($now, Zend_Date::DATES) == 1) { // $now less then $from
            return false;
        }
        if (!is_null($to) && $to->compare($now, Zend_Date::DATES) == -1) { // $now great then $to
            return false;
        }
        return true;
    }

    public function isAffiliateAllowed($affiliateId)
    {
        $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
        if (is_null($affiliate->getId())) {
            return false;
        }

        if (in_array($affiliate->getCustomerGroupId(), $this->getAllowedGroups())) {
            return true;
        }
        return false;
    }

    public function getRateForAffiliate($affiliate)
    {
        if ($affiliate instanceof AW_Affiliate_Model_Affiliate) {
            return $this->getProfitModel()->getRateForAffiliate($affiliate);
        } elseif (intval($affiliate) > 0) {
            $affiliateModel = Mage::getModel('awaffiliate/affiliate')->load(intval($affiliate));
            if (!is_null($affiliateModel->getId())) {
                return $this->getProfitModel()->getRateForAffiliate($affiliateModel);
            }
        }
        return null;
    }

    /**
     * Serialize fields for database storage
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        if (is_string($this->getData('store_ids')))
            $this->setData('store_ids', @explode(',', $this->getData('store_ids')));
        if (is_string($this->getData('allowed_groups')))
            $this->setData('allowed_groups', @explode(',', $this->getData('allowed_groups')));
        return parent::_afterLoad();
    }

    /**
     * Serialize fields for database storage
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if ($this->getData('store_ids') !== null && is_array($this->getData('store_ids')))
            $this->setData('store_ids', @implode(',', $this->getData('store_ids')));
        if ($this->getData('allowed_groups') !== null && is_array($this->getData('allowed_groups')))
            $this->setData('allowed_groups', @implode(',', $this->getData('allowed_groups')));
        $this->setProductSelectionRule($this->getConditionsModel()->getConditions()->getData());
        return parent::_beforeSave();
    }

    /*
     *
     */
    public function callAfterLoad()
    {
        return $this->_afterLoad();
    }
}
