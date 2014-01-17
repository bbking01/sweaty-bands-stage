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


class AW_Points_Model_Cron {
     
    /**
     *  Cron launches  the function once a day (see crontab settings in config.xml)
     */
    public function checkAndCleanExpiredTransactions()
    {
        $this
                ->_cleanExpiredTransactions()
                ->_sendWarningLetter();
    }
    
    protected function _logger()
    {
        return  Mage::helper('awcore/logger');
    }
    
    public function addBirthdayTransactions()
    {       
        $helper = Mage::helper('points/config');
        
        $collection = Mage::getModel('customer/customer')->getCollection()
                ->addAttributeToFilter('dob', array('notnull' => 'dob'));
        
        $collection->getSelect()
                ->joinLeft(array('summary' => $collection->getTable('points/summary')), 
                        'summary.customer_id = e.entity_id', array('summary_id' => 'id', '*'))                
                ->having('EXTRACT(DAY FROM `dob`) = EXTRACT(DAY FROM UTC_TIMESTAMP())')
                ->having('EXTRACT(MONTH FROM `dob`) = EXTRACT(MONTH FROM UTC_TIMESTAMP())');
        
        foreach($collection as $info) {
            
            $summary = Mage::getModel('points/summary')->load($info->getSummaryId());
            
            if(!$summary->getId()) {  
                if(!$summary = $this->_createSummary($info)) {
                    continue;
                }                
            }
         
            try {                
               $summary->setLastBirthday(preg_replace("#^(\d+)#is", gmdate('Y'), $info->getDob()))->save();                    
            } catch(Exception $e) {
                $msg = $helper->__('Unable to set birthday for current date to customer #%s', $info->getEntityId());
                $this->_logger()->log($this, $msg, AW_Core_Model_Logger::LOG_SEVERITY_WARNING);
                continue;
            }                
          
            if(!$points = $this->_getPointsToSend($info)) {
                 continue;    
            }
          
            $customer = Mage::getModel('customer/customer')->load($info->getEntityId());
            
            Mage::register(AW_Points_Helper_Config::STOP_MAIL, true, true);       
            
            if(!$this->_addBirthdayTransaction($customer)) {
                 continue;   
            }            
             /* notifications for store are not enabled */
            if (!$helper->getIsEnabledNotifications($info->getStoreId())) {
                continue;
            }
            /* customer is not subscribed to email notifications */
            if(!(int) $summary->getBalanceUpdateNotification()) {
                continue;
            }
           
              try {
                   Mage::getModel('core/email_template')->setDesignConfig(
                        array('area' => 'frontend', 'store' => $info->getStoreId()))
                            ->sendTransactional(
                                    $helper->getPointsBirthdayTemplate($info->getStoreId()), 
                                    $helper->getNotificatioinSender($info->getStoreId()), 
                                    $customer->getEmail(), 
                                    null, 
                                    array(
                                        'store' => $customer->getStore(),
                                        'customer' => $customer,
                                        'pointstotal' => $summary->getPoints() + $points,
                                        'pointsupdate' => $points,
                                        'comment'   => $helper->__('%s for birthday', $helper->getPointUnitName($info->getStoreId())),
                                        'pointsname' => $helper->getPointUnitName($info->getStoreId())                
                                    ),
                                    $info->getStoreId()
                    );
                } catch (Exception $e) {
                    $logMessage = $helper->__('Unable to send birthday notification to customer #%s', $e->getMessage());
                    $this->_logger()->log($this, $logMessage, AW_Core_Model_Logger::LOG_SEVERITY_WARNING);
                }               
            }        
    }
    
    protected function _createSummary($info)
    {       
        $info->setData('subscribed_by_default', 
                Mage::helper('points/config')->getIsSubscribedByDefault($info->getStoreId()));
        
        return Mage::getModel('points/summary')->createFromObject($info); 
    }
    
    protected function _addBirthdayTransaction($customer) 
    {                       
        try {
            Mage::getModel('points/api')->addTransaction(
                    Mage::helper('points/config')->getPointsForBirthday($customer->getStoreId()), 
                   'customer_birthday', 
                    $customer, 
                    new Varien_Object(array('store_id', $customer->getStoreId()))
            );
            return true;
        } catch (Exception $e) {
            Mage::helper('awcore/logger')->log(
                    $this, 
                    sprintf('Failed to add points for birthday to customer #%d', $customer->getId()), 
                    AW_Core_Model_Logger::LOG_SEVERITY_FATAL
            );                    
        } 
        
        return false;
    }
    
    protected function _getPointsToSend($info)
    {
        $helper = Mage::helper('points/config');

        if (!$info->getIsActive()) {
            return;
        }   
        if (!$points = $helper->getPointsForBirthday($info->getStoreId())) {
            return;
        }
         
        if ($info->getLastBirthday()) {
            $now = new Zend_Date();
            $now->setTimezone('UTC');
            $dob = new Zend_Date($info->getLastBirthday(), Zend_Date::ISO_8601);
            $dob->setTimezone('UTC');
            $dateDiff = $now->get(Zend_Date::TIMESTAMP) - $dob->get(Zend_Date::TIMESTAMP);
            $days = floor((($dateDiff / 60) / 60) / 24);
            if ($days < 365) {
                return;
            }                    
        } 

        return $points;
    }
    
    /**
     * Cleans expired transactions. If transaction is expired, balance_change_spent becomes = balance_change
     */
    protected function _cleanExpiredTransactions()
    {
        // Remove old locks
        Mage::getModel('points/transaction')->getCollection()->addOldLockedFilter()->unlock();

        // Lock table for writing
        $expiredTransactions = Mage::getModel('points/transaction')->getCollection()
                ->addBalanceActiveFilter()
                ->addNotLockedFilter()
                ->addExpiredFilter();

        $expiredTransactions->getResource()->getReadConnection()->raw_query('LOCK TABLE ' . $expiredTransactions->getMainTable() . " as main_table READ");

        $ids = array();
        foreach ($expiredTransactions as $tr) {
            $ids[] = $tr->getId();
        }

        $expiredTransactions->getResource()->getReadConnection()->raw_query('UNLOCK TABLES');

        // Set lock
        $expiredTransactions->lock();

        foreach ($ids as $transactionId) {
            $transaction = Mage::getModel('points/transaction')->load($transactionId);
            $result = Mage::getModel('points/api')->addTransaction(
                    $transaction->getBalanceChangeSpent() - $transaction->getBalanceChange(), 'transaction_expired', $transaction->getCustomer(), $transaction, array('transaction_id' => $transaction->getId())
            );
        }


        return $this;
    }

    protected function _sendWarningLetter()
    {
        if (Mage::helper('points/config')->getIsEnabledNotifications()) {

            //Send points expire email before
            $sendBeforeDays = Mage::helper('points/config')->getDaysBeforePointExpiredToSendEmail();
            $transactionsToWarn = Mage::getModel('points/transaction')
                    ->getCollection()
                    ->addBalanceActiveFilter()
                    ->addExpiredAfterDaysFilter($sendBeforeDays);


            $summaryData = array();
            $currentDate = new Zend_Date(date('Y-m-d'));

            foreach ($transactionsToWarn as $transaction) {

                $summaryId = $transaction->getSummaryId();
                if (!isset($summaryData[$summaryId])) {
                    $summaryData[$summaryId] = array();
                }

                $expirationDate = $transaction->getData('expiration_date');

                $expirationDate = new Zend_Date($expirationDate);
                $expirationDate = new Zend_Date($expirationDate->toString(Varien_Date::DATE_INTERNAL_FORMAT));

                $daysLeft = $expirationDate->sub($currentDate)->toValue();
                $daysLeft = floor($daysLeft / (60 * 60 * 24));

                if (!isset($summaryData[$summaryId][$daysLeft])) {
                    $summaryData[$summaryId][$daysLeft] = (int) $transaction->getData('balance_change');
                } else {
                    $summaryData[$summaryId][$daysLeft]+=$transaction->getData('balance_change');
                }
            }


            $mail = Mage::getModel('core/email_template');
            foreach ($summaryData as $summaryId => $pointsExpData) {

                $summary = Mage::getModel('points/summary')->load($summaryId);

                if ($summary->getPointsExpirationNotification()) {

                    $customer = $summary->getCustomer();
                    $store = $customer->getStore();
                    $pointUnitName = Mage::helper('points/config')->getPointUnitName($store->getStoreId());


                    Mage::unregister('aw_points_unit_name');
                    Mage::register('aw_points_unit_name', $pointUnitName);

                    Mage::unregister('aw_points_exp_data');
                    Mage::register('aw_points_exp_data', $pointsExpData);

                    $mailParams = array(
                        'store' => $store,
                        'customer' => $customer,
                        'pointsname' => $pointUnitName,
                        'pointstoexpire' => array_sum($pointsExpData),
                        'expirationdays' => $sendBeforeDays,
                    );

                    try {

                        $mail->setDesignConfig(array('area' => 'frontend', 'store' => $store->getStoreId()))
                                ->sendTransactional(
                                        Mage::helper('points/config')->getPointsExpireTemplate($store->getStoreId()), Mage::helper('points/config')->getNotificatioinSender($store->getStoreId()), $customer->getEmail(), null, $mailParams
                        );
                    } catch (Exception $exc) {
                        $logMessage = Mage::helper('points')->__('Unable to send %s expiration email.', $pointUnitName) . '  ' . $exc->getMessage();
                        Mage::helper('awcore/logger')->log($this, $logMessage, AW_Core_Model_Logger::LOG_SEVERITY_WARNING);
                    }

                    if ($mail->getSentSuccess()) {
                        foreach ($transactionsToWarn as $transaction) {
                            if ($transaction->getSummaryId() == $summaryId) {
                                $transaction->setData('expiration_notification_sent', true)->save();
                            }
                        }
                    }
                }
            }
        }
        return $this;
    }

}
