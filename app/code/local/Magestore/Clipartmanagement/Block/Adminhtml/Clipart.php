<?php
class Magestore_Clipartmanagement_Block_Adminhtml_Clipart extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_clipart';
    $this->_blockGroup = 'clipartmanagement';
    $this->_headerText = Mage::helper('clipartmanagement')->__('Clipart Management');
    $this->_addButtonLabel = Mage::helper('clipartmanagement')->__('Add Clipart');
    parent::__construct();
  }
}?>