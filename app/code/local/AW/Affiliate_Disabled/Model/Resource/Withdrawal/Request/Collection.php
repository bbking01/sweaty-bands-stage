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


class AW_Affiliate_Model_Resource_Withdrawal_Request_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_isJoinedWithTransactions = false;
    protected $_isJoinedWithCustomer = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init('awaffiliate/withdrawal_request');
    }

    /**
     * @param $id
     * @return AW_Affiliate_Model_Resource_Transaction_Profit_Collection
     */
    public function addAffiliateFilter($id)
    {
        $this->addFieldToFilter('affiliate_id', array('eq' => $id));
        return $this;
    }

    public function addStatusFilter($status, $eq = true)
    {

        $this->addFieldToFilter('main_table.status', array($eq ? 'eq' : 'neq' => $status));
        return $this;
    }

    public function addNotPaidFilter()
    {
        return $this->addStatusFilter(AW_Affiliate_Model_Source_Withdrawal_Status::PAID, false);
    }

    public function addPendingStatusFilter()
    {

        return $this->addStatusFilter(AW_Affiliate_Model_Source_Withdrawal_Status::PENDING);
    }

    public function joinWithTransactions()
    {
        if ($this->_isJoinedWithTransactions) {
            return $this;
        }
        $transactionsTableName = $this->getTable('transaction_withdrawal');
        $this->getSelect()->
            joinLeft(array('transactions' => $transactionsTableName),
            'transactions.id = main_table.transaction_id',
            array(
                'transaction_created_at' => 'created_at',
                'transaction_description' => 'description',
                'transaction_notice' => 'notice',
                'transaction_amount' => 'amount',
            )
        );
        $this->_isJoinedWithTransactions = true;
        return $this;
    }

    public function addStoreFilter($stores = array())
    {
        if (!empty($stores)) {
            $websites = array();
            foreach ($stores as $storeId) {
                $store = Mage::app()->getSafeStore($storeId);
                if ($store) {
                    $websites[] = $store->getWebsiteId();
                }
            }
            $websites = array_unique($websites);
            $_sqlString = '(';
            $i = 0;
            $this->joinWithCustomer();
            foreach ($websites as $_store) {
                $_sqlString .= sprintf('find_in_set(%s, website_id)', $this->getConnection()->quote($_store));
                if (++$i < count($websites))
                    $_sqlString .= ' OR ';
            }
            $_sqlString .= ')';
            $this->getSelect()->where($_sqlString);
        }
    }

    public function joinWithCustomer()
    {
        if ($this->_isJoinedWithCustomer) {
            return $this;
        }
        $affiliateTableName = $this->getTable('affiliate');
        $this->getSelect()->
            joinLeft(array('affiliate' => $affiliateTableName),
            'affiliate.id = main_table.affiliate_id',
            array(
                'customer_id' => 'customer_id',
            )
        );

        $customerTableName = $this->getTable('customer/entity');
        $this->getSelect()->
            joinLeft(array('customer' => $customerTableName),
            'customer.entity_id = affiliate.customer_id',
            array(
                'website_id' => 'website_id',
            )
        );
        $this->_isJoinedWithCustomer = true;
        return $this;
    }
}
