<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Block_Adminhtml_Shopperslideshow_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {

	  $model = Mage::registry('shopperslideshow_shopperslideshow');

      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('shopperslideshow_form', array('legend'=>Mage::helper('shopperslideshow')->__('Flexslider Slide information')));

		$fieldset->addField('store_id', 'multiselect', array(
		      'name'      => 'stores[]',
		      'label'     => Mage::helper('shopperslideshow')->__('Store View'),
		      'title'     => Mage::helper('shopperslideshow')->__('Store View'),
		      'required'  => true,
		      'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
		  ));

      $fieldset->addField('slide_align', 'select', array(
          'label'     => Mage::helper('shopperslideshow')->__('Text Align'),
          'name'      => 'slide_align',
          'values'    => array(
              array(
                  'value'     => 'left',
                  'label'     => Mage::helper('shopperslideshow')->__('Left'),
              ),
              array(
                  'value'     => 'right',
                  'label'     => Mage::helper('shopperslideshow')->__('Right'),
              ),
              array(
                  'value'     => 'center',
                  'label'     => Mage::helper('shopperslideshow')->__('Center'),
              ),
          ),
      ));

      $fieldset->addField('slide_title', 'text', array(
          'label'     => Mage::helper('shopperslideshow')->__('Title'),
          'required'  => false,
          'name'      => 'slide_title',
      ));
      $fieldset->addField('slide_text', 'textarea', array(
          'label'     => Mage::helper('shopperslideshow')->__('Text'),
          'required'  => false,
          'name'      => 'slide_text',
      ));
      $fieldset->addField('slide_button', 'text', array(
          'label'     => Mage::helper('shopperslideshow')->__('Button Text'),
          'required'  => false,
          'name'      => 'slide_button',
      ));
      $fieldset->addField('slide_width', 'text', array(
          'label'     => Mage::helper('shopperslideshow')->__('Content width'),
          'required'  => false,
          'name'      => 'slide_width',
      ));
	  
	  $fieldset->addField('slide_link', 'text', array(
          'label'     => Mage::helper('shopperslideshow')->__('Link'),
          'required'  => false,
          'name'      => 'slide_link',
      ));


	  $data = array();
	  $out = '';
	  if ( Mage::getSingleton('adminhtml/session')->getShopperslideshowData() )
		{
			$data = Mage::getSingleton('adminhtml/session')->getShopperslideshowData();
		} elseif ( Mage::registry('shopperslideshow_data') ) {
			$data = Mage::registry('shopperslideshow_data')->getData();
		}

	  if ( !empty($data['image']) ) {
		  $url = Mage::getBaseUrl('media') . $data['image'];
          $out = '<br/><center><a href="' . $url . '" target="_blank" id="imageurl">';
		  $out .= "<img src=" . $url . " width='150px' />";
		  $out .= '</a></center>';
	  }

      $fieldset->addField('image', 'file', array(
          'label'     => Mage::helper('shopperslideshow')->__('Image for PC'),
          'required'  => false,
          'name'      => 'image',
	      'note' => 'Image used for PC screens (larger than 768) '.$out,
	  ));

      $out = '';
      if ( !empty($data['small_image']) ) {
		  $url = Mage::getBaseUrl('media') . $data['small_image'];
          $out = '<br/><center><a href="' . $url . '" target="_blank" id="imageurl">';
		  $out .= "<img src=" . $url . " width='150px' />";
		  $out .= '</a></center>';
	  }

      $fieldset->addField('small_image', 'file', array(
          'label'     => Mage::helper('shopperslideshow')->__('Small Image for iPhone'),
          'required'  => false,
          'name'      => 'small_image',
	      'note' => 'Small image used for small screens (less than 768) '.$out,
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('shopperslideshow')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('shopperslideshow')->__('Enabled'),
              ),
              array(
                  'value'     => 2,
                  'label'     => Mage::helper('shopperslideshow')->__('Disabled'),
              ),
          ),
      ));

      $fieldset->addField('sort_order', 'text', array(
            'label'     => Mage::helper('shopperslideshow')->__('Sort Order'),
            'required'  => false,
            'name'      => 'sort_order',
        ));

      if ( Mage::getSingleton('adminhtml/session')->getShopperslideshowData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getShopperslideshowData());
          Mage::getSingleton('adminhtml/session')->getShopperslideshowData(null);
      } elseif ( Mage::registry('shopperslideshow_data') ) {
          $form->setValues(Mage::registry('shopperslideshow_data')->getData());
      }
      return parent::_prepareForm();
  }
}