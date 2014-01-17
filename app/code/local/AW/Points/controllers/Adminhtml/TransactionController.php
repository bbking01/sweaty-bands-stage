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

class AW_Points_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action {

    protected function displayTitle() {
        if (!Mage::helper('points')->magentoLess14())
            $this->_title($this->__('Rewards'))->_title($this->__('Transactions'));
        return $this;
    }

    public function indexAction() {
        $this
                ->displayTitle()
                ->loadLayout()
                ->_setActiveMenu('promo')
                ->_addContent($this->getLayout()->createBlock('points/adminhtml_transaction'))
                ->renderLayout();
    }

    public function editAction() {
        $transaction = Mage::getModel('points/transaction')->load($this->getRequest()->getParam('id'));

        Mage::register('points_current_transaction', $transaction);
        $breadcrumbTitle = $breadcrumbLabel = Mage::helper('points')->__('View Transaction');

        $this
                ->displayTitle()
                ->loadLayout()
                ->_setActiveMenu('promo')
                ->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle)
                ->_addContent($this->getLayout()->createBlock('points/adminhtml_transaction_edit'))
                ->renderLayout();
    }

    public function newAction() {
        $this
                ->displayTitle()
                ->loadLayout()
                ->_setActiveMenu('promo')
                ->renderLayout();
    }

    public function saveAction() {
        $post = $this->getRequest()->getPost();
        if (empty($post['comment']) || empty($post['balance_change']) || empty($post['selected_customers'])) {
            Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('points')->__('Comments and Balance Change cannot be empty, at least one customer must be selected'));
            return $this->_redirect('*/*/new');
        }
        try {
            $customersIds = explode(',', $post['selected_customers']);
            foreach ($customersIds as $customerId) {                
                $customer = Mage::getModel('customer/customer')->load($customerId);             
                Mage::getModel('points/api')->addTransaction(
                        $post['balance_change'], 
                        'added_by_admin', 
                        $customer, 
                        null, 
                        array('comment' => $post['comment']), 
                        array('store_id' => $this->_getStoreId($customer) )
                );
            }
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('points')->__('Error while saving transaction'));
            return $this->_redirect('*/*/new');
        }
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('points')->__('Transaction(s) successfully created'));
        return $this->_redirect('*/*/index');
    }
    
    

    public function customersGridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('points/adminhtml_customer_grid')->toHtml());
    }

    public function massSubscribeAction() {

        $arrayOfCustomersId = $this->getRequest()->getParam('selected_customers_form');

        if (isset($arrayOfCustomersId)) {
            $countOfRecords = 0;

            foreach ($arrayOfCustomersId as $customerID) {

                if ($customerID == 0)
                    continue;

                $summary = Mage::getModel('points/summary')
                        ->loadByCustomerID($customerID);

                if ($summary->getData('balance_update_notification') == 1
                        && $summary->getData('points_expiration_notification') == 1) {
                    $countOfRecords++;
                    continue;
                }

                $summary
                        ->setBalanceUpdateNotification(1)
                        ->setPointsExpirationNotification(1)
                        ->setUpdateDate(true)
                        ->save();

                $countOfRecords++;
            }
            Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('points')->__('Total of %s record(s) were updated.', $countOfRecords));
        } else {

            Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('points')->__('Error while subscribing for newsletter'));
        }
        $this->_redirectReferer();
    }

    public function resetTransactionsAction() {

        try {

            $transaction_table_name = Mage::getSingleton('core/resource')->getTableName('points/transaction');
            $summary_table_name = Mage::getSingleton('core/resource')->getTableName('points/summary');
            $couponTable = Mage::getSingleton('core/resource')->getTableName('points/coupon');
            $couponTransactionTable = Mage::getSingleton('core/resource')->getTableName('points/coupon_transaction');
            
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            $write->truncate($transaction_table_name);
            $write->truncate($couponTransactionTable);
            $write->exec("UPDATE `$couponTable` SET `activation_cnt`=0 WHERE 1");
            $write->exec("DELETE FROM `{$summary_table_name}` WHERE 1");

            Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('points')->__('Transaction(s) successfully reseted'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('points')->__('Error while resetting transaction(s)'));

            Mage::helper('awcore/logger')->log($this, Mage::helper('points')->__('Error while resetting transaction(s)'), AW_Core_Model_Logger::LOG_SEVERITY_ERROR, $e->getMessage(), $e->getLine());
        }
        $this->_redirectReferer();
    }
    
    protected function _getStoreId($customer)
    {
        try {
            $store = $customer->getStore();
            if ($store instanceof Varien_Object) {
                return $store->getId();
            }
        } catch (Mage_Core_Model_Store_Exception $e) {
            Mage::helper('awcore/logger')->log(
                    $this, 
                    sprintf('Store related to customer #%d not found', $customer->getId()), 
                    AW_Core_Model_Logger::LOG_SEVERITY_NOTICE
            );
        } catch (Exception $e) {
            
        }

        return null;
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('promo/points/transactions');
    }

}
