<?php
class Magestore_Fontmanagement_Block_Adminhtml_Addfont extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_addfont';
    $this->_blockGroup = 'fontmanagement';
    $this->_headerText = Mage::helper('fontmanagement')->__('Font Management');
    $this->_addButtonLabel = Mage::helper('fontmanagement')->__('Add Font');
    parent::__construct();
  }
}?>