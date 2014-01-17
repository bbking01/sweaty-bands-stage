<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */
class Amasty_Perm_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCurrentSalesPersonId()
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        if (!$user)
            return 0;

        if (!$this->isSalesPerson($user)){
            return 0;
        }

        return $user->getId();
    }
    
    public function isSalesPerson($user)
    {
        $roles = Mage::getStoreConfig('amperm/general/role');
        $roles = explode(',', str_replace(' ', '', $roles));   

        return in_array($user->getRole()->getId(), $roles);        
    }

    public function getSalesPersonList()
    {
        $values = array();
        
        $roles = Mage::getStoreConfig('amperm/general/role');
        $roles = explode(',', str_replace(' ', '', $roles));   
        if (!$roles){
            return $values;
        }
        

        
    	$uid = $this->getCurrentSalesPersonId();
	    if ($uid && !Mage::getStoreConfig('amperm/messages/see_other_dealers')){
	       $user = Mage::getSingleton('admin/session')->getUser();
	       $values = array(
	           $uid =>  $user->getFirstname() . ' ' . $user->getLastname()   
	       );
	       return $values;    
	    }        

        $users = Mage::getResourceModel('admin/user_collection');
         
        $select = $users->getSelect();
        $select->reset(Zend_Db_Select::WHERE); 
        
        $table = Mage::getSingleton('core/resource')->getTableName('admin/role');
        $select->joinInner(array('u'=>$table), 'u.user_id = main_table.user_id')
            ->where("role_type = 'U'")
            ->where("parent_id IN(?)", $roles);

        foreach ($users as $u){
            $values[$u->getUserId()] =  $u->getFirstname() . ' ' . $u->getLastname();
        }

        return $values;
    }

}