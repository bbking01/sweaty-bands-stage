<?php
/**
 * @copyright   Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */
class Amasty_Perm_Block_Adminhtml_Restrictions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('permissions_user');
        
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('user_');

        $groups = Mage::helper('customer')->getGroups()->toOptionArray();
        $empty = array(
            'label' => Mage::helper('amperm')->__('All'),
            'value' => 0
        );
        array_unshift($groups, $empty);
        
        $fldSet = $form->addFieldset('amperm_set', array('legend'=>Mage::helper('adminhtml')->__('Restrictions')));
        $fldSet->addField('customer_group_id', 'multiselect', array(
            'name'  	=> 'customer_group_id',
            'label' 	=> Mage::helper('amperm')->__('Allowed Customer Group'),
            'values'    => $groups,         
        ));

        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    } 
}