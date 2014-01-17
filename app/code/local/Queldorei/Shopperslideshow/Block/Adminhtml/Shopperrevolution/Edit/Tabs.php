<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Block_Adminhtml_Shopperrevolution_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('shopperrevolution_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('shopperslideshow')->__('Revolution Slide Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('shopperslideshow')->__('Revolution Slide Information'),
          'title'     => Mage::helper('shopperslideshow')->__('Revolution Slide Information'),
          'content'   => $this->getLayout()->createBlock('shopperslideshow/adminhtml_shopperrevolution_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}