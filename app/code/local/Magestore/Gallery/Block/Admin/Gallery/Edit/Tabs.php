<?php

class Magestore_Gallery_Block_Admin_Gallery_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('gallery_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('gallery')->__('Ideas Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('gallery')->__('Ideas Information'),
          'title'     => Mage::helper('gallery')->__('Ideas Information'),
          'content'   => $this->getLayout()->createBlock('gallery/admin_gallery_edit_tab_form')->toHtml(),
      ));
	  
	 $id      = $this->getRequest()->getParam('id');
	 
	 
	if($id != "")
	{
		 $this->addTab('customize_section', array(
			  'label'     => Mage::helper('gallery')->__('Customize Ideas'),
			  'title'     => Mage::helper('gallery')->__('Customize Ideas'),
			  'content'   => $this->getLayout()->createBlock('gallery/admin_gallery_edit_tab_tool')->toHtml(),
			  //'url'		  => $this->getUrl('*/*/customize', array('_current' => true)),	
			  //'class'     => 'ajax',
		  ));
	  
	  }
      return parent::_beforeToHtml();
  }
}