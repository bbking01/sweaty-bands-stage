<?php
/**
 * @copyright   Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */
class Amasty_Perm_Model_Perm extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('amperm/perm');
    }
    
    //ids only
    public function getCustomers($userId)
    {
        return $this->getResource()->getCustomerIds($userId);
    }
    
    public function assignCustomers($userId, $customerIds)
    {
        $this->getResource()->assignCustomers($userId, $customerIds);
        return $this;
    }
    
    public function assignOneCustomer($userId, $customerId)
    {
        $this->getResource()->assignOneCustomer($userId, $customerId);
        return $this;
    }
    
    public function addOrdersRestriction($collection, $userId)
    {
        $collection->addFieldToFilter('uid', $userId);
        return $this;
    }
    
    public function addOrderDataRestriction($collection, $userId)
    {
        $orders = $this->getResource()->getOrderIds($userId);
        $collection->addFieldToFilter('order_id', array('in'=>$orders));
        
        return $this;
    }
    
    public function addCustomersRestriction($collection, $userId)
    {
        $collection->joinField(
            'perm',            // alias
            'amperm/perm',     // table
            'uid',             // field
            'cid=entity_id',   // bind
            array ('uid' => $userId), // conditions
            'inner'            // join type
        ); 
        
        return $this;
    }    
    
    public function assignOneOrder($userId, $orderId)
    {
        $this->getResource()->assignOneOrder($userId, $orderId);
        return $this;
    } 
    
    public function assignOrderByCustomer($customerId, $orderId)
    {
        $userId = $this->getResource()->getUserByCustomer($customerId);
        $this->assignOneOrder($userId, $orderId);
        
        return $userId; 
    }     
    
    public function getUserByOrder($orderId)
    {
        return $this->getResource()->getUserByOrder($orderId); 
    }
    
    public function updateOrder($orderId, $uid)
    {
        return $this->getResource()->updateOrder($orderId, $uid);
    }
       
}