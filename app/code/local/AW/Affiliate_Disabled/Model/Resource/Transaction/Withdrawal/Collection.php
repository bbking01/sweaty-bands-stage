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


class AW_Affiliate_Model_Resource_Transaction_Withdrawal_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected $_isJoinedWithRequests = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init('awaffiliate/transaction_withdrawal');
    }

    public function joinWithRequests()
    {
        if ($this->_isJoinedWithRequests) {
            return $this;
        }
        $requestsTableName = $this->getTable('withdrawal_request');
        $this->getSelect()->
            joinLeft(array('requests' => $requestsTableName),
            'requests.transaction_id = main_table.id',
            array(
                'affiliate_id' => 'affiliate_id',
                'request_created_at' => 'created_at',
                'request_description' => 'description',
                'request_notice' => 'notice',
                'request_amount' => 'amount',
                'status' => 'status',
            )
        );
        $this->_isJoinedWithRequests = true;
        return $this;
    }

    /**
     * @param $id
     * @return AW_Affiliate_Model_Resource_Transaction_Profit_Collection
     */
    public function addAffiliateFilter($id)
    {
        $this->joinWithRequests();
        $this->addFieldToFilter('affiliate_id', array('eq' => $id));
        return $this;
    }

    public function onlyPaid()
    {
        $this->addRequestStatusFilter(AW_Affiliate_Model_Source_Withdrawal_Status::PAID);
        return $this;
    }

    public function addRequestStatusFilter($status)
    {
        $this->joinWithRequests();
        $this->addFieldToFilter('status', array('eq' => $status));
        return $this;
    }
}
