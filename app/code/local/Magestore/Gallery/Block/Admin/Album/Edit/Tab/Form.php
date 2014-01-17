<?php

class Magestore_Gallery_Block_Admin_Album_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('album_form', array('legend'=>Mage::helper('gallery')->__('Category information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('gallery')->__('Category Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'image', array(
          'label'     => Mage::helper('gallery')->__('Photo'),
          'required'  => false,
          'name'      => 'filename',
	  ));
     $fieldset->addField('url_key', 'text', array(
          'label'     => Mage::helper('gallery')->__('Url key'),
          'required'  => false,
          'name'      => 'url_key',
      ));
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('gallery')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('gallery')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('gallery')->__('Disabled'),
              ),
          ),
      ));
      
     /* $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('gallery')->__('Description'),
          'title'     => Mage::helper('gallery')->__('Description'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
      ));*/
      $fieldset->addField('url_rewrite_id', 'hidden', array(
          'name'      => 'url_rewrite_id',
      ));
      if ( Mage::getSingleton('adminhtml/session')->getGalleryData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getGalleryData());
          Mage::getSingleton('adminhtml/session')->setGalleryData(null);
      } elseif ( Mage::registry('album_data') ) {
          $form->setValues(Mage::registry('album_data')->getData());
      }
      return parent::_prepareForm();
  }
}