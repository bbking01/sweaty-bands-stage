<?php
class Mage_Colors_Block_Adminhtml_Colors extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_colors';
    $this->_blockGroup = 'colors';
    $this->_headerText = Mage::helper('colors')->__('Colors Counter Manager');
    $this->_addButtonLabel = Mage::helper('colors')->__('Add Colors Counter');
    parent::__construct();
  }
}