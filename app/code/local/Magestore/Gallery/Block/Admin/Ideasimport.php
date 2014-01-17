<?php
class Magestore_Gallery_Block_Admin_Ideasimport extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {    
	$this->_controller = 'admin_ideasimport';
    $this->_blockGroup = 'gallery';
    $this->_headerText = Mage::helper('gallery')->__('Import Ideas Manager');   	
    parent::__construct();
	$this->_removeButton('add');
  }
}?>