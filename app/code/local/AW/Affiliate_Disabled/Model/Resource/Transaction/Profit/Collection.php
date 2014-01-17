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


class AW_Affiliate_Model_Resource_Transaction_Profit_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_isJoinedWithCampaigns = false;
    protected $_isEntitiesInfoAdded = false;
    protected $_isJoinedWithClient = false;
    protected $_isJoinedWithTrafficSource = false;

    public function _construct()
    {
        parent::_construct();
        $this->_init('awaffiliate/transaction_profit');
    }

    public function addAffiliateFilter($id)
    {
        $this->addFieldToFilter('main_table.affiliate_id', array('eq' => $id));
        return $this;
    }

    public function addTypeTransactionFilter($name)
    {
        $this->addFieldToFilter('main_table.type', array('eq' => $name));
        return $this;
    }

    public function addCampaignFilter($ids)
    {
        $this->addFieldToFilter('main_table.campaign_id', array('in' => $ids));
        return $this;
    }

    public function addTrafficSourceFilter($id)
    {
        $this->addFieldToFilter('traffic_id', array('eq' => $id));
        return $this;
    }

    public function addPeriodFilter($range)
    {
        if (!isset($range['from']) || !isset($range['to'])) {
            return $this;
        }
        $this->addFieldToFilter('main_table.created_at', array("from" => $range['from'], "to" => $range['to']));
        return $this;
    }

    public function joinWithCampaign()
    {
        if ($this->_isJoinedWithCampaigns) {
            return $this;
        }
        $campaignsTableName = $this->getTable('campaign');
        $this->getSelect()->
            joinLeft(array('campaign' => $campaignsTableName),
            'campaign.id = main_table.campaign_id',
            array(
                'campaign_name' => 'name',
            )
        );
        $this->_isJoinedWithRequests = true;
        return $this;
    }

    public function joinWithClient()
    {
        if ($this->_isJoinedWithClient) {
            return $this;
        }
        $clientTableName = $this->getTable('client');
        $this->getSelect()->
            join(array('client' => $clientTableName),
            'main_table.traffic_id=client.traffic_id AND main_table.client_id=client.id',
            array(
                'hits' => 'COUNT(client.id)',
            )
        );
        $this->_isJoinedWithClient = true;
        return $this;
    }

    public function joinWithTrafficSource()
    {
        if ($this->_isJoinedWithTrafficSource) {
            return $this;
        }
        $trafficSourceTableName = $this->getTable('traffic_source');
        $this->getSelect()->
            join(array('traffic' => $trafficSourceTableName),
            'traffic.id = main_table.traffic_id',
            array(
                'traffic_name' => 'traffic_name',
            )
        );
        $this->_isJoinedWithTrafficSource = true;
        return $this;
    }

    public function addEntitiesInfo()
    {
        if ($this->_isEntitiesInfoAdded) {
            return $this;
        }

        //join order_items info
        $ordersItemTable = $this->getTable('sales/order_item');
        $this->getSelect()->
            joinLeft(array('order_item' => $ordersItemTable),
            "main_table.linked_entity_type = '" . AW_Affiliate_Model_Source_Transaction_Profit_Linked::ORDER_ITEM
                . "' AND order_item.item_id = main_table.linked_entity_id",
            array(
                'order_item_sku' => 'sku',
                'order_item_name' => 'name',
                'order_item_id' => 'product_id'
            )
        );
    }

    public function setOrderAs($field, $spec = 'ASC')
    {
        $this->getSelect()->order($field . ' ' . $spec);
        return $this;
    }
}
