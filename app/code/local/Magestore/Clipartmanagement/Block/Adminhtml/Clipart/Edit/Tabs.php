<?php

class Magestore_Clipartmanagement_Block_Adminhtml_Clipart_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('clipart_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('clipartmanagement')->__('Clipart Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('clipartmanagement')->__('Clipart Information'),
          'title'     => Mage::helper('clipartmanagement')->__('Clipart Information'),
          'content'   => $this->getLayout()->createBlock('clipartmanagement/adminhtml_clipart_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}