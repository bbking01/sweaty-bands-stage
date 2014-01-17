<?php
/**
 * Unirgy_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_StoreLocator
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_StoreLocator
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_StoreLocator_Block_Adminhtml_Location_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $hlp = Mage::helper('ustorelocator');
        $this->setForm($form);
        $data = array();
        if (Mage::registry('location_data')) {
            $data = Mage::registry('location_data')->getData();
        }

        $fieldset = $form->addFieldset('location_form', array(
            'legend'=>$hlp->__('Store Location Info')
        ));

        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => $hlp->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('address_display', 'textarea', array(
            'name'      => 'address_display',
            'label'     => $hlp->__('Address to be displayed'),
            'class'     => 'required-entry',
            'style'     => 'height:50px',
            'required'  => true,
            'note'      => $hlp->__('This address will be shown to visitor and should have multiple lines formatting'),
        ));

        $fieldset->addField('phone', 'text', array(
            'name'      => 'phone',
            'label'     => $hlp->__('Phone'),
        ));

        $fieldset->addField('website_url', 'text', array(
            'name'      => 'website_url',
            'label'     => $hlp->__('Website URL / Email'),
            'note'      => $hlp->__('For websites URL please start with http://'),
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $values = Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false);
            $fieldset->addField('stores', 'multiselect', array(
                'name'      => 'stores[]',
                'label'     => Mage::helper('cms')->__('Store View'),
                'title'     => Mage::helper('cms')->__('Store View'),
                'values'    => $values,
            ));
        }

        $fieldset->addField(
            'country', 'select',
            array(
                'name'  => 'country',
                'label' => $hlp->__("Select location country"),
                'values'=> Mage::getModel('adminhtml/system_config_source_country')->toOptionArray()
        ));

        $fieldset->addField(
            'product_types', 'text',
            array(
                 'name' => 'product_types',
                 'label'=> $hlp->__("Store type"),
                 'note' => $hlp->__("Comma separated list of product types sold on this location.")
        ));

        $fieldset->addField(
            'is_featured',
            'select',
            array(
                'name'  => 'is_featured',
                'label' => $hlp->__("This is featured location"),
                'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
            )
        );

        $fieldset->addField('notes', 'textarea', array(
            'name'      => 'notes',
            'style'     => 'height:50px',
            'label'     => $hlp->__('Notes'),
        ));

        $fieldset = $form->addFieldset(
            'map_settings',
             array(
               'legend' => $hlp->__("Map settings")
             )
        );
        $fieldset->addField(
            'icon',
            'image',
            array(
                'name'  => 'icon',
                'label' => $hlp->__("Custom icon image"),
                'note'  => $hlp->__("Allowed file type: <strong>PNG</strong>.<br/>For best quality provide image with dimensions close to default Google icons - width 20px, height 34px.<br/>Maximum allowed size <strong>100px by 100px</strong>")
            )
        );

        $fieldset->addField(
            'use_label',
            'select',
            array(
                'label' => $hlp->__("Add sequence label to marker?"),
                'name'  => 'use_label',
                'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
                'note'  => $this->__("This setting is used only when no custom icon is provided.")
            )
        );

        $fieldset = $form->addFieldset('geo_form', array(
            'legend'=>$hlp->__('Geo Location')
        ));

        $fieldset->addField('address', 'textarea', array(
            'name'      => 'address',
            'style'     => 'height:50px',
            'label'     => $hlp->__('Address for geo location'),
            'note'      => $hlp->__('This address will be used to calculate latitude and longitude, free format is allowed.<br/>If left empty, will be copied from address to be displayed.'),
        ));

        $fieldset->addField('latitude', 'text', array(
             'name'      => 'latitude',
             'label'     => $hlp->__('Latitude'),
        ));
        
        $fieldset->addField('longitude', 'text', array(
            'name'      => 'longitude',
            'label'     => $hlp->__('Longitude'),
            'note'      => $hlp->__('If empty, will attempt to retrieve using the geo location address.'),
        ));


        $fieldset->addField(
            'zoom',
            'text',
            array(
                'name' => 'zoom',
                'label' => $hlp->__("Initial location zoom"),
                'note'  => $hlp->__("A number between 1 and 25, where 1 is max zoomed out and 25 is closest zoom possible.")
            )
        );

        Mage::dispatchEvent('ustorelocator_adminhtml_edit_prepare_form', array('block'=>$this, 'form'=>$form));

        if (Mage::registry('location_data')) {
            $form->setValues($data);
        }

        return parent::_prepareForm();
    }
}