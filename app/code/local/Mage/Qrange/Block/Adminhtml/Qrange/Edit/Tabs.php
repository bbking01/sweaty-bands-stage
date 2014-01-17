<?php

class Mage_Qrange_Block_Adminhtml_Qrange_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('qrange_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('qrange')->__('Quantity Range Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('qrange')->__('Quantity Range Information'),
          'title'     => Mage::helper('qrange')->__('Quantity Range Information'),
          'content'   => $this->getLayout()->createBlock('qrange/adminhtml_qrange_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}