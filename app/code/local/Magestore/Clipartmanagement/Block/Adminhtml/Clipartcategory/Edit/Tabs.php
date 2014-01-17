<?php

class Magestore_Clipartmanagement_Block_Adminhtml_Clipartcategory_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('clipartcategory_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('clipartmanagement')->__('Clipart Category Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('clipartmanagement')->__('Clipart Category Information'),
          'title'     => Mage::helper('clipartmanagement')->__('Clipart Category Information'),
          'content'   => $this->getLayout()->createBlock('clipartmanagement/adminhtml_clipartcategory_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}