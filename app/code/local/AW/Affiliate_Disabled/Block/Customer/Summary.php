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


class AW_Affiliate_Block_Customer_Summary extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $_template = 'aw_affiliate/customer/summary.phtml';
        $this->setTemplate($_template);
        return $this;
    }

    public function getCurrentBalance()
    {
        $currentBalance = Mage::registry('current_affiliate')->getCurrentBalance();
        return Mage::helper('core')->formatCurrency($currentBalance);
    }

    public function getActiveBalance()
    {
        $requestWithdrawal = Mage::getModel('awaffiliate/withdrawal_request')->getCollection();
        $requestWithdrawal->addFieldToFilter('status', array('eq' => AW_Affiliate_Model_Source_Withdrawal_Status::PENDING));
        $requestWithdrawal->addAffiliateFilter(Mage::registry('current_affiliate')->getId());
        $activeBalance = Mage::registry('current_affiliate')->getActiveBalance();
        foreach ($requestWithdrawal as $item) {
            $activeBalance -= $item->getAmount();
        }
        return Mage::helper('core')->formatCurrency($activeBalance);

    }

    public function getTotalAffiliated()
    {
        $totalAffiliated = Mage::registry('current_affiliate')->getTotalAffiliated();
        return Mage::helper('core')->formatCurrency($totalAffiliated);
    }

    public function getAffiliatedLastMonth()
    {
        $_helper = Mage::helper('awaffiliate/affiliate');
        $affiliatedInLastMonth = $_helper->getLastMonthAmountForAffiliate(Mage::registry('current_affiliate'));
        return Mage::helper('core')->formatCurrency($affiliatedInLastMonth);
    }
}
