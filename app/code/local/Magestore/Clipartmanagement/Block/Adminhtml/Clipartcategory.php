<?php
class Magestore_Clipartmanagement_Block_Adminhtml_Clipartcategory extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_clipartcategory';
    $this->_blockGroup = 'clipartmanagement';
    $this->_headerText = Mage::helper('clipartmanagement')->__('Clipart Category Management');
    $this->_addButtonLabel = Mage::helper('clipartmanagement')->__('Add Clipart Category');
    parent::__construct();
  }
}?>