<?php
class Magestore_Clipartmanagement_Block_Adminhtml_Clipartimport extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_clipartimport';
    $this->_blockGroup = 'clipartmanagement';
    $this->_headerText = Mage::helper('clipartmanagement')->__('Import Cliparts');
    parent::__construct();
	$this->_removeButton('add');
  }
}?>