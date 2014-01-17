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


class AW_Affiliate_Model_Affiliate extends Mage_Core_Model_Abstract
{
    protected $_profitTransactions = null;
    protected $_withdrawalTransactions = null;
    protected $_withdrawalRequests = null;
    protected $_wlProfitTransactions = null;
    protected $_wlWithdrawalTransactions = null;

    protected $_totalAffiliated = null;
    protected $_totalWithdrawn = null;
    protected $_wlTotalWithdrawn = null;
    protected $_wlTotalAffiliated = null;
    protected $_totalRequested = null;
    protected $_customer = null;

    public function _construct()
    {
        $this->_init('awaffiliate/affiliate');
    }

    public function getWLTotalAffiliated($recollect = false)
    {
        if ($this->_wlTotalAffiliated === null || $recollect) {
            $this->_wlTotalAffiliated = 0;
            $baseCurrency = Mage::helper('awaffiliate')->getDefaultCurrencyCode();
            foreach ($this->getWLProfitTransactions($recollect) as $transaction) {
                if ($baseCurrency != $transaction->getData('currency_code')) {
                    try {
                        $currency = Mage::getModel('directory/currency')->load($transaction->getData('currency_code'));
                        $amount = $currency->convert($transaction->getAmount(), $baseCurrency);
                    } catch (Exception $e) {
                        //TODO: log
                        $amount = $transaction->getAmount();
                    }
                } else {
                    $amount = $transaction->getAmount();
                }
                $this->_wlTotalAffiliated += $amount;
            }
        }
        return $this->_wlTotalAffiliated;
    }

    public function getTotalAffiliated($recollect = false)
    {
        if (is_null($this->_totalAffiliated) || $recollect) {
            $this->_totalAffiliated = 0;
            $baseCurrency = Mage::helper('awaffiliate')->getDefaultCurrencyCode();
            foreach ($this->getProfitTransactions($recollect) as $transaction) {
                if ($baseCurrency != $transaction->getData('currency_code')) {
                    try {
                        $currency = Mage::getModel('directory/currency')->load($transaction->getData('currency_code'));
                        $amount = $currency->convert($transaction->getAmount(), $baseCurrency);
                    } catch (Exception $e) {
                        //TODO: log
                        $amount = $transaction->getAmount();
                    }
                } else {
                    $amount = $transaction->getAmount();
                }
                $this->_totalAffiliated += $amount;
            }
        }
        return $this->_totalAffiliated;
    }

    public function getWLTotalWithdrawn($recollect = false)
    {
        if ($this->_wlTotalWithdrawn === null || $recollect) {
            $this->_wlTotalWithdrawn = 0;
            $collection = $this->getWLWithdrawalTransactions($recollect);
            $collection->onlyPaid();
            foreach ($collection as $transaction) {
                $this->_wlTotalWithdrawn += $transaction->getData('amount');
            }
            $this->getWLWithdrawalTransactions(true);
        }
        return $this->_wlTotalWithdrawn;
    }

    public function getTotalWithdrawn($recollect = false)
    {
        if (is_null($this->_totalWithdrawn) || $recollect) {
            $this->_totalWithdrawn = 0;
            $collection = $this->getWithdrawalTransactions($recollect);
            $collection->onlyPaid();
            foreach ($collection as $transaction) {
                $this->_totalWithdrawn += $transaction->getAmount();
            }
            //recollect collection
            $this->getWithdrawalTransactions(true);
        }
        return $this->_totalWithdrawn;
    }

    public function getWLProfitTransactions($recollect = false)
    {
        if ($this->_wlProfitTransactions === null || $recollect) {
            $collection = $this->_wlProfitTransactions = Mage::getModel('awaffiliate/transaction_profit')->getCollection();
            $collection->addAffiliateFilter($this->getId());
            $minimumWithdrawalPeriod = intval(Mage::helper('awaffiliate/config')->getMinimumWithdrawalPeriod());
            if ($minimumWithdrawalPeriod) {
                $collection->addFieldToFilter('created_at',
                    array('gteq' => new Zend_Db_Expr("SUBDATE(NOW(), INTERVAL {$minimumWithdrawalPeriod} DAY)"))
                );
            }
        }
        return $this->_wlProfitTransactions;
    }

    public function getWLWithdrawalTransactions($recollect = false)
    {
        if ($this->_wlWithdrawalTransactions === null || $recollect) {
            $collection = $this->_wlWithdrawalTransactions = Mage::getModel('awaffiliate/transaction_withdrawal')->getCollection();
            $collection->addAffiliateFilter($this->getId());
            $minimumWithdrawalPeriod = intval(Mage::helper('awaffiliate/config')->getMinimumWithdrawalPeriod());
            if ($minimumWithdrawalPeriod) {
                $collection->addFieldToFilter('main_table.created_at',
                    array('gteq' => new Zend_Db_Expr("SUBDATE(NOW(), INTERVAL {$minimumWithdrawalPeriod} DAY)"))
                );
            }
        }
        return $this->_wlWithdrawalTransactions;
    }

    public function getProfitTransactions($recollect = false)
    {
        if (is_null($this->_profitTransactions) || $recollect) {
            $__affiliateId = $this->getId();
            $this->_profitTransactions = Mage::getModel('awaffiliate/transaction_profit')->getCollection();
            $this->_profitTransactions->addAffiliateFilter($__affiliateId);
        }
        return $this->_profitTransactions;
    }

    public function getWithdrawalTransactions($recollect = false)
    {
        if (is_null($this->_withdrawalTransactions) || $recollect) {
            $__affiliateId = $this->getId();
            $this->_withdrawalTransactions = Mage::getModel('awaffiliate/transaction_withdrawal')->getCollection();
            $this->_withdrawalTransactions->addAffiliateFilter($__affiliateId);
        }
        return $this->_withdrawalTransactions;
    }

    public function getWithdrawalRequests($recollect = false)
    {
        if (is_null($this->_withdrawalRequests) || $recollect) {
            $__affiliateId = $this->getId();
            $this->_withdrawalRequests = Mage::getModel('awaffiliate/withdrawal_request')->getCollection();
            $this->_withdrawalRequests->addAffiliateFilter($__affiliateId);
        }
        return $this->_withdrawalRequests;
    }

    public function recollectBalances()
    {
        $currentBalance = $this->getTotalAffiliated(true) - $this->getTotalWithdrawn(true);
        $activeBalance = $this->getWLTotalAffiliated(true) - $this->getWLTotalWithdrawn(true);
        if ($this->getData('current_balance') != $currentBalance) {
            $this->setData('current_balance', $currentBalance);
        }
        if ($this->getData('active_balance') != $activeBalance) {
            $this->setData('active_balance', $activeBalance);
        }
        if ($this->hasDataChanges()) {
            $this->save();
        }
        return $this;
    }

    public function loadByCustomerId($customerId)
    {
        $this->load($customerId, 'customer_id');
        return $this;
    }

    public function getCustomer() {
        if(is_null($this->_customer)&& !is_null($this->getData('customer_id'))) {
            $this->_customer = Mage::getModel('customer/customer')->load($this->getData('customer_id'));
        }
        return $this->_customer;

    }
    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        $__customerId = $this->getData('customer_id');
        $customer = Mage::getModel('customer/customer')->load($__customerId);
        if (!is_null($customer->getId())) {
            $data = array(
                'firstname' => $customer->getFirstname(),
                'lastname' => $customer->getLastname(),
                'email' => $customer->getEmail(),
                'customer_group_id' => $customer->getGroupId()
            );
            $this->addData($data);
        }
        return parent::_afterLoad();
    }
}
