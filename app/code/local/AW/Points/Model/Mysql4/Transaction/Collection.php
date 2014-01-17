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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Model_Mysql4_Transaction_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    const OLD_LOCK_DAYS = 3;

    protected $_is_locked;

    public function _construct() {
        parent::_construct();
        $this->_init('points/transaction');
    }

    public function limitByDay($dateTimestamp) {
        $currentDate = date('Y-m-d', $dateTimestamp);
        $this->getSelect()->where('date(change_date) = ?', $currentDate);

        return $this;
    }

    public function addExpiredFilter() {
        $curZendDate = new Zend_Date();
        $curDateTime = $curZendDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $this->getSelect()->where('expiration_date <= ?', $curDateTime);
        return $this;
    }

    public function addExpiredAfterDaysFilter($daysToAdd) {

        $currentZendDate = new Zend_Date();
        $dateAfterDays = $currentZendDate->addDay($daysToAdd + 1)->toString(Varien_Date::DATE_INTERNAL_FORMAT);

        $this
                ->getSelect()
                ->where('expiration_date < ?', $dateAfterDays)
                ->where('expiration_notification_sent IS NULL')
                ->order('summary_id');

        return $this;
    }

    public function addBalanceActiveFilter() {
        $this->getSelect()->where('balance_change_spent < balance_change')->where('balance_change > 0');
        return $this;
    }

    public function addNotLockedFilter() {
        $this->getSelect()->where('COALESCE(is_locked,0) < 1');
        return $this;
    }

    public function addOldLockedFilter() {
        $today = new Zend_Date;
        $today->subDay(3);
        $oldDateTimeString = $today->toString(AW_Core_Model_Abstract::DB_DATETIME_FORMAT);
        $this->getSelect()->where("is_locked>0 AND lock_changed_date<'" . ($oldDateTimeString) . "'");
        return $this;
    }

    /**
     * Set fast lock for collection.
     * @param bool|int $state
     * @return AW_Points_Model_Mysql4_Transaction_Collection
     */
    public function lock($state=1) {
        $onlyWhere = preg_replace('/.*(where.*)/i', '$1', $this->getSelect()->assemble());
        $lockQuery = "UPDATE `" . $this->getMainTable() . "` SET is_locked=" . intval($state) . ", lock_changed_date='" . now() . "' $onlyWhere";
        $this->getResource()->getReadConnection()->raw_query($lockQuery);
        $this->_is_locked = $state;
        return $this;
    }

    public function unlock() {
        return $this->lock(0);
    }

    public function updateBalanceChangeSpent($summaryId, $spentAmount) {
        $this
                ->addFieldToFilter('summary_id', $summaryId)
                ->addBalanceActiveFilter();

        $amountToReduce = - $spentAmount;
        foreach ($this as $item) {
            $itemBalance = $item->getBalanceChange();
            $itemBalanceSpent = $item->getBalanceChangeSpent();
            $itemBalanceDelta = $itemBalance - $itemBalanceSpent;
            if ($amountToReduce >= $itemBalanceDelta) {
                $amountToReduce -= $itemBalanceDelta;
                $item->setBalanceChangeSpent($itemBalance)->save();
            } else {
                $item->setBalanceChangeSpent($itemBalanceSpent + $amountToReduce)->save();
                break;
            }
        }
        return $this;
    }

    public function fixResult() {
        $this->_fetchStmt = $this->getConnection()->query($this->getSelect());
    }

}