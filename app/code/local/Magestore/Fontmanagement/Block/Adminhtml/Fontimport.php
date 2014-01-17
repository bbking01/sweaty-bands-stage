<?php
class Magestore_Fontmanagement_Block_Adminhtml_Fontimport extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_fontimport';
    $this->_blockGroup = 'fontmanagement';
    $this->_headerText = Mage::helper('fontmanagement')->__('Import Fonts');
    parent::__construct();
	$this->_removeButton('add');
  }
}?>