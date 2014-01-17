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
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Autorelated_Block_Adminhtml_Blocks_Category_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_form = new Varien_Data_Form();
        $this->setForm($_form);
        $_data = Mage::helper('awautorelated/forms')->getFormData($this->getRequest()->getParam('id'));
        if (!is_object($_data))
            $_data = new Varien_Object($_data);

        $_fieldset = $_form->addFieldset('general_fieldset', array(
            'legend' => $this->__('General')
        ));

        $_fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $this->__('Name'),
            'required' => true
        ));

        if ($_data->getData('status') === null)
            $_data->setData('status', 1);

        $_fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => $this->__('Status'),
            'required' => TRUE,
            'values' => Mage::getModel('awautorelated/source_status')->toOptionArray()
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $_fieldset->addField('store', 'multiselect', array(
                'name' => 'store[]',
                'label' => $this->__('Store View'),
                'required' => TRUE,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(FALSE, TRUE),
            ));
        } else {
            if ($_data->getStore() && is_array($_data->getStore())) {
                $_stores = $_data->getStore();
                if (isset($_stores[0]) && $_stores[0] != '')
                    $_stores = $_stores[0];
                else
                    $_stores = 0;
                $_data->setStore($_stores);
            }

            $_fieldset->addField('store', 'hidden', array(
                'name' => 'store[]'
            ));
        }

        if (!$_data->getStore())
            $_data->setStore(0);

        if ($_data->getData('customer_groups') === null)
            $_data->setData('customer_groups', array(Mage_Customer_Model_Group::CUST_GROUP_ALL));

        $_fieldset->addField('customer_groups', 'multiselect', array(
            'name' => 'customer_groups[]',
            'label' => $this->__('Customer groups'),
            'title' => $this->__('Customer groups'),
            'required' => true,
            'values' => Mage::getModel('awautorelated/source_customer_groups')->toOptionArray()
        ));

        $_fieldset->addField('priority', 'text', array(
            'name' => 'priority',
            'label' => $this->__('Priority'),
            'title' => $this->__('Priority'),
            'required' => false
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $_fieldset->addField('date_from', 'date', array(
            'name' => 'date_from',
            'label' => $this->__('Date From'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso
        ));

        $_fieldset->addField('date_to', 'date', array(
            'name' => 'date_to',
            'label' => $this->__('Date To'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso
        ));

        $_fieldset->addField('position', 'select', array(
            'name' => 'position',
            'label' => $this->__('Position'),
            'title' => $this->__('Position'),
            'required' => true,
            'values' => Mage::getModel('awautorelated/source_position')->toOptionArray(AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK)
        ));

        $_form->setValues($_data);
    }
}
