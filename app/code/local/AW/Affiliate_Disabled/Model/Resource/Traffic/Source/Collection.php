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


class AW_Affiliate_Model_Resource_Traffic_Source_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_isJoinedWithClient = false;
    protected $_isJoinedWithTransactionProfit = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init('awaffiliate/traffic_source');
    }

    public function addAffiliateFilter($id)
    {
        $this->addFieldToFilter('main_table.affiliate_id', array('eq' => $id));
        return $this;
    }

    public function addCampaignFilter($ids)
    {
        $this->addFieldToFilter('campaign_id', array('in' => $ids));
        return $this;
    }

    public function addPeriodFilter($range)
    {
        if (!isset($range['from']) || !isset($range['to'])) {
            return $this;
        }
        $this->addFieldToFilter('created_at', array("from" => $range['from'], "to" => $range['to']));
        return $this;
    }

    public function joinWithTransactionProfit()
    {
        if ($this->_isJoinedWithTransactionProfit) {
            return $this;
        }
        $clientTableName = $this->getTable('transaction_profit');
        $this->getSelect()->
            joinLeft(array('transaction_profit' => $clientTableName),
            'transaction_profit.traffic_id = main_table.id',
            array(
                'sales' => 'COUNT(transaction_profit.id)',
                'profit' => 'SUM(amount)'

            )
        );
        $this->_isJoinedTransactionProfit = true;
        return $this;
    }
}
