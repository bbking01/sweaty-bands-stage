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


class AW_Affiliate_Block_Adminhtml_Affiliate_Edit_Tab_Balance_Withdrawals_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'withdrawal_form', 'action' => '#', 'method' => '#', 'class' => 'custom_ajax_form'));

        $form->addField('withdrawal_request_id', 'hidden', array(
            'name' => 'withdrawal_request_id',
        ));

        $form->addField('created_at', 'note', array(
            'html_id' => 'created_at',
            'text' => '&nbsp;',
            'label' => Mage::helper('awaffiliate')->__('Date Created'),
            'title' => Mage::helper('awaffiliate')->__('Date Created'),
        ));

        $form->addField('amount', 'note', array(
            'html_id' => 'amount_requested',
            'text' => '&nbsp;',
            'label' => Mage::helper('awaffiliate')->__('Amount Requested'),
            'title' => Mage::helper('awaffiliate')->__('Amount Requested'),
        ));

        $form->addField('details', 'note', array(
            'html_id' => 'description',
            'text' => '&nbsp;',
            'label' => Mage::helper('awaffiliate')->__('Details'),
            'title' => Mage::helper('awaffiliate')->__('Details'),
        ));

        $form->addField('withdrawal_status', 'select', array(
            'name' => 'withdrawal_status',
            'label' => Mage::helper('awaffiliate')->__('Status'),
            'title' => Mage::helper('awaffiliate')->__('Status'),
            'values' => Mage::getModel('awaffiliate/source_withdrawal_status')->toShortOptionArray(),
        ));

        $form->addField('withdrawal_details', 'textarea', array(
            'name' => 'withdrawal_details',
            'label' => Mage::helper('awaffiliate')->__('Notice'),
            'title' => Mage::helper('awaffiliate')->__('Notice'),
            'after_element_html' => '<p class="aw-note"><span>' . $this->__('Appears in the admin panel only') . '</span></p>'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
