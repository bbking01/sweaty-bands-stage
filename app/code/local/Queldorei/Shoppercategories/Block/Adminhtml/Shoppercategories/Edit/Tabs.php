<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shoppercategories_Block_Adminhtml_Shoppercategories_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('shoppercategories_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('shoppercategories')->__('Scheme Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('shoppercategories')->__('Scheme Information'),
          'title'     => Mage::helper('shoppercategories')->__('Scheme Information'),
          'content'   => $this->getLayout()->createBlock('shoppercategories/adminhtml_shoppercategories_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}