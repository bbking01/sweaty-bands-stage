<?php
/**
 * Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */
class Amasty_Perm_Block_Adminhtml_Permissions_User_Edit_Tabs extends Mage_Adminhtml_Block_Permissions_User_Edit_Tabs
{
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
		}
		
	} 
	
    protected function _beforeToHtml()
    {
        $user = Mage::registry('permissions_user');
        if (!$user)
            return parent::_beforeToHtml();
            
        if (!$user->getId())
            return parent::_beforeToHtml();         
        
        if (Mage::helper('amperm')->isSalesPerson($user)){
            $this->addTab('customers_section', array(
                'label'     => Mage::helper('customer')->__('Manage Customers'),
                'title'     => Mage::helper('customer')->__('Manage Customers'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('amperm/adminhtml_perm/relation', array('_current' => true)),        
            ));
            
            $this->addTab('orders_section', array(
                'label'     => Mage::helper('reports')->__('Reports'),
                'title'     => Mage::helper('reports')->__('Reports'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('amperm/adminhtml_perm/reports', array('_current' => true)),        
            ));
            
            $this->addTab('restrictions_section', array(
                'label'     => Mage::helper('reports')->__('Restrictions'),
                'title'     => Mage::helper('reports')->__('Restrictions'),
                'content'   => $this->getLayout()->createBlock('amperm/adminhtml_restrictions')->toHtml()));
            
            $this->addTab('additional_information', array(
                'label'     => Mage::helper('reports')->__('Additional'),
                'title'     => Mage::helper('reports')->__('Additional'),
                'content'   => $this->getLayout()->createBlock('amperm/adminhtml_additional')->toHtml()));
        }
        
        
        return parent::_beforeToHtml();
    }
}