<?php

class MW_RewardPoints_Block_Adminhtml_Sellproducts_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form_products = new Varien_Data_Form(array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/saveSell', array('id' => $this->getRequest()->getParam('id'))),
                                      'method' => 'post',
        							  'enctype' => 'multipart/form-data'
                                   )
      );

      $form_products->setUseContainer(true);
      $this->setForm($form_products);
      return parent::_prepareForm();
  }
}