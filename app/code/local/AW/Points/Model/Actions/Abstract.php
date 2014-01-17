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


abstract class AW_Points_Model_Actions_Abstract {
    const ADMIN = 'admin';
    const FRONTEND = 'front';

    protected $_action = 'no_action';
    protected $_comment = 'no_comment';
    protected static $_actions = array();
    protected $_objectForAction;
    protected $_summary;
    protected $_transaction;
    protected $_amount;
    protected $_commentParams = array();
    protected $_additionalParams = array();

    public static function getInstance($action, $customer) {
        if (!self::$_actions)
            self::$_actions = Mage::getConfig()->getNode('points_actions');

        if ($action == 'an_customer_subscription' && !self::$_actions->$action) {
            $action = 'customer_subscription';
        }

        if (!self::$_actions->$action)
            throw new AW_Core_Exception('Cannot find instance for action');

        $instance = Mage::getModel(self::$_actions->$action);
        if (!($instance instanceof AW_Points_Model_Actions_Abstract))
            throw new Exception('Cannot find instance for action');

        return $instance->setSummary(Mage::getModel('points/summary')->loadByCustomer($customer));
    }

    public function setObjectForAction($objectForAction) {
        $this->_objectForAction = $objectForAction;
        return $this;
    }

    public function getObjectForAction() {
        return $this->_objectForAction;
    }

    public function setTransaction($transaction) {
        $this->_transaction = $transaction;
        return $this;
    }

    public function getTransaction() {
        return $this->_transaction;
    }

    public function setAmount($_amount) {
        $this->_amount = $_amount;
        return $this;
    }

    public function getAmount() {
        return $this->_amount;
    }

    public function setSummary($summary) {
        $this->_summary = $summary;
        return $this;
    }

    public function getSummary() {
        return $this->_summary;
    }

    public function setCommentParams($commentParams) {
        $this->_commentParams = $commentParams;
        return $this;
    }

    public function getCommentParams() {
        return $this->_commentParams;
    }

    public function getAction() {
        return $this->_action;
    }

    public function getComment() {
        return Mage::helper('points')->__($this->_comment);
    }

    public function getCommentHtml($area = self::ADMIN) {
        return $this->getComment();
    }

    /**
     * Add transaction for action
     * @param array $additionalData
     * @return AW_Points_Model_Actions_Abstract
     */
    public function addTransaction($additionalData = array()) {
        $additionalData['comment'] = $this->getComment();
        
        if(!isset($additionalData['store_id'])) {
            if ($this->getObjectForAction() instanceof Varien_Object && $this->getObjectForAction()->getData('store_id')) {
                $additionalData['store_id'] = $this->getObjectForAction()->getData('store_id');
            } else {
                $additionalData['store_id'] = Mage::app()->getStore()->getId();
            }
        }

        $this->_transaction =
                Mage::getModel('points/transaction')
                ->changePoints(
                $this->_applyLimitations($this->getAmount()), $this->getAction(), $this->getSummary(), $additionalData
        );

        if ($this->_transaction->getBalanceChange() < 0)
            $this->_updateTransactionsBalancePointsSpent();

        $this->sendMail();
        return $this;
    }

    /**
     * Used for negative amount transavtions. Updates previous transactions data, set balance_change_spent value to them in equivalent
     * of negative amount
     * @return AW_Points_Model_Actions_Abstract 
     */
    protected function _updateTransactionsBalancePointsSpent() {
        $this
                ->_transaction
                ->getCollection()
                ->updateBalanceChangeSpent($this->_summary->getId(), $this->_transaction->getBalanceChange());
        return $this;
    }

    /**
     * Apply limitation to amount (maimum points per customer limit)
     * @param int $amount
     * @return int
     */
    protected function _applyLimitations($amount) {
        $maxPointsPerCustomer = Mage::helper('points/config')->getMaximumPointsPerCustomer();
        $customerPoints = $this->getSummary()->getPoints();

        return $this->_calculateNewAmount($customerPoints, $amount, $maxPointsPerCustomer);
    }

    /**
     * Calculates correct amount to add to transaction according limit ($limitMax)
     * @param int $currentAmount
     * @param int $amountToAdd
     * @param int $limitMax
     * @return int 
     */
    protected function _calculateNewAmount($currentAmount, $amountToAdd, $limitMax) {
        $newAmountToAdd = $amountToAdd;
        /* If current amount + amount to add > limitation, we need to change amount to add */
        if ($limitMax && $currentAmount + $amountToAdd > $limitMax && $amountToAdd > 0) {
            if ($limitMax > $currentAmount) {
                $newAmountToAdd = $limitMax - $currentAmount;
            } else {
                $newAmountToAdd = 0;
            }
        }

        return $newAmountToAdd;
    }

    /**
     * Sending email for action
     * @return AW_Points_Model_Actions_Abstract 
     */
    public function sendMail() {
        
        if(Mage::registry(AW_Points_Helper_Config::STOP_MAIL)) {
            return $this;
        }

        $isEnabledUpdateNotification = (bool) (int) $this
                        ->getSummary()
                        ->getBalanceUpdateNotification();

        if (Mage::helper('points/config')->getIsEnabledNotifications() && $isEnabledUpdateNotification) {
            if ($this->getTransaction()->getStoreId()) {
                $store = Mage::app()->getStore($this->getTransaction()->getStoreId());
            } else {
                $store = $this->getSummary()->getCustomer()->getStore();
            }

            $mail = Mage::getModel('core/email_template');

            /* Magento 1.3 stub. */
            if (Mage::helper('points')->magentoLess14()) {
                $store->setFrontendName($store->getGroup()->getName());
            }
            /* Magento 1.3 stub ends */


            $mail->setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()))
                    ->sendTransactional(
                            Mage::helper('points/config')->getBalanceUpdateTemplate($store->getId()), Mage::helper('points/config')->getNotificatioinSender($store->getId()), $this->getTransaction()->getCustomerEmail(), null, array(
                        'store' => $store,
                        'comment' => $this->getTransaction()->getComment(),
                        'pointsupdate' => $this->getTransaction()->getBalanceChange(),
                        'pointstotal' => $this->getSummary()->getPoints(),
                        'pointsname' => Mage::helper('points/config')->getPointUnitName(),
                        'customer' => $this->getTransaction()->getCustomer()
                    ));
            if (!$mail->getSentSuccess()) {

                Mage::helper('awcore/logger')->log($this, Mage::helper('points')->__('Unable to send balance update email.'), AW_Core_Model_Logger::LOG_SEVERITY_WARNING
                );
            }
        }
        return $this;
    }

}
