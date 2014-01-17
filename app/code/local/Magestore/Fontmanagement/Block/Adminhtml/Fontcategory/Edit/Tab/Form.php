<?php

class Magestore_Fontmanagement_Block_Adminhtml_Fontcategory_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('fontmanagement_form', array('legend'=>Mage::helper('fontmanagement')->__('Font information')));
     
      $fieldset->addField('category_name', 'text', array(
          'label'     => Mage::helper('fontmanagement')->__('Font Category Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'category_name',
      ));
	  
	  $fieldset->addField('position', 'text', array(
          'label'     => Mage::helper('fontmanagement')->__('Position'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'position',
      )); 
     
      if ( Mage::getSingleton('adminhtml/session')->getFontManagementData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getFontManagementData());
          Mage::getSingleton('adminhtml/session')->setFontManagementData(null);
      } elseif ( Mage::registry('fontmanagement_data') ) {
          $form->setValues(Mage::registry('fontmanagement_data')->getData());
      }
      return parent::_prepareForm();
  }
} ?>