
<?php

class Magestore_Clipartmanagement_Block_Adminhtml_Clipart_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{ 
  protected function _prepareForm()
  {
	  $form = new Varien_Data_Form();
      $this->setForm($form);
	  $category = array();
	  $category_list = array();
	  /*$parent_category = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->addFieldToFilter('parent_cat_id', 0)->toOptionArray();
	  	  
	 foreach($parent_category as $parent)	 
	 {	 		 
	 	$category[$parent['label']][] = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->addFieldToFilter('parent_cat_id', $parent['value'])->toOptionArray();
	 }
	 
	 $category_title = array_keys($category);	
	 $i = 0;
	 foreach($category as $cate)
	 {	 	
	 	$main_category = $category_title[$i];
	 	foreach($cate as $cat)
		{
			foreach($cat as $c)
			{
				$c['label'] =  $main_category." ->  ".$c['label'];			
				$category_list[] = $c;
			}
		}
		$i++;
	 }*/
	  /* edited by ajay for single level category*/
	  $category_list = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->toOptionArray();
	  $sel_cat = array(
                  'value'     => '',
                  'label'     => Mage::helper('clipartmanagement')->__('Select Category'),
              );
	 array_unshift($category_list, $sel_cat);	
	 

	  
	  $img = '(Upload only .swf file)';
	  if( Mage::registry('clipartmanagement_data')->getClipart_image() ){
      		$imgpath  = Mage::getBaseUrl('media') .'clipart/images/'. Mage::registry('clipartmanagement_data')->getClipart_image();
			
			$img .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="200" height="200">
  <param name="movie" value="'.$imgpath.'" />
  <param name="quality" value="high" />
  <embed src="'.$imgpath.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="200" height="200"></embed>
</object>';
      }
	  
	  	  
      $fieldset = $form->addFieldset('clipartmanagement_form', array('legend'=>Mage::helper('clipartmanagement')->__('Clipart information')));
     
      $fieldset->addField('clipart_name', 'text', array(
          'label'     => Mage::helper('clipartmanagement')->__('Clipart Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'clipart_name',
      ));  
		
      $fieldset->addField('c_category_id', 'select', array(
          'label'     => Mage::helper('clipartmanagement')->__('Clipart Category'),
		  'class'     => 'required-entry',
          'required'  => true,		  
          'name'      => 'c_category_id',
          'values'    => $category_list,
      ));
	  
	   $fieldset->addField('position', 'text', array(
          'label'     => Mage::helper('clipartmanagement')->__('Position'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'position',
      )); 
	  
	 /* $fieldset->addField('clipart_category_id', 'select', array(
          'label'     => Mage::helper('clipartmanagement')->__('Clipart Sub Category'),
		  'class'     => 'required-entry',
          'required'  => true,		  
          'name'      => 'clipart_category_id',
          'values'    => $sub_category,
      ));*/
	  
	   $fieldset->addField('clipart_image', 'file', array(
          'label'     => Mage::helper('clipartmanagement')->__('Clipart Image'),
		  'note'      => $img,
		  'class'     => ((Mage::registry('clipartmanagement_data')->getClipart_image()) ? '' : 'required-entry'),
          'required'  => ((Mage::registry('clipartmanagement_data')->getClipart_image()) ? false : true),
          'name'      => 'clipart_image',
	  ));	
			
     
      if ( Mage::getSingleton('adminhtml/session')->getClipartManagementData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getClipartManagementData());
          Mage::getSingleton('adminhtml/session')->setClipartManagementData(null);
      } elseif ( Mage::registry('clipartmanagement_data') ) {
          $form->setValues(Mage::registry('clipartmanagement_data')->getData());
      }
      return parent::_prepareForm();
  }
}?>
