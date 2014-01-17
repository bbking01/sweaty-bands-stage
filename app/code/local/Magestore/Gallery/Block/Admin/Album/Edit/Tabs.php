<?php

class Magestore_Gallery_Block_Admin_Album_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('album_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('gallery')->__('Category Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('gallery')->__('Category Information'),
          'title'     => Mage::helper('gallery')->__('Category Information'),
          'content'   => $this->getLayout()->createBlock('gallery/admin_album_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}