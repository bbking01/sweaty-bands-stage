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


class AW_Affiliate_Block_Report_View_Transactions extends AW_Affiliate_Block_Report_View_Abstract
{
    protected function _beforeToHtml()
    {
        $this->setTemplate('aw_affiliate/report/view/transactions.phtml');
        return parent::_beforeToHtml();
    }

    protected function _collectData($from, $to)
    {
        /** @var $collection AW_Affiliate_Model_Resource_Transaction_Profit_Collection */
        $collection = Mage::getModel('awaffiliate/transaction_profit')->getCollection();
        $this->_applyDefaultFilters($collection, $from, $to);
        $collection->joinWithCampaign()
            ->joinWithTrafficSource()
            ->setOrder('id', Varien_Data_Collection::SORT_ORDER_ASC);
        $items = array();
        foreach ($collection as $item) {
            /** @var $item AW_Affiliate_Model_Transaction_Profit */
            $items[] = array(
                'c_id' => $item->getId(),
                'campaign_name' => $item->getData('campaign_name'),
                'ts_name' => $this->_getTSName($item),
                'date' => $this->formatTime($item->getData('created_at'), Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true),
                'profit' => $this->_formatCurrency($item->getData('amount'))
            );
        }
        return $items;
    }

    protected function _getTSName($item)
    {
        return ($trafficName = $item->getData('traffic_name')) ? $trafficName : $this->__('(Empty)');
    }

    protected function _collectTotals()
    {
        $totals = array(
            'profit' => 0
        );
        foreach ($this->_items as $item) {
            $totals['profit'] += $item['profit'];
        }
        $totals['profit'] = $this->_formatCurrency($totals['profit']);
        return $totals;
    }

    protected function _saveItemsInSession()
    {
        $items = array();
        foreach ($this->_items as $item) {
            $items[] = array(
                $this->__('Conversion ID') => $item['c_id'],
                $this->__('Campaign') => $item['campaign_name'],
                $this->__('Traffic Source') => $item['ts_name'],
                $this->__('Date') => $item['date'],
                $this->__('Profit') => $item['profit']
            );
        }
        Mage::getSingleton('customer/session')->setAffiliateGridForDownload($items);
        return $this;
    }

    protected function _getReportType()
    {
        return AW_Affiliate_Model_Source_Report_Type::TRANSACTIONS;
    }
}
