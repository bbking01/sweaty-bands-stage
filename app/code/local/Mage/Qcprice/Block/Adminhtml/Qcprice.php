<?php
class Mage_Qcprice_Block_Adminhtml_Qcprice extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_qcprice';
    $this->_blockGroup = 'qcprice';
    $this->_headerText = Mage::helper('qcprice')->__('Q&C Price Manager');
    $this->_addButtonLabel = Mage::helper('qcprice')->__('Add Price Combination');
    parent::__construct();
  }
}