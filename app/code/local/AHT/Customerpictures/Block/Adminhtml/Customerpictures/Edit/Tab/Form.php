<?php

class AHT_Customerpictures_Block_Adminhtml_Customerpictures_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('customerpictures_form', array('legend'=>Mage::helper('customerpictures')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('customerpictures')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('customerpictures')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('customerpictures')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('customerpictures')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('customerpictures')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('customerpictures')->__('Content'),
          'title'     => Mage::helper('customerpictures')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getCustomerpicturesData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getCustomerpicturesData());
          Mage::getSingleton('adminhtml/session')->setCustomerpicturesData(null);
      } elseif ( Mage::registry('customerpictures_data') ) {
          $form->setValues(Mage::registry('customerpictures_data')->getData());
      }
      return parent::_prepareForm();
  }
}