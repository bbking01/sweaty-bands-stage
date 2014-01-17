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


/**
 * @method string getDatePeriod
 * @method string getDetalization
 * @method string getPeriodFrom
 * @method string getPeriodTo
 * @method string getType
 * @method string setDetalization
 */

abstract class AW_Affiliate_Block_Report_View_Abstract extends Mage_Core_Block_Template
{
    const MAX_RANGES_IN_REPORT = 60;

    protected $_items = null;
    protected $_totals = null;
    protected $_campaigns = null;
    protected $_currentAffiliate = null;
    protected $_ranges = array();

    public function getItems()
    {
        if ($this->_items === null) {
            $this->_items = array();
            foreach ($this->_getRanges() as $range) {
                $this->_items = array_merge($this->_items, $this->_collectData($range['_from'], $range['_to']));
            }
            if (count($this->_items) > 1) {
                $this->_totals = $this->_collectTotals();
            }
            $this->_saveItemsInSession();
        }
        return $this->_items;
    }

    protected function _getRange()
    {
        $periodFrom = $periodTo = $this->_date();
        switch ($this->getDatePeriod()) {
            case AW_Affiliate_Model_Source_Report_Period::TODAY:
                $periodFrom = $this->_date()->setTime(0);
                $periodTo = $this->_date()->addDay(1)->setTime(0)->subSecond(1);
                break;
            case AW_Affiliate_Model_Source_Report_Period::YESTERDAY:
                $periodFrom = $this->_date()->subDay(1)->setTime(0);
                $periodTo = $this->_date()->setTime(0)->subSecond(1);
                break;
            case AW_Affiliate_Model_Source_Report_Period::LAST_SEVEN_DAYS:
                $periodFrom = $this->_date()->subDay(6)->setTime(0);
                $periodTo = $this->_date()->addDay(1)->setTime(0)->subSecond(1);
                break;
            case AW_Affiliate_Model_Source_Report_Period::THIS_MONTH:
                $periodFrom = $this->_date()->setDay(1)->setTime(0);
                $periodTo = $this->_date()->addDay(1)->setTime(0)->subSecond(1);
                break;
            case AW_Affiliate_Model_Source_Report_Period::ALL_TIME:
                $range = $this->_getPeriodForCurrentAffiliate();
                if ($range) {
                    list($_from, $_to) = $range;
                    $periodFrom = $this->_date()->setDate($_from, Varien_Date::DATETIME_INTERNAL_FORMAT)->setTime(0);
                    $periodTo = $this->_date()->setDate($_to, Varien_Date::DATETIME_INTERNAL_FORMAT)->addDay(1)->setTime(0)->subSecond(1);
                    unset($_from, $_to);
                }
                break;
            case AW_Affiliate_Model_Source_Report_Period::CUSTOM_PERIOD:
                $_from = $this->getPeriodFrom();
                $_to = $this->getPeriodTo();
                if ($_from && $_to) {
                    try {
                        $_from = $this->_dateFromCalendar($_from)->setTime(0);
                        $_to = $this->_dateFromCalendar($_to)->setTime('23:59:59');
                        $periodFrom = $_from;
                        $periodTo = $_to;
                    } catch (Exception $ex) {
                    }
                }
                break;
        }
        return array($periodFrom, $periodTo);
    }

    protected function _getRanges()
    {
        /** @var $periodFrom Zend_Date */
        /** @var $periodTo Zend_Date */
        list($periodFrom, $periodTo) = $this->_getRange();
        $this->_ranges = array();
        $currentDate = new Zend_Date();
        $currentDate->setTimestamp($this->_date()->getTimestamp());
        // Is periodFrom in future? No transaction found.
        if ($periodFrom->compare($currentDate) < 1) {
            $currentPeriod = new Zend_Date();
            $currentPeriod->setTimestamp($periodFrom->getTimestamp());
            if ($this->_getReportType() != AW_Affiliate_Model_Source_Report_Type::SALES) {
                $this->setDetalization(null);
            }
            switch ($this->getDetalization()) {
                case AW_Affiliate_Model_Source_Report_Detalization::DAY:
                    while (($currentPeriod->compare($periodTo) < 1) && ($currentPeriod->compare($currentDate) < 1)) {
                        $_from = $this->_dateToString($currentPeriod);
                        $_to = $this->_dateToString($currentPeriod->addDay(1)->subSecond(1));
                        $currentPeriod->addSecond(1);
                        if (!$this->_addRange($_from, $_to)) {
                            break;
                        }
                    }
                    unset($_from, $_to);
                    break;
                case AW_Affiliate_Model_Source_Report_Detalization::MONTH:
                    while ($currentPeriod->getYear()->compare($periodTo->getYear()) < 1) {
                        $_firstDayOfMonth = clone $currentPeriod;
                        $_firstDayOfMonth->setDay(1);
                        if ($_firstDayOfMonth->compare($periodFrom) < 0) {
                            $_firstDayOfMonth = clone $periodFrom;
                        }
                        $_lastDayOfMonth = clone $currentPeriod;
                        $_lastDayOfMonth->addMonth(1)->setDay(1)->subSecond(1);
                        if ($_lastDayOfMonth->compare($periodTo) > 0) {
                            $_lastDayOfMonth = clone $periodTo;
                        }
                        $currentPeriod->addMonth(1);
                        if (!$this->_addRange($_firstDayOfMonth, $_lastDayOfMonth)) {
                            break;
                        }
                        // Break if currentPeriod > periodTo || currentPeriod > currentDate
                        if ((($currentPeriod->getYear()->compare($periodTo->getYear()) == 0)
                            && ($currentPeriod->getMonth()->compare($periodTo->getMonth()) == 1)
                            || ($currentPeriod->compare($currentDate) == 1))
                        ) {
                            break;
                        }
                    }
                    unset($_firstDayOfMonth, $_lastDayOfMonth);
                    break;
                case AW_Affiliate_Model_Source_Report_Detalization::YEAR:
                    while (($currentPeriod->getYear()->compare($periodTo->getYear()) < 1)
                        && ($currentPeriod->getYear()->compare($currentDate->getYear()) < 1)) {
                        $_firstDayOfYear = clone $currentPeriod;
                        $_firstDayOfYear->setMonth(1)->setDay(1);
                        if ($_firstDayOfYear->compare($periodFrom) < 0) {
                            $_firstDayOfYear = clone $periodFrom;
                        }
                        $_lastDayOfYear = clone $currentPeriod;
                        $_lastDayOfYear->setMonth(12)->setDay(31)->setTime('23:59:59');
                        if ($_lastDayOfYear->compare($periodTo) > 0) {
                            $_lastDayOfYear = clone $periodTo;
                        }
                        if (!$this->_addRange($_firstDayOfYear, $_lastDayOfYear)) {
                            break;
                        }
                        $currentPeriod->addYear(1);
                    }
                    unset($_firstDayOfYear, $_lastDayOfYear);
                    break;
                default:
                    $this->_addRange($periodFrom, $periodTo);
                    break;
            }
        }
        return $this->_ranges;
    }

    protected function _addRange($from, $to)
    {
        if (count($this->_ranges) >= self::MAX_RANGES_IN_REPORT) {
            $this->setMaxRangesLimitExceeded();
            return false;
        } else {
            $this->_ranges[] = array(
                '_from' => ($from instanceof Zend_Date) ? $this->_dateToString($from) : $from,
                '_to' => ($to instanceof Zend_Date) ? $this->_dateToString($to) : $to
            );
            return true;
        }
    }

    /**
     * @return AW_Affiliate_Model_Affiliate
     */
    protected function _getCurrentAffiliate()
    {
        if ($this->_currentAffiliate === null) {
            $this->_currentAffiliate = Mage::registry('current_affiliate');
        }
        return $this->_currentAffiliate;
    }

    protected function _dateFromCalendar($date)
    {
        $zendDate = new Zend_Date();
        $zendDate->setDate($date, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
        return $zendDate;
    }

    /**
     * @return Zend_Date
     */
    protected function _date($date = null)
    {
        return Mage::app()->getLocale()->date($date);
    }

    protected function _getPeriodForCurrentAffiliate()
    {
        /** @var $profitsCollection AW_Affiliate_Model_Resource_Transaction_Profit_Collection */
        $profitsCollection = Mage::getModel('awaffiliate/transaction_profit')->getCollection();
        $profitsCollection->addAffiliateFilter($this->_getCurrentAffiliate()->getId())
            ->addCampaignFilter($this->getCampaignIds())
            ->setOrder('created_at', Varien_Data_Collection_Db::SORT_ORDER_ASC);
        if ($profitsCollection->getSize()) {
            $profitsFirstItem = $profitsCollection->getFirstItem();
            $profitsLastItem = $profitsCollection->getLastItem();
        }
        /** @var $hitsCollection AW_Affiliate_Model_Resource_Client_Collection */
        $hitsCollection = Mage::getModel('awaffiliate/client')->getCollection();
        $hitsCollection->addAffiliateFilter($this->_getCurrentAffiliate()->getId())
            ->addCampaignFilter($this->getCampaignIds())
            ->setOrder('created_at', Varien_Data_Collection_Db::SORT_ORDER_ASC);
        if ($hitsCollection->getSize()) {
            $hitsFirstItem = $hitsCollection->getFirstItem();
            $hitsLastItem = $hitsCollection->getLastItem();
        }
        if (!$profitsCollection->getSize() && !$hitsCollection->getSize()) {
            return null;
        }
        $_profitsFrom = isset($profitsFirstItem) ? $profitsFirstItem->getData('created_at') : null;
        $_profitsTo = isset($profitsLastItem) ? $profitsLastItem->getData('created_at') : null;
        $_hitsFrom = isset($hitsFirstItem) ? $hitsFirstItem->getData('created_at') : null;
        $_hitsTo = isset($hitsLastItem) ? $hitsLastItem->getData('created_at') : null;
        $from = $_profitsFrom ? $_profitsFrom : $_hitsFrom;
        $to = $_profitsTo ? $_profitsTo : $_hitsTo;
        if ($_profitsFrom && $_hitsFrom) {
            $from = strtotime($_profitsFrom) < strtotime($_hitsFrom) ? $_profitsFrom : $_hitsFrom;
        }
        if ($_profitsTo && $_hitsTo) {
            $to = strtotime($_profitsTo) > strtotime($_hitsTo) ? $_profitsTo : $_hitsTo;
        }
        return array($from, $to);
    }

    protected function _formatDate($date)
    {
        switch ($this->getDetalization()) {
            case AW_Affiliate_Model_Source_Report_Detalization::DAY:
                return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
                break;
            case AW_Affiliate_Model_Source_Report_Detalization::MONTH:
                return date('m/Y', strtotime($date));
                break;
            case AW_Affiliate_Model_Source_Report_Detalization::YEAR:
                return date('Y', strtotime($date));
                break;
        }
    }

    protected function _dateToString(Zend_Date $date)
    {
        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    protected function _applyDefaultFilters($collection, $from, $to)
    {
        $collection->addFieldToFilter('main_table.created_at', array('from' => $from, 'to' => $to))
            ->addFieldToFilter('main_table.affiliate_id', $this->_getCurrentAffiliate()->getId())
            ->addFieldToFilter('main_table.campaign_id', array('in' => $this->getCampaignIds()));
        return $this;
    }

    public function getTotals()
    {
        return $this->_totals;
    }

    protected function _formatCurrency($value)
    {
        return sprintf('%.2f', $value);
    }

    protected function _formatPercent($value)
    {
        return sprintf('%.2f%%', $value * 100);
    }

    /**
     * @return AW_Affiliate_Model_Resource_Campaign_Collection
     */
    public function getCampaigns()
    {
        if ($this->_campaigns === null) {
            /** @var $collection AW_Affiliate_Model_Resource_Campaign_Collection */
            $collection = Mage::getModel('awaffiliate/campaign')->getCollection();
            $collection->addFilterByIds($this->getData('campaigns'));
            $this->_campaigns = $collection;
        }
        return $this->_campaigns;
    }

    public function getCampaignIds()
    {
        return $this->getCampaigns()->getAllIds();
    }

    public function getDetalizationLabel()
    {
        return Mage::getSingleton('awaffiliate/source_report_detalization')->getOptionLabel($this->getDetalization());
    }

    public function getDownloadAsCsvUrl()
    {
        return Mage::getUrl('awaffiliate/customer_affiliate/downloadreport');
    }

    public function getDefaultCurrencySymbol()
    {
        return Mage::helper('awaffiliate')->getDefaultCurrencySymbol();
    }

    public function setMaxRangesLimitExceeded($flag = true)
    {
        return $this->setData('_max_ranges_exceeded', $flag);
    }

    public function getMaxRangesLimitExceeded()
    {
        return (bool)$this->getData('_max_ranges_exceeded');
    }

    abstract protected function _collectData($from, $to);

    abstract protected function _collectTotals();

    abstract protected function _saveItemsInSession();

    abstract protected function _getReportType();
}
