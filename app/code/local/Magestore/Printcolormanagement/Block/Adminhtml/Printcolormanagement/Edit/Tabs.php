<?php

class Magestore_Printcolormanagement_Block_Adminhtml_Printcolormanagement_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('printcolormanagement_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('printcolormanagement')->__('Printable Color Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('printcolormanagement')->__('Printable Color Information'),
          'title'     => Mage::helper('printcolormanagement')->__('Printable Color  Information'),
          'content'   => $this->getLayout()->createBlock('printcolormanagement/adminhtml_printcolormanagement_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}