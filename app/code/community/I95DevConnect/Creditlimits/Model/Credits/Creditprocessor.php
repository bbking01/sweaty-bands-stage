<?php

/**
 * i95Dev.com
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.i95dev.com/LICENSE-M1.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sub@i95dev.com so we can send you a copy immediately.
 *
 *
 * @category       I95DevConnect
 * @package        I95DevConnect_Creditlimits
 * @Description    Model for creditlimit payment method
 * @author         I95Dev
 * @copyright      Copyright (c) 2013 i95Dev
 * @license        http://store.i95dev.com/LICENSE-M1.txt
 */
class I95DevConnect_Creditlimits_Model_Credits_Creditprocessor extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'creditlimits';
    protected $_formBlockType = 'creditlimits/form_credit';
    protected $_infoBlockType = 'creditlimits/info_credit';
    protected $_order;
    protected $_isGateway = false;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canVoid = false;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_paymentMethod = 'creditlimits';
    protected $_defaultLocale = 'en';

    /**
     * To assign credit balance
     * @param array $data
     * @return I95DevConnect_Creditlimits_Model_Credits_Creditprocessor
     */
    public function assignData($data)
    {  
        $controllerName = Mage::app()->getRequest()->getControllerName(); 
        if($controllerName == 'onepage')
               $cid = Mage::getSingleton('customer/session')->getCustomer()->getId();
        else
               $cid = Mage::getModel('adminhtml/sales_order_create')->getSession()->getCustomer()->getId();
        $creditInfo = Mage::helper('i95devconnect_creditlimits')->checkAvailabeleBalanceAtGP($cid);
        $isBalance = $creditInfo['AvailableLimit'];
        try
        {
            if ($isBalance < 0 || !Mage::helper('i95devconnect_creditlimits')->checkAvailableBalance($cid))
                Mage::throwException('No sufficient credit balance. Please Check credit balance and try again. Thanks');
        }
        catch (Excepion $ex)
        {
            echo $ex->getMessage();
            Mage::helper('I95DevConnect_Base')->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
        return $this;
    }

    /**
     * Authorize action
     * @param Varien_Object $payment
     * @param $amount
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        try
        {
            Mage::throwException('sorry');
        }
        catch (Excepion $ex)
        {
            echo $ex->getMessage();
            Mage::helper('I95DevConnect_Base')->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
    }

    /**
     * To update the credit balance
     * @param Varien_Object $payment
     * @param $amount
     * @return I95DevConnect_Creditlimits_Model_Credits_Creditprocessor
     */
    public function capture(Varien_Object $payment, $amount)
    {
        try
        {
            $customerId = $payment->getOrder()->getCustomerId();
            if (!$customerId)
            {
                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            }

            $totalCredit = (float) Mage::helper('i95devconnect_creditlimits')->getTotalCredit($customerId);


            if ($totalCredit < $amount)
            {
                Mage::throwException(Mage::helper('i95devconnect_creditlimits')->__('No sufficient credit balance. Please Check credit balance and try again. Thanks'));
            }

            $transactionId = $payment->getOrder()->getIncrementId();
            if ($capture <= 0)
            {
                $payment->setStatus(self::STATUS_APPROVED)
                        ->setLastTransId($this->getTransactionId());
            }
        }
        catch (Excepion $ex)
        {
            echo $ex->getMessage();
            Mage::helper('I95DevConnect_Base')->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
        return $this;
    }

    /**
     * To cancel the transaction
     * @param Varien_Object $payment
     * @return I95DevConnect_Creditlimits_Model_Credits_Creditprocessor
     */
    public function cancel(Varien_Object $payment)
    {
        try
        {
            $transactionId = $payment->getOrder()->getIncrementId();

            if (!$transactionId)
            {
                Mage::throwException(Mage::helper('i95devconnect_creditlimits')->__('No transaction found.'));
            }
            else
            {

                $payment->setStatus(self::STATUS_DECLINED)
                        ->setLastTransId($this->getTransactionId());
            }
        }
        catch (Excepion $ex)
        {
            echo $ex->getMessage();
            Mage::helper('I95DevConnect_Base')->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
        return $this;
    }

    /**
     * To refund the amount
     * @param Varien_Object $payment
     * @param $amount
     * @return I95DevConnect_Creditlimits_Model_Credits_Creditprocessor
     */
    public function refund(Varien_Object $payment, $amount)
    {
        try
        {
            Mage::throwException(Mage::helper('i95devconnect_creditlimits')->__('No transaction found.'));
        }
        catch (Excepion $ex)
        {
            echo $ex->getMessage();
            Mage::helper('I95DevConnect_Base')->i95devLog(__METHOD__, $ex->getMessage(), "i95devException");
        }
        return $this;
    }

}