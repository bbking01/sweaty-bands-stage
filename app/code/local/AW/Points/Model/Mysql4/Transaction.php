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


class AW_Points_Model_Mysql4_Transaction extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('points/transaction', 'id');
    }

    public function loadByOrderIncrementId($transaction, $orderIncrementId) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('transaction_orderspend'))
                ->where('order_increment_id = ?', $orderIncrementId);
        $data = $this->_getReadAdapter()->fetchRow($select);
        if (isset($data['transaction_id'])) {
            $transaction->load($data['transaction_id'])->addData($data);
        }
        return $this;
    }
    
    public function calculatePointsFor($order, $type)
    {
        $select = $this->_getReadAdapter()->select()
                ->from(array('main_table' => $this->getTable('transaction')))
                ->join(array('summary' => $this->getTable('summary')), 'main_table.summary_id = summary.id', 
                       array('points_earned' => new Zend_Db_Expr('SUM(balance_change)')))
                ->where('order_id = ?', $order->getId())
                ->where('main_table.store_id = ?', $order->getStoreId())
                ->where('summary.customer_id = ?', $order->getCustomerId())
                ->where('main_table.balance_change_type = ?',  $type)
                ->group(array('main_table.store_id', 'main_table.summary_id', 'main_table.order_id'));

        $points = $this->_getReadAdapter()->fetchRow($select);
        
        if(empty($points)) {
            return 0;
        }

        return (int) $points['points_earned'];
    }
    
    public function getRefererPointsFor($order)
    {        
        $select = $this->_getReadAdapter()->select()
                ->from(array('main_table' => $this->getTable('transaction')), array('*', 'points_earned' => 
                    new Zend_Db_Expr('SUM(balance_change)')))              
                ->where('order_id = ?', $order->getId())
                ->order('summary_id DESC')
               // ->where('main_table.store_id = ?', $order->getStoreId())                
                ->where('main_table.balance_change_type = ?',  AW_Points_Helper_Config::INVOICED_BY_REFERRAL)
                ->group(array('main_table.summary_id', 'main_table.order_id'));
       
        return $this->_getReadAdapter()->fetchRow($select);
    }
    
    public function saveSpendOrderInfo($transaction, $order) {
        $orderCustomer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $orderWebsite = $order->getStore()->getWebsite();
        $moneyForPointsBase = Mage::getModel('points/api')->changePointsToMoney($transaction->getBalanceChange(), $orderCustomer, $orderWebsite);
        $moneyForPoints = $order->getBaseCurrency()->convert($moneyForPointsBase, $order->getOrderCurrencyCode());

        $data = array(
            'transaction_id' => $transaction->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'points_to_money' => $moneyForPoints,
            'base_points_to_money' => $moneyForPointsBase
        );
        $this->_getWriteAdapter()->insert($this->getTable('transaction_orderspend'), $data);
        return $this;
    }

}
