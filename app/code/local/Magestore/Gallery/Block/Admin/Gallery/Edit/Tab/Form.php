<?php

class Magestore_Gallery_Block_Admin_Gallery_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('gallery_form', array('legend'=>Mage::helper('gallery')->__('Template information')));
     
	  $albums = array(array('value' => '', 'label' => 'Select a Category'));
	  $collection = Mage::getModel('gallery/album')->getCollection();
	  foreach ($collection as $album) {
		 $albums[] = array('value' => $album->getId(), 'label' => $album->getTitle());
	  }

      $fieldset->addField('album_id', 'select', array(
          'label'     => Mage::helper('gallery')->__('Category'),
          'name'      => 'album_id',
          'required'  => true,
          'values'    => $albums,
      ));

      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('gallery')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

     $fieldset->addField('filename', 'image', array(
          'label'     => Mage::helper('gallery')->__('File'),
          'required'  => false,
          'name'      => 'filename',
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
	  
      
     /* $fieldset->addField('order', 'text', array(
          'label'     => Mage::helper('gallery')->__('Order'),
          'class'     => 'validate-zero-or-greater input-text validation-failed',
          'required'  => false,
          'name'      => 'order',
      ));
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('gallery')->__('Content'),
          'title'     => Mage::helper('gallery')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false
      ));*/
      if ( Mage::getSingleton('adminhtml/session')->getGalleryData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getGalleryData());
          Mage::getSingleton('adminhtml/session')->setGalleryData(null);
      } elseif ( Mage::registry('gallery_data') ) {
          $form->setValues(Mage::registry('gallery_data')->getData());
      }
      return parent::_prepareForm();
  }
}