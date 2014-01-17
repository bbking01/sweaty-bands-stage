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


class AW_Affiliate_Block_Report_View_Sales extends AW_Affiliate_Block_Report_View_Abstract
{
    protected function _beforeToHtml()
    {
        $this->setTemplate('aw_affiliate/report/view/sales.phtml');
        return parent::_beforeToHtml();
    }

    protected function _collectData($from, $to)
    {
        $_salesAndProfit = $this->_getSalesAndProfit($from, $to);
        $item = array(
            'date' => $this->_formatDate($from),
            'hits' => $this->_getHits($from, $to),
            'sales' => $_salesAndProfit['sales'],
            'cr' => 0,
            'profit' => $this->_formatCurrency($_salesAndProfit['profit'])
        );
        $this->_postProcess($item);
        return array($item);
    }

    protected function _getHits($from, $to)
    {
        /** @var $collection AW_Affiliate_Model_Resource_Client_Collection */
        $collection = Mage::getModel('awaffiliate/client')->getCollection();
        $this->_applyDefaultFilters($collection, $from, $to);
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->addExpressionFieldToSelect('sales', 'COUNT(id)', array());
        return $collection->getConnection()->fetchOne($collection->getSelect());
    }

    protected function _getSalesAndProfit($from, $to)
    {
        /** @var $collection AW_Affiliate_Model_Resource_Transaction_Profit_Collection */
        $collection = Mage::getModel('awaffiliate/transaction_profit')->getCollection();
        $this->_applyDefaultFilters($collection, $from, $to);
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->addExpressionFieldToSelect('sales', 'COUNT(id)', array());
        $collection->addExpressionFieldToSelect('profit', 'SUM(amount)', array());
        //TODO Group By linked_entity_id
        return $collection->getConnection()->fetchRow($collection->getSelect());
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

    protected function _collectTotals()
    {
        $totals = array(
            'hits' => 0,
            'sales' => 0,
            'cr' => 0,
            'profit' => 0
        );
        foreach ($this->_items as $item) {
            $totals['hits'] += $item['hits'];
            $totals['sales'] += $item['sales'];
            $totals['profit'] += $item['profit'];
        }
        $totals['cr'] = $this->_getCR($totals['sales'], $totals['hits']);
        $totals['profit'] = $this->_formatCurrency($totals['profit']);
        return $totals;
    }

    protected function _saveItemsInSession()
    {
        $items = array();
        foreach ($this->_items as $item) {
            $items[] = array(
                $this->__('Date') => $item['date'],
                $this->__('Hits') => $item['hits'],
                $this->__('Sales') => $item['sales'],
                $this->__('CR') => $item['cr'],
                $this->__('Profit') => $item['profit']
            );
        }
        Mage::getSingleton('customer/session')->setAffiliateGridForDownload($items);
        return $this;
    }

    protected function _getReportType()
    {
        return AW_Affiliate_Model_Source_Report_Type::SALES;
    }
}
