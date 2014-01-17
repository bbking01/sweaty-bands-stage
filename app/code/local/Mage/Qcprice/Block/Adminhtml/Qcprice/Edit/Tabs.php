<?php

class Mage_Qcprice_Block_Adminhtml_Qcprice_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('qcprice_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('qcprice')->__('Q&C Price Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('qcprice')->__('Q&C Price Information'),
          'title'     => Mage::helper('qcprice')->__('Q&C Price Information'),
          'content'   => $this->getLayout()->createBlock('qcprice/adminhtml_qcprice_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}