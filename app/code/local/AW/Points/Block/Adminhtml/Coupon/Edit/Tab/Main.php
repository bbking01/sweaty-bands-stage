<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Adminhtml_Coupon_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('points_coupon_data');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('coupon_');
        $helper = Mage::helper('points');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $helper->__('General Information')));


        if ($model->getId()) {
            $fieldset->addField('coupon_id', 'hidden', array(
                'name' => 'coupon_id',
            ));
        }

        $fieldset->addField('coupon_name', 'text', array(
            'name' => 'coupon_name',
            'label' => $helper->__('?oupon Name'),
            'title' => $helper->__('?oupon Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => Mage::helper('points')->__('Description'),
            'title' => Mage::helper('points')->__('Description'),
            'style' => 'width: 98%; height: 100px;',
        ));


        $fieldset->addField('coupon_code', 'text', array(
            'name' => 'coupon_code',
            'label' => Mage::helper('points')->__('Coupon Code'),
            'title' => Mage::helper('points')->__('Coupon Code'),
            'required' => true,
        ));



        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('website_ids', 'multiselect', array(
                'name' => 'website_ids[]',
                'label' => Mage::helper('points')->__('Websites'),
                'title' => Mage::helper('points')->__('Websites'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray(),
            ));
        } else {
            $fieldset->addField('website_ids', 'hidden', array(
                'name' => 'website_ids[]',
                'value' => Mage::app()->getStore(true)->getWebsiteId()
            ));
            $model->setWebsiteIds(Mage::app()->getStore(true)->getWebsiteId());
        }

        $customerGroups = Mage::getResourceModel('customer/group_collection')
                        ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $key => $group) {
            if ($group['value'] == 0) {
                unset($customerGroups[$key]);
            }
        }

        $fieldset->addField('customer_group_ids', 'multiselect', array(
            'name' => 'customer_group_ids[]',
            'label' => Mage::helper('points')->__('Customer Groups'),
            'title' => Mage::helper('points')->__('Customer Groups'),
            'required' => true,
            'values' => $customerGroups,
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name' => 'from_date',
            'label' => Mage::helper('points')->__('From Date'),
            'title' => Mage::helper('points')->__('From Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso
        ));
        $fieldset->addField('to_date', 'date', array(
            'name' => 'to_date',
            'label' => Mage::helper('points')->__('To Date'),
            'title' => Mage::helper('points')->__('To Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso
        ));

        $fieldset->addField('status', 'select', array(
            'label' => $helper->__('Status'),
            'title' => $helper->__('Status'),
            'name' => 'status',
            'options' => array(
                '1' => $helper->__('Active'),
                '0' => $helper->__('Inactive'),
            ),
        ));


        $fieldset->addField('points_amount', 'text', array(
            'name' => 'points_amount',
            'label' => $helper->__('Points Amount'),
            'title' => $helper->__('Points Amount'),
            'required' => true,
            'class' => 'validate-number',
        ));


        $fieldset->addField('uses_per_coupon', 'text', array(
            'name' => 'uses_per_coupon',
            'label' => $helper->__('Uses per Coupon'),
            'title' => $helper->__('Uses per Coupon'),
            'required' => true,
            'class' => 'validate-number',
        ));

        $fieldset->addField('activation_cnt', 'label', array(
            'name' => 'uses_per_coupon',
            'label' => $helper->__('Activation Count'),
            'title' => $helper->__('Activation Count'),
            'disabled' => true,
        ));


        $form->setValues($model->getData());
        $this->setForm($form);

        Mage::dispatchEvent('adminhtml_promo_quote_edit_tab_main_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }

}
