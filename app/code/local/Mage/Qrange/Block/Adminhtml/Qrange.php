<?php
class Mage_Qrange_Block_Adminhtml_Qrange extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_qrange';
    $this->_blockGroup = 'qrange';
    $this->_headerText = Mage::helper('qrange')->__('Quantity Range Manager');
    $this->_addButtonLabel = Mage::helper('qrange')->__('Add Quantity Range');
    parent::__construct();
  }
}