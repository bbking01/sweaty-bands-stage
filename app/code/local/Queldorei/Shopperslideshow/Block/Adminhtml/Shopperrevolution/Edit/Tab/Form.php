<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Block_Adminhtml_Shopperrevolution_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{

		$model = Mage::registry('shopperslideshow_shopperrevolution');

		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('shopperslideshow_form', array('legend' => Mage::helper('shopperslideshow')->__('Revolution Slide information')));

		$fieldset->addField('store_id', 'multiselect', array(
			'name' => 'stores[]',
			'label' => Mage::helper('shopperslideshow')->__('Store View'),
			'title' => Mage::helper('shopperslideshow')->__('Store View'),
			'required' => true,
			'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
		));

		$fieldset->addField('transition', 'select', array(
			'label' => Mage::helper('shopperslideshow')->__('Transition'),
			'name' => 'transition',
			'values' => array(
				array(
					'value' => 'boxslide',
					'label' => Mage::helper('shopperslideshow')->__('boxslide'),
				),
				array(
					'value' => 'boxfade',
					'label' => Mage::helper('shopperslideshow')->__('boxfade'),
				),
				array(
					'value' => 'slotzoom-horizontal',
					'label' => Mage::helper('shopperslideshow')->__('slotzoom-horizontal'),
				),
				array(
					'value' => 'slotslide-horizontal',
					'label' => Mage::helper('shopperslideshow')->__('slotslide-horizontal'),
				),
				array(
					'value' => 'slotfade-horizontal',
					'label' => Mage::helper('shopperslideshow')->__('slotfade-horizontal'),
				),
				array(
					'value' => 'slotzoom-vertical',
					'label' => Mage::helper('shopperslideshow')->__('slotzoom-vertical'),
				),
				array(
					'value' => 'slotslide-vertical',
					'label' => Mage::helper('shopperslideshow')->__('slotslide-vertical'),
				),
				array(
					'value' => 'slotfade-vertical',
					'label' => Mage::helper('shopperslideshow')->__('slotfade-vertical'),
				),
				array(
					'value' => 'curtain-1',
					'label' => Mage::helper('shopperslideshow')->__('curtain-1'),
				),
				array(
					'value' => 'curtain-2',
					'label' => Mage::helper('shopperslideshow')->__('curtain-2'),
				),
				array(
					'value' => 'curtain-3',
					'label' => Mage::helper('shopperslideshow')->__('curtain-3'),
				),
				array(
					'value' => 'slideleft',
					'label' => Mage::helper('shopperslideshow')->__('slideleft'),
				),
				array(
					'value' => 'slideright',
					'label' => Mage::helper('shopperslideshow')->__('slideright'),
				),
				array(
					'value' => 'slideup',
					'label' => Mage::helper('shopperslideshow')->__('slideup'),
				),
				array(
					'value' => 'slidedown',
					'label' => Mage::helper('shopperslideshow')->__('slidedown'),
				),
				array(
					'value' => 'fade',
					'label' => Mage::helper('shopperslideshow')->__('fade'),
				),
				array(
					'value' => 'random',
					'label' => Mage::helper('shopperslideshow')->__('random'),
				),
				array(
					'value' => 'slidehorizontal',
					'label' => Mage::helper('shopperslideshow')->__('slidehorizontal'),
				),
				array(
					'value' => 'slidevertical',
					'label' => Mage::helper('shopperslideshow')->__('slidevertical'),
				),
				array(
					'value' => 'papercut',
					'label' => Mage::helper('shopperslideshow')->__('papercut'),
				),
				array(
					'value' => 'flyin',
					'label' => Mage::helper('shopperslideshow')->__('flyin'),
				),
				array(
					'value' => 'turnoff',
					'label' => Mage::helper('shopperslideshow')->__('turnoff'),
				),
				array(
					'value' => 'cube',
					'label' => Mage::helper('shopperslideshow')->__('cube'),
				),
				array(
					'value' => '3dcurtain-vertical',
					'label' => Mage::helper('shopperslideshow')->__('3dcurtain-vertical'),
				),
				array(
					'value' => '3dcurtain-horizontal',
					'label' => Mage::helper('shopperslideshow')->__('3dcurtain-horizontal'),
				),
			),
			'note' => 'The appearance transition of this slide',
		));

		$fieldset->addField('masterspeed', 'text', array(
			'label' => Mage::helper('shopperslideshow')->__('Masterspeed'),
			'required' => false,
			'name' => 'masterspeed',
			'note' => 'Set the Speed of the Slide Transition. Default 300, min:100 max:2000.'
		));
		$fieldset->addField('slotamount', 'text', array(
			'label' => Mage::helper('shopperslideshow')->__('Slotamount'),
			'required' => false,
			'name' => 'slotamount',
			'note' => 'The number of slots or boxes the slide is divided into. If you use boxfade, over 7 slots can be juggy.'
		));
		$fieldset->addField('link', 'text', array(
			'label' => Mage::helper('shopperslideshow')->__('Slide Link'),
			'required' => false,
			'name' => 'link',
		));

		$data = array();
		$out = '';
		if (Mage::getSingleton('adminhtml/session')->getShopperrevolutionData()) {
			$data = Mage::getSingleton('adminhtml/session')->getShopperrevolutionData();
		} elseif (Mage::registry('shopperrevolution_data')) {
			$data = Mage::registry('shopperrevolution_data')->getData();
		}

		if (!empty($data['image'])) {
			$url = Mage::getBaseUrl('media') . $data['image'];
			$out = '<br/><center><a href="' . $url . '" target="_blank" id="imageurl">';
			$out .= "<img src=" . $url . " width='150px' />";
			$out .= '</a></center>';
		}

		$fieldset->addField('image', 'file', array(
			'label' => Mage::helper('shopperslideshow')->__('Image'),
			'required' => false,
			'name' => 'image',
			'note' => $out,
		));

		$out = '';
		if (!empty($data['thumb'])) {
			$url = Mage::getBaseUrl('media') . $data['thumb'];
			$out = '<br/><center><a href="' . $url . '" target="_blank" id="imageurl">';
			$out .= "<img src=" . $url . " width='150px' />";
			$out .= '</a></center>';
		}

		$fieldset->addField('thumb', 'file', array(
			'label' => Mage::helper('shopperslideshow')->__('Slide thumb'),
			'required' => false,
			'name' => 'thumb',
			'note' => 'An Alternative Source for thumbs. If not defined a copy of the background image will be used in resized form. ' . $out,
		));

		$fieldset->addField('text', 'textarea', array(
			'label'     => Mage::helper('shopperslideshow')->__('Slide Content'),
			'required'  => false,
			'name'      => 'text',
		));

		$fieldset->addField('status', 'select', array(
			'label' => Mage::helper('shopperslideshow')->__('Status'),
			'name' => 'status',
			'values' => array(
				array(
					'value' => 1,
					'label' => Mage::helper('shopperslideshow')->__('Enabled'),
				),
				array(
					'value' => 2,
					'label' => Mage::helper('shopperslideshow')->__('Disabled'),
				),
			),
		));

		$fieldset->addField('sort_order', 'text', array(
			'label' => Mage::helper('shopperslideshow')->__('Sort Order'),
			'required' => false,
			'name' => 'sort_order',
		));

		if (Mage::getSingleton('adminhtml/session')->getShopperrevolutionData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getShopperrevolutionData());
			Mage::getSingleton('adminhtml/session')->getShopperrevolutionData(null);
		} elseif (Mage::registry('shopperrevolution_data')) {
			$form->setValues(Mage::registry('shopperrevolution_data')->getData());
		}
		return parent::_prepareForm();
	}
}