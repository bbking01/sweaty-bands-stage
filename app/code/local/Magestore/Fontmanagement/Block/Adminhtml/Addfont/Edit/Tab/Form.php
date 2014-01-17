<?php

class Magestore_Fontmanagement_Block_Adminhtml_Addfont_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
	  $category = Mage::getModel('fontmanagement/fontcategory')->getCollection()->load()->toOptionArray();
	  $sel_cat = array(
                  'value'     => '',
                  'label'     => Mage::helper('fontmanagement')->__('Select Category'),
              );
			  
	  array_unshift($category, $sel_cat);	
	  
	  
	  $note = '(Upload only .swf files)';
	  $getFont_file = Mage::registry('fontmanagement_data')->getFont_file();	  
	  
	  $filepath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'designtool/';	
	  $filepath_fla = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'designtool/';	
	  
	  $font_file_exists = file_exists('./media/font/'. $getFont_file);
	  if( $getFont_file &&  $font_file_exists){
      		$note .= '&nbsp;&nbsp;&nbsp;<a href="'.Mage::getBaseUrl('media') .'/font/' .$getFont_file.'" target="_blank">View current file</a>';
      }
	  
	  
	  $img = '(Upload only .jpg,.gif,.png files)'; 
	  $getFont_image = Mage::registry('fontmanagement_data')->getFont_image();
	  $image_file_exists = file_exists('./media/font/images/'. $getFont_image);
	  if( $getFont_image && $image_file_exists ){
      		$img .= '&nbsp;&nbsp;&nbsp;<img src="'.Mage::getBaseUrl('media') .'/font/images/'. $getFont_image.'" border="0" />';
      }
	  
	  	  
      $fieldset = $form->addFieldset('fontmanagement_form', array('legend'=>Mage::helper('fontmanagement')->__('Font information')));
     
      $fieldset->addField('font_name', 'text', array(
          'label'     => Mage::helper('fontmanagement')->__('Font Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'font_name',
      ));  
	  	
      $fieldset->addField('font_file', 'file', array(
          'label'     => Mage::helper('fontmanagement')->__('Upload Font'),
		  'note'      => $note,
		  'class'     => (( $getFont_file && $font_file_exists ) ? '' : 'required-entry'),
          'required'  => (( $getFont_file && $font_file_exists ) ? false : true),
          'name'      => 'font_file',
	  ));
		
      $fieldset->addField('font_category_id', 'select', array(
          'label'     => Mage::helper('fontmanagement')->__('Font Category'),
		  'class'     => 'required-entry',
          'required'  => true,		  
          'name'      => 'font_category_id',
          'values'    => $category,
		  'note'		=> '
		   <br/><p  class="fontrswf" >How to convert font from TTF/OTF to SWF format?</p><br/>
		  <span class="dlnk"><b>Sample FLA File:</b><a href="'.$filepath.'download.php?fla_name=Algerian.fla">Download Algerian fla</a></span><br/><br/>
		  <ul class="listul">
		  <li class="fonrmngmt"><span>Open the FLA, save it as your preferred name (If you want to generate "Arial" font then save this FLA file as "Arial")</span></li>
		  <li class="fonrmngmt"><span>Open the Library, if it is not visible, press F11</span></li>
		  
		  <li class="fonrmngmt"><span>Double click the first font&nbsp;symbol      ("font1") in the&nbsp;library, which opens the "Font Symbol      Properties" dialog box.</span></li>
		  
		  <li class="fonrmngmt"><span> Change the font name in font dropdown in "Font Symbol Properties" dialog box. </span></li>
		  
		  <li class="fonrmngmt"><span>Keep the style as it is. If the font symbol style is bold then keep it bold, if italic then keep it italic and if it is bolditalic then keep it bolditalic. and click the OK button.</span></li>
		  
		  <li class="fonrmngmt"><span>Repeat steps 2-4 for each font symbol.</span></li>
		  
		  <li class="fonrmngmt"><span> Now compile the swf by selecting Control -> Test Movie, you will get one name in output panel (traced 8 times). Copy the name.</span></li>
		  
		  <li class="fonrmngmt"><span> Compiled swf will be created in the same folder where your FLA file is.</span></li>
		  </ul>',
      ));
	
			
     
      if ( Mage::getSingleton('adminhtml/session')->getFontManagementData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getFontManagementData());
          Mage::getSingleton('adminhtml/session')->setFontManagementData(null);
      } elseif ( Mage::registry('fontmanagement_data') ) {
          $form->setValues(Mage::registry('fontmanagement_data')->getData());
      }
      return parent::_prepareForm();
  }
  
}?>