<?php
class Magestore_Gallery_Block_Admin_Album extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'admin_album';
    $this->_blockGroup = 'gallery';
    $this->_headerText = Mage::helper('gallery')->__('Ideas Category Manager');
    $this->_addButtonLabel = Mage::helper('gallery')->__('Add Ideas Category');
	/*$this->_addButton('sort', array(
    	'label'     => Mage::helper('gallery')->__('Save order'),
    	'onclick'   => 'saveSort();',
    	'class'     => 'button',
    ));*/
    parent::__construct();
  }
  
}