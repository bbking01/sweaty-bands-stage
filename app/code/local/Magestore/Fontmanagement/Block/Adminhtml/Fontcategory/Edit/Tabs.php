<?php

class Magestore_Fontmanagement_Block_Adminhtml_Fontcategory_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('fontcategory_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('fontmanagement')->__('Font Category Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('fontmanagement')->__('Font Category Information'),
          'title'     => Mage::helper('fontmanagement')->__('Font Category Information'),
          'content'   => $this->getLayout()->createBlock('fontmanagement/adminhtml_fontcategory_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}