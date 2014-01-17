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


class AW_Affiliate_Block_Report_View_Traffic extends AW_Affiliate_Block_Report_View_Abstract
{
    protected function _beforeToHtml()
    {
        $this->setTemplate('aw_affiliate/report/view/traffic.phtml');
        return parent::_beforeToHtml();
    }

    protected function _collectData($from, $to)
    {
        /** @var $trafficSourceCollection AW_Affiliate_Model_Resource_Traffic_Source_Collection */
        $trafficSourceCollection = Mage::getModel('awaffiliate/traffic_source')->getCollection();
        $trafficSourceCollection->addAffiliateFilter($this->_getCurrentAffiliate()->getId());

        /** @var $resource Mage_Core_Model_Resource */
        $resource = Mage::getSingleton('core/resource');
        /** @var $connection Varien_Db_Adapter_Pdo_Mysql */
        $connection = $resource->getConnection('core_read');

        $betweenCondition = " AND created_at BETWEEN {$connection->quote($from)} AND {$connection->quote($to)}";
        $campaignsCondition = " AND campaign_id IN(" . implode(',', $this->getCampaignIds()) . ')';

        $hitsSelect = new Zend_Db_Select($connection);
        $hitsSelect->from($resource->getTableName('awaffiliate/client'), array('hits' => 'count(*)'));
        $hitsSelect->where("`traffic_id`=`main_table`.`id`" . $campaignsCondition . $betweenCondition);

        $salesSelect = new Zend_Db_Select($connection);
        $salesSelect->from($resource->getTableName('awaffiliate/transaction_profit'), array('sales' => 'count(*)'));
        $salesSelect->where("`traffic_id`=`main_table`.`id`" . $campaignsCondition . $betweenCondition);

        $profitSelect = new Zend_Db_Select($connection);
        $profitSelect->from($resource->getTableName('awaffiliate/transaction_profit'), array('profit' => 'sum(`amount`)'));
        $profitSelect->where("`traffic_id`=`main_table`.`id`" . $campaignsCondition . $betweenCondition);

        $trafficSourceCollection->addFieldToSelect('traffic_name', 'traffic_name')
            ->addFieldToSelect(new Zend_Db_Expr("(" . $hitsSelect . ")"), 'hits')
            ->addFieldToSelect(new Zend_Db_Expr("(" . $salesSelect . ")"), 'sales')
            ->addFieldToSelect(new Zend_Db_Expr("(" . $profitSelect . ")"), 'profit')
            ->addOrder('hits', 'DESC');

        $trafficSourceCollection->getSelect()->group('main_table.id');
        $trafficSourceCollection->getSelect()->having("hits >0 or sales > 0");

        $items = array();
        foreach ($trafficSourceCollection as $item) {
            $item = array(
                'ts_name' => $this->_getTSName($item),
                'hits' => $item->getData('hits'),
                'sales' => $item->getData('sales'),
                'profit' => $this->_formatCurrency($item->getData('profit'))
            );
            $this->_postProcess($item);
            $items[] = $item;
        }

        return $items;
    }

    protected function _getCR($sales, $hits)
    {
        return $hits ? $this->_formatPercent($sales / $hits) : $this->__('-');
    }

    protected function _postProcess(&$item)
    {
        $item['cr'] = $this->_getCR($item['sales'], $item['hits']);
        return $this;
    }

    protected function _getTSName($item)
    {
        return ($trafficName = $item->getData('traffic_name')) ? $trafficName : $this->__('(Empty)');
    }

    protected function _collectTotals()
    {
        $totals = array(
            'hits' => 0,
            'sales' => 0,
            'cr' => 0,
            'profit' => 0
        );
        foreach ($this->getItems() as $item) {
            $totals['hits'] += $item['hits'];
            $totals['sales'] += $item['sales'];
            $totals['profit'] += $item['profit'];
        }
        $totals['cr'] = $this->_getCR($totals['sales'], $totals['hits']);
        $totals['profit'] = $this->_formatCurrency($totals['profit']);
        return $totals;
    }

    protected function _getReportType()
    {
        return AW_Affiliate_Model_Source_Report_Type::TRAFFIC;
    }

    protected function _saveItemsInSession()
    {
        $items = array();
        foreach ($this->_items as $item) {
            $items[] = array(
                $this->__('Traffic Source') => $item['ts_name'],
                $this->__('Hits') => $item['hits'],
                $this->__('Sales') => $item['sales'],
                $this->__('CR') => $item['cr'],
                $this->__('Profit') => $item['profit']
            );
        }
        Mage::getSingleton('customer/session')->setAffiliateGridForDownload($items);
        return $this;
    }
}
