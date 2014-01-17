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


class AW_Autorelated_Block_Adminhtml_Blocks_Shoppingcart_Edit_Tabs_Relatedproducts extends Mage_Adminhtml_Block_Widget_Form
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

        $generalOptions = array();
        if ($relatedProducts = $blockData->getData('related_products')) {
            if ($relatedProducts->getData('options')) {
                $generalOptions = $relatedProducts->getData('options');
            }
            $blockData->setData('related_products_count', $relatedProducts->getData('count'));
            $blockData->setData('show_out_of_stock', $relatedProducts->getData('show_out_of_stock'));
            $order = $relatedProducts->getData('order');
            if (!is_array($order)) {
                $order = array();
            }
            $blockData->setData('order', isset($order['type']) ? $order['type'] : null);
            $blockData->setData('order_attribute', isset($order['attribute']) ? $order['attribute'] : null);
            $blockData->setData('order_direction', isset($order['direction']) ? $order['direction'] : null);
        }


        $optionsRenderer = $this->getLayout()->createBlock('awautorelated/adminhtml_blocks_shoppingcart_edit_tabs_relatedproducts_attributes')
            ->setValues($generalOptions);

        $fieldset->addField('general_options', 'text', array(
            'label' => $this->__('Attributes'),
            'name' => 'related_products[options]'
        ))->setRenderer($optionsRenderer);

        $conditionsRenderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/*/newConditionHtml', array(
                'form' => 'related_conditions_fieldset',
                'prefix' => 'related',
                'rule' => base64_encode('awautorelated/blocks_shoppingcart_rulerelated')
            )
        ));

        $fieldset = $form->addFieldset('related_conditions_fieldset', array(
            'legend' => $this->__('Conditions (leave blank for all products)')
        ))->setRenderer($conditionsRenderer);

        /** @var $model AW_Autorelated_Model_Blocks_Shoppingcart_Ruleviewed */
        $model = Mage::getModel('awautorelated/blocks_shoppingcart_rulerelated');
        $model->setForm($fieldset);
        $model->getConditions()->setJsFormObject('related_conditions');
        if ($relatedProducts
            && is_array($conditions = $relatedProducts->getData('conditions'))
        ) {
            $model->getConditions()->loadArray($conditions, 'related');
            $model->getConditions()->setJsFormObject('related_conditions');
        }
        $fieldset->addField('related_conditions', 'text', array(
            'name' => 'related_conditions',
            'label' => Mage::helper('salesrule')->__('Conditions'),
            'title' => Mage::helper('salesrule')->__('Conditions'),
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $fieldset = $form->addFieldset('other', array(
            'legend' => $this->__('Other')
        ));

        if ($blockData->getData('related_products_count') === null)
            $blockData->setData('related_products_count', Mage::helper('awautorelated/config')->getNumberOfProducts());

        $fieldset->addField('related_products_count', 'text', array(
            'name' => 'related_products[count]',
            'title' => $this->__('Number of products'),
            'label' => $this->__('Number of products'),
            'required' => true
        ));

        $fieldset->addField('order', 'select', array(
            'name' => 'related_products[order][type]',
            'label' => $this->__('Order Products'),
            'title' => $this->__('Order Products'),
            'values' => Mage::getModel('awautorelated/source_block_common_order')->toOptionArray()
        ));

        $fieldset->addField('order_attribute', 'select', array(
            'name' => 'related_products[order][attribute]',
            'values' => Mage::getModel('awautorelated/source_catalog_product_attributes')->toOptionArray(),
            'note' => $this->__('Select Attribute')
        ));

        $fieldset->addField('order_direction', 'select', array(
            'name' => 'related_products[order][direction]',
            'values' => Mage::getModel('awautorelated/source_resource_collection_order')->toOptionArray(),
            'note' => $this->__('Sort Direction')
        ));

        $fieldset->addField('show_out_of_stock', 'select', array(
            'name' => 'related_products[show_out_of_stock]',
            'label' => $this->__('Show "Out of stock" Products'),
            'title' => $this->__('Show "Out of stock" Products'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        $form->setValues($blockData->toArray());
        $this->setForm($form);
    }
}
