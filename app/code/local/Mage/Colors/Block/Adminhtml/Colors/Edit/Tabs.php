<?php

class Mage_Colors_Block_Adminhtml_Colors_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('colors_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('colors')->__('Color Counter Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('colors')->__('Color Counter Information'),
          'title'     => Mage::helper('colors')->__('Color Counter Information'),
          'content'   => $this->getLayout()->createBlock('colors/adminhtml_colors_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}