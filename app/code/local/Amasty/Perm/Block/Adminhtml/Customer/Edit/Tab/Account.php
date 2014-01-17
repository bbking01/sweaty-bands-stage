<?php
/**
 * Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */
class Amasty_Perm_Block_Adminhtml_Customer_Edit_Tab_Account extends Mage_Adminhtml_Block_Customer_Edit_Tab_Account
{
    public function __construct()
    {
        parent::__construct();
    }

    public function setForm(Varien_Data_Form $form)
    {
        $allowedGroup = $this->_getAllowedGroup();
        
        if ($allowedGroup){
            $fld = $form->getElement('group_id');
            $label = '';
            
            $newValues = array();
            
            foreach ($fld->getValues() as $option){
                if (in_array($option['value'], $allowedGroup)) {
                    $newValues[$option['value']] = $option['label']; 
                }
            }
            $fld->setValues($newValues);
        }
        return parent::setForm($form);
    }
    
    protected function _getAllowedGroup()
    {
        $user = Mage::getSingleton('admin/session')->getUser();  
        if (!$user)
            return 0;
        
        if (!Mage::helper('amperm')->isSalesPerson($user)){
            return 0;
        }   

        return explode(",", $user->getCustomerGroupId());         
    }

}