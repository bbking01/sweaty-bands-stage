<?php

class Mage_Qcprice_Block_Adminhtml_Qcprice_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('qcprice_form', array('legend'=>Mage::helper('qcprice')->__('Item information')));
		
		$fieldset->addField('quantity_range_id', 'select', array(
		  'label'     => Mage::helper('qcprice')->__('Quantity Range'),
		  'name'      => 'quantity_range_id',
		  'values'    => $this->_getQuantityCollection(),
		));
		
		$fieldset->addField('colors_counter_id', 'select', array(
		  'label'     => Mage::helper('qcprice')->__('Colors Counter'),
		  'name'      => 'colors_counter_id',
		  'values'    => $this->_getColorsCollection(),
		));
		
		$fieldset->addField('price', 'text', array(
          'label'     => Mage::helper('colors')->__('Price'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'price',
      ));
		
		if ( Mage::getSingleton('adminhtml/session')->getQcpriceData() )
		{
		  $form->setValues(Mage::getSingleton('adminhtml/session')->getQcpriceData());
		  Mage::getSingleton('adminhtml/session')->setQcpriceData(null);
		} elseif ( Mage::registry('qcprice_data') ) {
		  $form->setValues(Mage::registry('qcprice_data')->getData());
		}
		return parent::_prepareForm();
	}
  
	protected function _getQuantityCollection()
	{
		$collection = Mage::getModel('qrange/qrange')->getCollection()->addFieldToSelect("qrange_id","value");
		$collection->getSelect()->columns(new Zend_Db_Expr("Concat(quantity_range_from,' - ',quantity_range_to) as label"));
		return $collection->getData();
	}
	
	protected function _getColorsCollection()
	{
		$collection = Mage::getModel('colors/colors')->getCollection()
													->addFieldToSelect("colors_id","value")
													->addFieldToSelect("colors_counter","label");
		return $collection->getData();
	}
}