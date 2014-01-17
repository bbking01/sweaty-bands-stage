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


class AW_Points_Model_Actions_TransactionExpired extends AW_Points_Model_Actions_Abstract {

    protected $_action = 'transaction_expired';
    protected $_comment = 'Transaction expired #%s';
    protected $_commentHtml = 'Transaction expired #%s';

    protected function _applyLimitations($amount) {
        /* If amount > 0 (expired transactions can be only negative) or customer has no points, set amount to 0 */
        if ($amount > 0 || $this->getSummary()->getPoints() <= 0)
            $amount = 0;
        else {
            /*
             * If customers points amount is less that amount to reduce (- $amount), we must set customers points amount to 0
             * ($amount = - $this->getSummary()->getPoints()) => $this->getSummary()->getPoints() - $this->getSummary()->getPoints() = 0
             */
            if ($this->getSummary()->getPoints() <= - $amount)
                $amount = - $this->getSummary()->getPoints();
        }

        return $amount;
    }

    public function getComment() {
        if (isset($this->_commentParams['transaction_id'])) {
            return Mage::helper('points')->__($this->_comment, $this->_commentParams['transaction_id']);
        }
        return $this->_comment;
    }

    public function getCommentHtml($area = self::ADMIN) {
        if (!$this->getTransaction())
            return;

        $expiredTransactionId = substr($this->getTransaction()->getComment(), strpos($this->getTransaction()->getComment(), '#') + 1);
        if ($expiredTransactionId) {
            return Mage::helper('points')->__($this->_comment, $expiredTransactionId);
        }
    }

    protected function _updateTransactionsBalancePointsSpent() {
        return $this;
    }

    public function addTransaction($additionalData = array()) {
        // Expire negative transactions can be created only for transactions not marked as expired yet
        $this->getObjectForAction()
                ->setBalanceChangeSpent($this->getObjectForAction()->getBalanceChange())
                ->save();
        parent::addTransaction($additionalData);
        return $this;
    }

}
