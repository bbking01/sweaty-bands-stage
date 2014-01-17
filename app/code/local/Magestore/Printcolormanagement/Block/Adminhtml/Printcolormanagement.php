<?php
class Magestore_Printcolormanagement_Block_Adminhtml_Printcolormanagement extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_printcolormanagement';
    $this->_blockGroup = 'printcolormanagement';
    $this->_headerText = Mage::helper('printcolormanagement')->__('Color Management');
    $this->_addButtonLabel = Mage::helper('printcolormanagement')->__('Add Printable Color');
    parent::__construct();
  }
}?>