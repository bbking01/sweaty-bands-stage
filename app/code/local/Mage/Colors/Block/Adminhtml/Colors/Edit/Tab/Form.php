<?php

class Mage_Colors_Block_Adminhtml_Colors_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('colors_form', array('legend'=>Mage::helper('colors')->__('Colors Counter information')));
     
      $fieldset->addField('colors_counter', 'text', array(
          'label'     => Mage::helper('colors')->__('Colors Counter'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'colors_counter',
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getColorsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getColorsData());
          Mage::getSingleton('adminhtml/session')->setColorsData(null);
      } elseif ( Mage::registry('colors_data') ) {
          $form->setValues(Mage::registry('colors_data')->getData());
      }
      return parent::_prepareForm();
  }
}