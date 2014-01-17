<?php

class Magestore_Fontmanagement_Block_Adminhtml_Addfont_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('addfont_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('fontmanagement')->__('Font Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('fontmanagement')->__('Font Information'),
          'title'     => Mage::helper('fontmanagement')->__('Font Information'),
          'content'   => $this->getLayout()->createBlock('fontmanagement/adminhtml_addfont_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}