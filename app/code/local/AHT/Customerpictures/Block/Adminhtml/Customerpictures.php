<?php
class AHT_Customerpictures_Block_Adminhtml_Customerpictures extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_customerpictures';
    $this->_blockGroup = 'customerpictures';
	if($this->getRequest()->getControllerName()=='adminhtml_winner')
		$this->_headerText = Mage::helper('customerpictures')->__('');
	else
		$this->_headerText = Mage::helper('customerpictures')->__('All Customer Pictures');
    $this->_addButtonLabel = Mage::helper('customerpictures')->__('Add Item');
    parent::__construct();
	$this->_removeButton('add');
	//$this->_removeHeaderText();
  }
}