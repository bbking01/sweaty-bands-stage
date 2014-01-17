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


class AW_Points_Model_Transaction extends Mage_Core_Model_Abstract {
    const ACTION_WRITING_REVIEW = 'rewiew_write';
    const ACTION_TAGGING_PRODUCT = 'tag_product';
    const ACTION_REFERAL_REGISTERED = 'ref_registation';
    const ACTION_REFERAL_PAYED_ORDER = 'ref_order_payed';
    const ACTION_NEWSLETTER_SIGNUP = 'newsletter_signup';
    const ACTION_POINTS_EXPIRATION = 'points_expiration';
    const ACTION_POINTS_ADDED_BY_ADMIN = 'added_by_admin';
    const ACTION_OTHER = 'other';

    const COMMENT_REFERAL_PAYED_ORDER = 'Reward for invited user placed order';
    const COMMENT_REFERAL_REGISTERED = 'Reward for registration of invited user %s';

    public function _construct() {
        parent::_construct();
        $this->_init('points/transaction');
    }

    public function getCustomer() {
        return Mage::getModel('points/summary')->load($this->getSummaryId())->getCustomer();
    }

    public function loadByOrder($order) {
        $this->getResource()->loadByOrderIncrementId($this, $order->getIncrementId());
        return $this;
    }
    
    public function calculatePointsFor($order, $type = AW_Points_Helper_Config::MONEY_SPENT)
    {        
        return $this->getResource()->calculatePointsFor($order, $type);
    }
    
    public function getRefererPointsFor($order)
    {        
        return $this->getResource()->getRefererPointsFor($order);
    }

    /**
     * Creates new transaction with information about points changes of customer
     * @param int $amount
     * @param int $action
     * @param Mage_Customer_Model_Customer $customer
     * @param array $additionalData
     * @return AW_Points_Model_Transaction 
     */
    public function changePoints($amount, $action, $summary, $additionalData = array()) {
        if (!($summary instanceof AW_Points_Model_Summary) || !$summary->getId()) {
            throw new AW_Core_Exception(Mage::helper('points')->__('Cannot load summary, action - %s, amount - %s', $action, $amount));
        }

        if (!$amount)
            throw new Exception(Mage::helper('points')->__('Zero transaction amount'));

        $customer = $summary->getCustomer();

        if (!$customer->getId())
            throw new Exception(Mage::helper('points')->__('Guest can not work with points'));

        $storeId = isset($additionalData['store_id']) ? $additionalData['store_id'] : null;
        $expirationDate = null;
        if ($amount > 0)
            $expirationDate = isset($additionalData['expiration_date']) ? $additionalData['expiration_date'] : $this->_prepareExpirationDate($storeId);

        if (isset($additionalData['comment']))
            $additionalData['comment'] = htmlspecialchars($additionalData['comment']);
        if (isset($additionalData['notice']))
            $additionalData['notice'] = htmlspecialchars($additionalData['notice']);

        $this
                ->addData($additionalData)
                ->setSummaryId($summary->getId())
                ->setAction($action)
                ->setChangeDate(Mage::getModel('core/date')->gmtDate())
                ->setExpirationDate($expirationDate)
                ->setBalanceChange($amount)
                ->setCustomerName($customer->getName())
                ->setCustomerEmail($customer->getEmail())
                ->save();

        $summary
                ->setPoints($summary->getPoints() + $amount)
                ->save();

        return $this;
    }

    public function getActionInstance() {
        return
                        AW_Points_Model_Actions_Abstract::getInstance($this->getAction(), $this->getCustomer())
                        ->setTransaction($this);
    }

    protected function _prepareExpirationDate($store = null) {
        if ($addDays = Mage::helper('points/config')->getPointsExpirationDays($store)) {
            $newDate = new Zend_Date();
            return $newDate->setTimezone('UTC')->addDay($addDays)->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }
        return;
    }

    public function saveSpendOrderInfo($order) {
        $this->getResource()->saveSpendOrderInfo($this, $order);
        return $this;
    }

}
