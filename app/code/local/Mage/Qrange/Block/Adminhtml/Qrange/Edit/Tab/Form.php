<?php

class Mage_Qrange_Block_Adminhtml_Qrange_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('qrange_form', array('legend'=>Mage::helper('qrange')->__('Quantity Range information')));
     
      $fieldset->addField('quantity_range_from', 'text', array(
          'label'     => Mage::helper('qrange')->__('Quantity From'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'quantity_range_from',
      ));

      $fieldset->addField('quantity_range_to', 'text', array(
          'label'     => Mage::helper('qrange')->__('Quantity To'),
          'required'  => false,
          'name'      => 'quantity_range_to',
	  ));
     
      if ( Mage::getSingleton('adminhtml/session')->getQrangeData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getQrangeData());
          Mage::getSingleton('adminhtml/session')->setQrangeData(null);
      } elseif ( Mage::registry('qrange_data') ) {
          $form->setValues(Mage::registry('qrange_data')->getData());
      }
      return parent::_prepareForm();
  }
}