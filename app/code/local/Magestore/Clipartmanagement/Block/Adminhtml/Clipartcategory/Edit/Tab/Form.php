<?php

class Magestore_Clipartmanagement_Block_Adminhtml_Clipartcategory_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('clipartmanagement_form', array('legend'=>Mage::helper('clipartmanagement')->__('Clipart information')));
     
	  $parent_cat = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->addFieldToFilter('parent_cat_id', 0)->toOptionArray();
	  $pricedata = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->addFieldToFilter('clipart_cat_id',Mage::registry('clipartmanagement_data')->getParent_cat_id());
	  $pricedataarray = $pricedata->getdata() ;
	  $priceval = $pricedataarray[0]['price'];
	  $sel_cat = array(
                  'value'     => 0,
                  'label'     => Mage::helper('clipartmanagement')->__('Select Parent Category (Root Category)'),
              );
			  
	  array_unshift($parent_cat, $sel_cat);	
	  	  
      /*$fieldset->addField('parent_cat_id', 'select', array(
          'label'     => Mage::helper('clipartmanagement')->__('Parent Category'),
		  'class'     => 'required-entry',
          'required'  => 'true',		  
          'name'      => 'parent_cat_id',
		  'disabled'  => ((Mage::registry('clipartmanagement_data')->getClipart_cat_id()) ? 'disabled' : ''),		  
          'values'    => $parent_cat,
		  'onchange'  =>"if(this.value>0){document.getElementById('price').value=0;document.getElementById('price').disabled=true;}else{document.getElementById('price').disabled=false;}",
      ));*/
	  
	  $fieldset->addField('category_name', 'text', array(
          'label'     => Mage::helper('clipartmanagement')->__('Clipart Category Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'category_name',
      ));
	  
	   $fieldset->addField('position', 'text', array(
          'label'     => Mage::helper('clipartmanagement')->__('Category Position'),
          'required'  => false,
          'name'      => 'position',
      ));
	  // added extra for clipart categroy pricing
	  /* commented by ajay*/ 
        /*$fieldset->addField('price', 'text', array(
          'label'     => Mage::helper('clipartmanagement')->__('Category Price['.$priceval.']'),
          'required'  => false,
          'name'      => 'price',
		  'id'=> 'price',
		   'value'    => $priceval,
		  'disabled'  => ((Mage::registry('clipartmanagement_data')->getParent_cat_id()== 0 ) ? '' : 'disabled'),		  
      ));*/
      if ( Mage::getSingleton('adminhtml/session')->getClipartManagementData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getClipartManagementData());
          Mage::getSingleton('adminhtml/session')->setClipartManagementData(null);
      } elseif ( Mage::registry('clipartmanagement_data') ) {
          $form->setValues(Mage::registry('clipartmanagement_data')->getData());
      }
      return parent::_prepareForm();
  }
} ?>
