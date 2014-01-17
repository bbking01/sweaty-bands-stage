<?php
/**
 * @copyright   Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */
class Amasty_Perm_Model_Mysql4_Perm extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('amperm/perm', 'perm_id');
    }

    public function getCustomerIds($userId)
    {
        $db = $this->_getReadAdapter();

        $sql = $db->select()
            ->from($this->getTable('amperm/perm'), 'cid')
            ->where('uid = ?', $userId);

        return $db->fetchCol($sql);

    }
    
    public function assignGroups($adminId, $groups)
    {
    	$db = $this->_getWriteAdapter();
    	$sql = "UPDATE `{$this->getTable('admin/user')}` SET customer_group_id='$groups' WHERE user_id = $adminId LIMIT 1";
        $db->raw_query($sql);
    }

    public function assignCustomers($userId, $customerIds)
    {
        $db = $this->_getWriteAdapter();

        $userId = intVal($userId);
        $db->delete($this->getTable('amperm/perm'), "uid=$userId");

        if (!$customerIds)
            return;

        $db->delete($this->getTable('amperm/perm'), "cid IN (".join(',', $customerIds).")");

        $sql = 'INSERT INTO `' . $this->getTable('amperm/perm') . '` (`uid`, `cid`) VALUES ';
        foreach ($customerIds as $id) {
            $id  = intVal($id);
            $sql .= "($userId , $id),";
        }

        $sql = substr($sql, 0, -1);
        $db->raw_query($sql);

        // make sure old orders now assigned to the right user
        //$sql = 'UPDATE `' . $this->getTable('sales/order_grid') . '` AS o, `'.$this->getTable('amperm/perm').'` AS p SET o.uid=p.uid WHERE o.customer_id=p.cid';
        //$db->raw_query($sql);


        return true;
    }

    public function assignOneCustomer($userId, $customerId)
    {
        $db = $this->_getWriteAdapter();

        $userId     = intVal($userId);
        $customerId = intVal($customerId);

        $db->delete($this->getTable('amperm/perm'), "cid=$customerId");

        $db->insert($this->getTable('amperm/perm'), array('uid'=>$userId, 'cid'=>$customerId));

        return true;
    }

    public function getOrderIds($userId)
    {
        $db = $this->_getReadAdapter();

        $sql = $db->select()
            ->from($this->getTable('sales/order_grid'), array('entity_id'))
            ->where('uid = ?', $userId);

        return $db->fetchCol($sql);
    }

    public function assignOneOrder($userId, $orderId)
    {
        $db = $this->_getWriteAdapter();

        $userId     = intVal($userId);
        $customerId = intVal($orderId);

        $sql = "UPDATE `{$this->getTable('sales/order_grid')}` SET uid=$userId WHERE entity_id = $orderId LIMIT 1";
        $db->raw_query($sql);

        return true;
    }

    public function getUserByCustomer($customerId)
    {
        $db = $this->_getReadAdapter();

        $sql = $db->select()
            ->from($this->getTable('amperm/perm'), 'uid')
            ->where('cid = ?', $customerId)
            ->limit(1);

        return $db->fetchOne($sql);
    }

    public function getUserByOrder($orderId)
    {
        $db = $this->_getReadAdapter();

        $sql = $db->select()
            ->from($this->getTable('sales/order_grid'), 'uid')
            ->where('entity_id = ?', $orderId)
            ->limit(1);

        return $db->fetchOne($sql);
    }

    public function updateOrder($orderId, $uid)
    {
        $db = $this->_getWriteAdapter();

        $db->update($this->getTable('sales/order_grid'),
            array('uid' => $uid), 'entity_id = '. $orderId);

        return true;
    }

}