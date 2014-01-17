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


class AW_Autorelated_Block_Adminhtml_Blocks_Shoppingcart_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $blockData = Mage::registry(AW_Autorelated_Adminhtml_ShoppingcartblockController::BLOCK_REGISTRY_KEY);
        if (!($blockData instanceof Varien_Object)) {
            $blockData = new Varien_Object();
        }

        $fieldset = $form->addFieldset('general', array(
            'legend' => $this->__('General')
        ));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $this->__('Name'),
            'required' => true
        ));

        if ($blockData->getData('status') === null)
            $blockData->setData('status', 1);

        $fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => $this->__('Status'),
            'required' => true,
            'values' => Mage::getModel('awautorelated/source_status')->toOptionArray()
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store', 'multiselect', array(
                'name' => 'store[]',
                'label' => $this->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        } else {
            $blockData->setStore(Mage::app()->getStore(true)->getId());
            $fieldset->addField('store', 'hidden', array(
                'name' => 'store[]'
            ));
        }

        if ($blockData->getData('customer_groups') === null) {
            $blockData->setData('customer_groups', array(Mage_Customer_Model_Group::CUST_GROUP_ALL));
        }

        $fieldset->addField('customer_groups', 'multiselect', array(
            'name' => 'customer_groups[]',
            'label' => $this->__('Customer groups'),
            'title' => $this->__('Customer groups'),
            'required' => true,
            'values' => Mage::getModel('awautorelated/source_customer_groups')->toOptionArray()
        ));

        $fieldset->addField('priority', 'text', array(
            'name' => 'priority',
            'label' => $this->__('Priority'),
            'title' => $this->__('Priority'),
            'required' => false
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $fieldset->addField('date_from', 'date', array(
            'name' => 'date_from',
            'label' => $this->__('Date From'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso
        ));

        $fieldset->addField('date_to', 'date', array(
            'name' => 'date_to',
            'label' => $this->__('Date To'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format' => $dateFormatIso
        ));

        $fieldset->addField('position', 'select', array(
            'name' => 'position',
            'label' => $this->__('Position'),
            'title' => $this->__('Position'),
            'required' => true,
            'values' => Mage::getModel('awautorelated/source_position')->toOptionArray(AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK)
        ));

        $form->setValues($blockData->toArray());
        $this->setForm($form);
    }
}
