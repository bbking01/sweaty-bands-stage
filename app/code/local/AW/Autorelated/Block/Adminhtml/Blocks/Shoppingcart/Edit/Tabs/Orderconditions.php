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


class AW_Autorelated_Block_Adminhtml_Blocks_Shoppingcart_Edit_Tabs_Orderconditions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $blockData = Mage::registry(AW_Autorelated_Adminhtml_ShoppingcartblockController::BLOCK_REGISTRY_KEY);
        if (!($blockData instanceof Varien_Object)) {
            $blockData = new Varien_Object();
        }

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/*/newConditionHtml', array(
                'form' => 'order_conditions_fieldset',
                'prefix' => 'viewed',
                'rule' => base64_encode('awautorelated/blocks_shoppingcart_ruleviewed')
            )
        ));

        $fieldset = $form->addFieldset('order_conditions_fieldset', array(
            'legend' => Mage::helper('salesrule')->__('Apply the rule only if the following conditions are met (leave blank for all products)')
        ))->setRenderer($renderer);

        /** @var $model AW_Autorelated_Model_Blocks_Shoppingcart_Ruleviewed */
        $model = Mage::getModel('awautorelated/blocks_shoppingcart_ruleviewed');
        $model->setForm($fieldset);
        $model->getConditions()->setJsFormObject('order_conditions_fieldset');
        if ($blockData->getData('currently_viewed')
            && is_array($conditions = $blockData->getData('currently_viewed')->getData('conditions'))
        ) {
            $model->getConditions()->loadArray($conditions, 'viewed');
            $model->getConditions()->setJsFormObject('order_conditions_fieldset');
        }
        $fieldset->addField('order_conditions', 'text', array(
            'name' => 'order_conditions',
            'label' => Mage::helper('salesrule')->__('Conditions'),
            'title' => Mage::helper('salesrule')->__('Conditions'),
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $form->setValues($blockData->toArray());
        $this->setForm($form);
    }
}
