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


class AW_Points_RewardController extends Mage_Core_Controller_Front_Action {

    /**
     * Check customer authentication
     */
    public function preDispatch() {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();

        if (Mage::helper('points/config')->isPointsEnabled()) {
            $loginUrl = Mage::helper('customer')->getLoginUrl();

            if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            }
        } else {
            $this->_redirect('customer/account/');
        }
    }

    public function indexAction() {

        $this->loadLayout();
        $this->_initPage();
        $block = $this->getLayout()->getBlock('points.reward');
        if ($block)
            $block->setRefererUrl($this->_getRefererUrl());

        if (array_key_exists('subscribe', $this->getRequest()->getParams())) {
            $this->_forward('subscribe');
        }

        $this->getLayout()->getBlock('head')->setTitle($this->__('Reward Points'));
        $this->renderLayout();
    }

    public function subscribeAction() {

        $_helper = Mage::helper('points');

        $isSubsc = (int) $this->getRequest()->getParam('is_subscribed');
        $isSubscExp = (int) $this->getRequest()->getParam('is_subscribed_exp');

        $summary = Mage::getModel('points/summary')
                ->loadByCustomer(
                Mage::getSingleton('customer/session')
                ->getCustomer()
        );

        if (!($summary->getBalanceUpdateNotification() == $isSubsc)) {
            $message = $isSubsc ? $_helper->__('The subscription for balance update notification has been saved') : $_helper->__('The subscription for balance update notification has been removed');
            Mage::getSingleton('customer/session')->addSuccess($message);
        }

        if (!($summary->getPointsExpirationNotification() == $isSubscExp)) {
            $message = $isSubscExp ? $_helper->__('The subscription for points expiration notification has been saved') : $_helper->__('The subscription for points expiration notification has been removed');
            Mage::getSingleton('customer/session')->addSuccess($message);
        }

        if (
                ($summary->getBalanceUpdateNotification() == $isSubsc)
                &&
                ($summary->getPointsExpirationNotification() == $isSubscExp)
        ) {
            Mage::getSingleton('customer/session')->addNotice('Email Notification Settings was not changed');
        }

        try {
            $summary
                    ->setBalanceUpdateNotification($isSubsc)
                    ->setPointsExpirationNotification($isSubscExp)
                    ->setUpdateDate(true)
                    ->save();
        } catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($e->getMessage());
        }


        $this->_redirectReferer();
    }

    private function _initPage() {

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock)
            $navigationBlock->setActive('points/reward');
    }

    /*
     * 
     *   activate coupon by code
     * 
     */

    public function couponActivationAction() {

        $coupon_code = $this->getRequest()->getParam('coupon_code');
        $customerSession = Mage::getSingleton('customer/session');

        if (!$coupon_code) {
            $customerSession->addError(Mage::helper('points')->__('Please, enter a coupon code'));
            $this->_redirectReferer();
            return $this;
        }

        $lastActivation = (int) $customerSession->getData('awpoints_coupon');
        $now = time();

        $customerSession->setData('awpoints_coupon', $now);
        $secondsBetweenAttempts = 10;

        if (($now - $lastActivation) < $secondsBetweenAttempts) {
            $customerSession->addError(Mage::helper('points')->__('Please, wait %s seconds before submitting', $secondsBetweenAttempts));
            $this->_redirectReferer();
            return $this;
        }
        $coupon = Mage::getModel('points/coupon')->loadByCouponCode($coupon_code);

        if (!$coupon->getId()) {
            $customerSession->addError(Mage::helper('points')->__('Invalid coupon code'));
            $this->_redirectReferer();
            return $this;
        }
        $customer = $customerSession->getCustomer();

        /* 1.Webs */
        if (!$coupon->validateWebsite()) {
            $customerSession->addError(Mage::helper('points')->__('You cannot use this coupon on this website'));
            $this->_redirectReferer();
            return $this;
        }

        /*   2.Customer group    */
        if (!$coupon->validateCustomerGroup($customer)) {
            $customerSession->addError(Mage::helper('points')->__('You must be included to another group of customers to activate this coupon'));
            $this->_redirectReferer();
            return $this;
        }

        /*  3.Active/Inactive  */
        if (!$coupon->getData('status')) {
            $customerSession->addError(Mage::helper('points')->__('Coupon is inactive'));
            $this->_redirectReferer();
            return $this;
        }


        /*  4.Period of validity  */
        if (!$coupon->isStarted()) {
            $customerSession->addError(Mage::helper('points')->__('Coupon is not active yet'));
            $this->_redirectReferer();
            return $this;
        }

        if ($coupon->isExpired()) {
            $customerSession->addError(Mage::helper('points')->__('Coupon activation period has expired'));
            $this->_redirectReferer();
            return $this;
        }


        /* 5.Number of activations */
        if ($coupon->getData('activation_cnt') >= $coupon->getData('uses_per_coupon')) {
            $customerSession->addError(Mage::helper('points')->__('The limit of activations of this coupon is reached'));
            $this->_redirectReferer();
            return $this;
        }

        $customerCouponTransaction = Mage::getModel('points/coupon_transaction')
                ->LoadByCouponIdCustomerId($coupon->getId(), $customer->getId())
                ->getData('transaction_id');
        if ($customerCouponTransaction) {
            $customerSession->addError(Mage::helper('points')->__('You cannot activate this coupon twice'));
            $this->_redirectReferer();
            return $this;
        }


        $transactionId = Mage::getModel('points/api')->addTransaction(
                $coupon->getData('points_amount'), 'coupon_activation', $customer
        );

        if ($transactionId) {

            $couponTransaction = Mage::getModel('points/coupon_transaction')
                    ->setData('coupon_id', $coupon->getId())
                    ->setData('transaction_id', $transactionId)
                    ->setData('customer_id', $customer->getId())
                    ->save();

            $coupon->activate();

            Mage::getSingleton('customer/session')->addSuccess(Mage::helper('points')->__('Coupon was activated'));
        } else {
            Mage::getSingleton('customer/session')->addError(Mage::helper('points')->__('Coupon was NOT activated'));
            $customerSession->setData('awpoints_coupon', NULL);
        }

        $this->_redirectReferer();
        return $this;
    }

}

?>
