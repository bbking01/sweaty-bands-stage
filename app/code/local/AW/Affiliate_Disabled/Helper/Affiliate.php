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


class AW_Affiliate_Helper_Affiliate extends Mage_Core_Helper_Abstract
{
    const CAMPAIGN_REQUEST_KEY = 'cmid';
    const AFFILIATE_REQUEST_KEY = 'afid';
    const AFFILIATE_TRAFFIC_SOURCE = 'ats';

    public function getFullCustomerNameForAffiliate(AW_Affiliate_Model_Affiliate $affiliate)
    {
        return $affiliate->getFirstname() . ' ' . $affiliate->getLastname() . ' <' . $affiliate->getEmail() . '>';
    }

    /*
        return total amount for affiliate in last month
        if $campaignId defined then return total amount in last month for this campaign
    */
    public function getLastMonthAmountForAffiliate(AW_Affiliate_Model_Affiliate $affiliate, $campaignId = null)
    {
        //calculate date
        $startMonthAgo = new Zend_Date();
        $startMonthAgo->setDay(1);
        $startMonthAgo->setHour(0);
        $startMonthAgo->setMinute(0);
        $startMonthAgo->setSecond(0);
        $endMonthAgo = clone $startMonthAgo;
        $endMonthAgo->subSecond(1);
        $startMonthAgo->subMonth(1);
        //create select
        /** @var $totalAffiliated AW_Affiliate_Model_Resource_Transaction_Profit_Collection */
        $totalAffiliated = Mage::getModel('awaffiliate/transaction_profit')->getCollection();

        $totalAffiliated->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $totalAffiliated->addExpressionFieldToSelect('last_month_amount', 'SUM({{attracted_amount}})', 'attracted_amount')
            ->addAffiliateFilter($affiliate->getId())
            ->addFieldToFilter('created_at', array('from' => $startMonthAgo->toString('YYYY-MM-dd HH:mm:ss'),
            'to' => $endMonthAgo->toString('YYYY-MM-dd HH:mm:ss')));

        if (!is_null($campaignId)) {
            $totalAffiliated->addFieldToFilter('campaign_id', $campaignId);
        }
        $result = $totalAffiliated->getData();
        $lastMonthAmount = 0;
        if (array_key_exists(0, $result) && !is_null($result[0]['last_month_amount'])) {
            $lastMonthAmount = $result[0]['last_month_amount'];
        }
        return $lastMonthAmount;
    }

    /*
        return total attracted amount for affiliate
        if $campaignId defined then return total amount for this campaign
    */
    public function getTotalAmountForAffiliate(AW_Affiliate_Model_Affiliate $affiliate, $campaignId = null)
    {
        //create select
        $totalAffiliated = Mage::getModel('awaffiliate/transaction_profit')->getCollection();

        $totalAffiliated->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $totalAffiliated->addExpressionFieldToSelect('attracted_amount', 'SUM({{attracted_amount}})', 'attracted_amount')
            ->addAffiliateFilter($affiliate->getId());

        if (!is_null($campaignId)) {
            $totalAffiliated->addFieldToFilter('campaign_id', $campaignId);
        }
        $result = $totalAffiliated->getData();
        $lastMonthAmount = 0;
        if (array_key_exists(0, $result) && !is_null($result[0]['attracted_amount'])) {
            $lastMonthAmount = $result[0]['attracted_amount'];
        }
        return $lastMonthAmount;
    }

    /*
        return total amount for affiliate
        if $campaignId defined then return total amount for this campaign
    */
    public function getAmountForAffiliate(AW_Affiliate_Model_Affiliate $affiliate, $campaignId = null)
    {
        //create select
        $totalAffiliated = Mage::getModel('awaffiliate/transaction_profit')->getCollection();
        $totalAffiliated->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $totalAffiliated
            ->addExpressionFieldToSelect('amount', 'SUM({{amount}})', 'amount')
            ->addAffiliateFilter($affiliate->getId());

        if (!is_null($campaignId)) {
            $totalAffiliated->addFieldToFilter('campaign_id', $campaignId);
        }
        $result = $totalAffiliated->getData();
        $lastMonthAmount = 0;
        if (array_key_exists(0, $result) && !is_null($result[0]['amount'])) {
            $lastMonthAmount = $result[0]['amount'];
        }
        return $lastMonthAmount;
    }

    /*
      check amount for withdrawal request
      it is true if affiliate has not requested amount which less $amount
     */
    public function isWithdrawalRequestAvailableOn(AW_Affiliate_Model_Affiliate $affiliate, $amount)
    {
        $totalRequested = $this->getTotalRequested($affiliate, false);
        $currentBalance = $affiliate->getActiveBalance();
        if ($currentBalance >= ($totalRequested + $amount)) {
            return true;
        }
        return false;
    }

    /*
        get total withdrawal requested amount
        if $inclPaid is false then amount is not contain paid requests
    */
    public function getTotalRequested(AW_Affiliate_Model_Affiliate $affiliate, $inclPaid = true)
    {
        $_totalRequested = 0;
        $withdrawalRequestCollection = $affiliate->getWithdrawalRequests();
        if (!$inclPaid) {
            $withdrawalRequestCollection->addNotPaidFilter();
            //recollect collection
            $affiliate->getWithdrawalRequests(true);
        }
        foreach ($withdrawalRequestCollection as $transaction) {
            if ($transaction['status'] == AW_Affiliate_Model_Source_Withdrawal_Status::PENDING) {
                $_totalRequested += $transaction->getAmount();
            }
        }
        return $_totalRequested;
    }

    /* count of pending withdrawals requests*/
    public function getPendingWithdrawalRequestsSize(AW_Affiliate_Model_Affiliate $affiliate)
    {
        $withdrawalRequestCollection = $affiliate->getWithdrawalRequests();
        //recollect collection
        $affiliate->getWithdrawalRequests(true);
        $withdrawalRequestCollection->addPendingStatusFilter();
        return $withdrawalRequestCollection->getSize();
    }

    public function getLastWithdrawalRequestDetails(AW_Affiliate_Model_Affiliate $affiliate)
    {
        $withdrawalRequests = $affiliate->getWithdrawalRequests();
        //recollect collection
        $affiliate->getWithdrawalRequests(true);
        $withdrawalDetails = $withdrawalRequests->getLastItem()->getDescription();
        return $withdrawalDetails;
    }

    /*
     * [scheme]://[user]:[password]@[host]:[port][path]?[query]#[fragment]
     */
    public function generateAffiliateLink($baseUrl, $params)
    {
        if (!array_key_exists(self::CAMPAIGN_REQUEST_KEY, $params) || !array_key_exists(self::AFFILIATE_REQUEST_KEY, $params)) {
            return null;
        }
        $cmid = Mage::helper('core')->encrypt($params[self::CAMPAIGN_REQUEST_KEY]);
        $afid = Mage::helper('core')->encrypt($params[self::AFFILIATE_REQUEST_KEY]);
        $ats = Mage::helper('core')->encrypt($params[self::AFFILIATE_TRAFFIC_SOURCE]);

        $url = $baseUrl . ((false === strpos($baseUrl, '?')) ? '?' : '&');
        $queryParams = array(
            self::CAMPAIGN_REQUEST_KEY . '=' . Mage::helper('core')->urlEncode($cmid),
            self::AFFILIATE_REQUEST_KEY . '=' . Mage::helper('core')->urlEncode($afid),
            self::AFFILIATE_TRAFFIC_SOURCE . '=' . Mage::helper('core')->urlEncode($ats)
        );
        $url .= implode('&', $queryParams);
        return $url;
    }
}
