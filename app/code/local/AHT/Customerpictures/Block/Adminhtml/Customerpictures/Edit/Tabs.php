<?php

class AHT_Customerpictures_Block_Adminhtml_Customerpictures_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('customerpictures_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('customerpictures')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('customerpictures')->__('Item Information'),
          'title'     => Mage::helper('customerpictures')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('customerpictures/adminhtml_customerpictures_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}