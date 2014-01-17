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
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Block_Adminhtml_Affiliate_Edit_Tab_Balance_Profits_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'profit_form', 'action' => '#', 'method' => '#', 'class' => 'custom_ajax_form'));

        $symbol = Mage::helper('awaffiliate')->getDefaultCurrencySymbol();
        $form->addField('profit_amount', 'text', array(
            'name' => 'profit_amount',
            'label' => Mage::helper('awaffiliate')->__('Amount (%s)', $symbol),
            'title' => Mage::helper('awaffiliate')->__('Amount'),
            'required' => true,
            'class' => 'local-validation validate-number',
        ));

        $form->addField('profit_campaign', 'select', array(
            'name' => 'profit_campaign',
            'label' => Mage::helper('awaffiliate')->__('Campaign'),
            'title' => Mage::helper('awaffiliate')->__('Campaign'),
            'values' => Mage::getModel('awaffiliate/source_campaign')->toShortOptionArray(),
            'required' => true,
            'class' => 'local-validation',
        ));
/*
        $form->addField('profit_details', 'textarea', array(
            'name' => 'profit_description',
            'label' => Mage::helper('awaffiliate')->__('Details'),
            'title' => Mage::helper('awaffiliate')->__('Details'),
            'after_element_html' => '<p class="aw-note"><span>' .Mage::helper('awaffiliate')->__('Visible for affiliate'). '</span></p>',
            'style' => 'height:5em',
        ));
*/
        $form->addField('profit_notice', 'textarea', array(
            'name' => 'profit_notice',
            'label' => Mage::helper('awaffiliate')->__('Comment'),
            'title' => Mage::helper('awaffiliate')->__('Comment'),
            'after_element_html' => '<p class="aw-note"><span>' .Mage::helper('awaffiliate')->__('Visible only for admin') . '</span></p>',
            'style' => 'height:5em',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
