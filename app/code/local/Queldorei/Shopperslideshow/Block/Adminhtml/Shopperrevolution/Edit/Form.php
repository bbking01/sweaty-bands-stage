<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Block_Adminhtml_Shopperrevolution_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form(array(
	          'id' => 'edit_form',
	          'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
	          'method' => 'post',
	          'enctype' => 'multipart/form-data'
	       )
      );

      $form->setUseContainer(true);
      $this->setForm($form);
      return parent::_prepareForm();
  }
}