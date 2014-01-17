<?php
include('app/Mage.php');
Mage::App('default'); //might be "default"


$sub_category = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->addFieldToFilter('parent_cat_id', 1)->toOptionArray();
	  $sel_cat = array(
                  'value'     => '',
                  'label'     => Mage::helper('clipartmanagement')->__('Select Sub Category'),
              );
			  
	  array_unshift($sub_category, $sel_cat);
	  echo  $sub_category;
	  exit;
	 ?>