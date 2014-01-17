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


class AW_Affiliate_Model_Profit_Tier_Rate extends Mage_Core_Model_Abstract
{
    const DEFAULT_RATE_AMOUNT = 0;

    public function _construct()
    {
        $this->_init('awaffiliate/profit_tier_rate');
    }

    public function loadByProfitId($profitId)
    {
        $_collection = $this->getCollection()
            ->addFieldToFilter('profit_id', array('eq' => $profitId))
            ->load();
        return $_collection;
    }

    public function isValidGroupId($groupId)
    {
        if ($this->getData('affiliate_group_id') == Mage_Customer_Model_Group::CUST_GROUP_ALL) {
            return true;
        }
        if ($this->getData('affiliate_group_id') != $groupId) {
            return false;
        }
        return true;
    }

    public function removeAllTiersByProfitId($profitId)
    {
        $_collection = $this->loadByProfitId($profitId);
        return $_collection->walk('delete');
    }
}
