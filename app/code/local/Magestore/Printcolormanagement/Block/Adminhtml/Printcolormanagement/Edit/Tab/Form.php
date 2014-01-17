<?php

class Magestore_Printcolormanagement_Block_Adminhtml_Printcolormanagement_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		?>
		<script type="text/javascript" src="<?php echo $this->getJsUrl() ?>jscolor/jquery-1.6.4.js"></script>
		<script type="text/javascript" src="<?php echo $this->getJsUrl() ?>jscolor/jscolor.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $this->getJsUrl() ?>jscolor/tabstyle.css">
		<script type="text/javascript">
			jQuery.noConflict(); 
		</script>
		<?php
		
		$form = new Varien_Data_Form();
		$this->setForm($form);
		
		$fieldset = $form->addFieldset('printcolormanagement_form', array('legend'=>Mage::helper('printcolormanagement')->__('Item information')));
		$fieldset->addField('color_name', 'text', array(
			'label'     => Mage::helper('printcolormanagement')->__('Color Name'),
			'class'     => 'required-entry',
			'required'  => true,
			'name'      => 'color_name',
		));
	/*	
		$fieldset->addField('color_code', 'text', array(
			'label'     => Mage::helper('printcolormanagement')->__('Color Code'),
			'class'     => 'required-entry',
			'required'  => true,
			'note'		=> 'Ex. 0XFFFFFF',
			'name'      => 'color_code',
		));*/
	
		$fieldset->addField('color_code', 'text', array(
			'label'     => Mage::helper('printcolormanagement')->__('Color Code'),
			'class'     => 'color',
			'required'  => true,
			'note'		=> 'Click to view Color Picker',
			'name'      => 'color_code',
		));
		
		$fieldset->addField('status', 'select', array(
			'label'     => Mage::helper('printcolormanagement')->__('Status'),
			'name'      => 'status',
			'values'    => array(
				array(
					'value'     => 1,
					'label'     => Mage::helper('printcolormanagement')->__('Enabled'),
				),
				array(
					'value'     => 2,
					'label'     => Mage::helper('printcolormanagement')->__('Disabled'),
				),
			),
		));       		
		

				 
		if ( Mage::getSingleton('adminhtml/session')->getPrintcolorManagementData() )
		{
		  $form->setValues(Mage::getSingleton('adminhtml/session')->getPrintcolorManagementData());
		  Mage::getSingleton('adminhtml/session')->setPrintcolorManagementData(null);
		} elseif ( Mage::registry('printcolormanagement_data') ) {
		  $form->setValues(Mage::registry('printcolormanagement_data')->getData());
		}
		return parent::_prepareForm();
	}
  
}?>