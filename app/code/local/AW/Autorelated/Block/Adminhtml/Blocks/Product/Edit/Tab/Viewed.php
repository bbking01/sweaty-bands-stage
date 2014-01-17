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


class AW_Autorelated_Block_Adminhtml_Blocks_Product_Edit_Tab_Viewed extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        //$model = Mage::registry('productblock_data');
        $model = Mage::getModel('awautorelated/blocks')->load((int)$this->getRequest()->getParam('id'));
        //$model = Mage::getModel('sale/rule');
        $form = new Varien_Data_Form();
        //$form->setHtmlIdPrefix('viewed_');
        $helper = Mage::helper('awautorelated');

        /*
          $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
          ->setTemplate('promo/fieldset.phtml')
          ->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newConditionHtml/form/conditions_fieldset'));
         */
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/*/newConditionHtml', array(
            'form' => 'viewed_conditions_fieldset',
            'prefix' => 'viewed',
            'rule' => base64_encode('awautorelated/blocks_product_ruleviewed'))));

        $fieldset = $form->addFieldset('viewed_conditions_fieldset', array(
            'legend' => $this->__('Conditions (leave blank for all products)')
        ))->setRenderer($renderer);

        $rule = Mage::getModel('awautorelated/blocks_product_ruleviewed');
        $rule->getConditions()->setJsFormObject('viewed_conditions_fieldset');
        $rule->getConditions()->setId('viewed_conditions_fieldset');

        $rule->setForm($fieldset);
        if ($model->getData('currently_viewed') && is_array($model->getData('currently_viewed')->getData('conditions'))) {
            $conditions = $model->getData('currently_viewed')->getData('conditions');
            $rule->getConditions()->loadArray($conditions, 'viewed');
            $rule->getConditions()->setJsFormObject('viewed_conditions_fieldset');
        }

        $fieldset->addField('viewed_conditions', 'text', array(
            'name' => 'viewed_conditions',
            'label' => $this->__('Apply To'),
            'title' => $this->__('Apply To'),
            'required' => true,
        ))->setRule($rule)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
