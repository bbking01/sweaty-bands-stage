<?php
/**
 * @copyright   Copyright (c) 2009-2011 Amasty (http://www.amasty.com)
 */
class Amasty_Perm_Block_Adminhtml_Additional extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('permissions_user');
        
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('user_');
        
        $hlp = Mage::helper('amperm');
        
        $fieldset = $form->addFieldset('Additional', array('legend'=> $hlp->__('Additional')));
        $fieldset->addField('description', 'editor', array(
          'name'      => 'description',
          'label'     => $hlp->__('Dealer Description'),
          'title'     => $hlp->__('Dealer Description'),
          'style' 	  => 'width:700px; height:200px;', 
		  'wysiwyg'   => true, 
       	  'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
        )); 

        $fieldset->addField('emails', 'textarea', array(
          'name'      => 'emails',
          'label'     => $hlp->__('Send copy of email to'),
          'title'     => $hlp->__('Send copy of email to'),
          'note'       => $hlp->__('Comma separated list of email addresses'),
          'style' 	  => 'width:400px; height:100px;', 
        )); 
        
        
        $data = $model->getData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    } 
}