<?php
class Magestore_Fontmanagement_Block_Adminhtml_Fontcategory extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_fontcategory';
    $this->_blockGroup = 'fontmanagement';
    $this->_headerText = Mage::helper('fontmanagement')->__('Font Category Management');
    $this->_addButtonLabel = Mage::helper('fontmanagement')->__('Add Font Category');
    parent::__construct();
  }
}?>