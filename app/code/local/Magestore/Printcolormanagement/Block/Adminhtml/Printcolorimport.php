<?php
class Magestore_Printcolormanagement_Block_Adminhtml_Printcolorimport extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_printcolorimport';
    $this->_blockGroup = 'printcolormanagement';
    $this->_headerText = Mage::helper('printcolormanagement')->__('Import Printable Color');
    parent::__construct();
	$this->_removeButton('add');
  }
}?>