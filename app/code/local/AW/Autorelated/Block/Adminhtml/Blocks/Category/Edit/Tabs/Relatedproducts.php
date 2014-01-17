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


class AW_Autorelated_Block_Adminhtml_Blocks_Category_Edit_Tabs_Relatedproducts extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_form = new Varien_Data_Form();
        $this->setForm($_form);
        $_data = Mage::helper('awautorelated/forms')->getFormData($this->getRequest()->getParam('id'));
        if (is_array($_data)) {
            $_data = Mage::getModel('awautorelated/blocks')->setData($_data);
        } else if (!is_object($_data)) {
            $_data = Mage::getModel('awautorelated/blocks');
        }

        if ($_data->getRelatedProducts()) {
            $_data->setData('related_products_include', $_data->getData('related_products')->getData('include'));
            $_data->setData('related_products_count', $_data->getData('related_products')->getData('count'));
            $_data->setData('show_out_of_stock', $_data->getData('related_products')->getData('show_out_of_stock'));
            $order = $_data->getData('related_products')->getData('order');
            if (!is_array($order)) {
                $order = array();
            }
            $_data->setData('order', isset($order['type']) ? $order['type'] : null);
            $_data->setData('order_attribute', isset($order['attribute']) ? $order['attribute'] : null);
            $_data->setData('order_direction', isset($order['direction']) ? $order['direction'] : null);
        }

        $_fieldset = $_form->addFieldset('general_fieldset', array(
            'legend' => $this->__('General')
        ));

        $_fieldset->addField('related_products_include', 'select', array(
            'name' => 'related_products[include]',
            'label' => $this->__('Include'),
            'values' => Mage::getModel('awautorelated/source_block_category_include')->toOptionArray()
        ));

        if ($_data->getData('related_products_count') === null)
            $_data->setData('related_products_count', Mage::helper('awautorelated/config')->getNumberOfProducts());

        $_fieldset->addField('related_products_count', 'text', array(
            'name' => 'related_products[count]',
            'title' => $this->__('Number of products'),
            'label' => $this->__('Number of products'),
            'required' => true
        ));

        $_fieldset->addField('order', 'select', array(
            'name' => 'related_products[order][type]',
            'label' => $this->__('Order Products'),
            'title' => $this->__('Order Products'),
            'values' => Mage::getModel('awautorelated/source_block_common_order')->toOptionArray()
        ));

        $_fieldset->addField('order_attribute', 'select', array(
            'name' => 'related_products[order][attribute]',
            'values' => Mage::getModel('awautorelated/source_catalog_product_attributes')->toOptionArray(),
            'note' => $this->__('Select Attribute')
        ));

        $_fieldset->addField('order_direction', 'select', array(
            'name' => 'related_products[order][direction]',
            'values' => Mage::getModel('awautorelated/source_resource_collection_order')->toOptionArray(),
            'note' => $this->__('Sort Direction')
        ));

        $_fieldset->addField('show_out_of_stock', 'select', array(
            'name' => 'related_products[show_out_of_stock]',
            'label' => $this->__('Show "Out of stock" Products'),
            'title' => $this->__('Show "Out of stock" Products'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/*/newConditionHtml', array(
                'form' => 'conditions_fieldset',
                'prefix' => 'related',
                'rule' => base64_encode('awautorelated/blocks_category_rule')
            )
        ));

        $_fieldset = $_form->addFieldset('conditions_fieldset', array(
            'legend' => $this->__('Conditions (leave blank for all products)')
        ))->setRenderer($renderer);

        /* Setup of the rule control */
        $model = Mage::getModel('awautorelated/blocks_category_rule');
        $model->setForm($_fieldset);
        $model->getConditions()->setJsFormObject('conditions_fieldset');
        if ($_data->getData('related_products') && is_array($_data->getData('related_products')->getData('conditions'))) {
            $conditions = $_data->getData('related_products')->getData('conditions');
            $model->getConditions()->loadArray($conditions, 'related');
            $model->getConditions()->setJsFormObject('conditions_fieldset');
        }

        $_fieldset->addField('conditions', 'text', array(
            'name' => 'related_conditions',
            'label' => Mage::helper('salesrule')->__('Conditions'),
            'title' => Mage::helper('salesrule')->__('Conditions'),
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $_form->setValues($_data);
    }
}
