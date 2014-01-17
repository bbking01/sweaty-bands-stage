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


class AW_Points_Block_Adminhtml_Transaction_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $helper = Mage::helper('points');

        $transaction = Mage::registry('points_current_transaction');

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('main_group', array('legend' => Mage::helper('points')->__('Fields')));

        $fieldset->addField('comment', 'note', array(
            'label' => Mage::helper('points')->__('Comment'),
            'text' => $transaction->getActionInstance()->getCommentHtml()
        ));

        $fieldset->addField('balance_change', 'label', array(
            'label' => Mage::helper('points')->__('Points Balance Change'),
            'value' => $transaction->getBalanceChange()
        ));


        $customerNameLinkRenderer = $this->getLayout()
                ->createBlock('points/adminhtml_transaction_edit_form_renderer_link')
                ->setCustomerUrl($this->getUrl('adminhtml/customer/edit/', array('id' => $transaction->getCustomer()->getId())))
                ->setHtmlId('customer_name');

        $fieldset->addField('customer_name', 'label', array(
            'label' => Mage::helper('points')->__('Customer Name'),
            'value' => $transaction->getCustomer()->getName()
        ))->setRenderer($customerNameLinkRenderer);


        $customerEmailLinkRenderer = $this->getLayout()
                ->createBlock('points/adminhtml_transaction_edit_form_renderer_link')
                ->setCustomerEmail('mailto:' . $transaction->getCustomer()->getEmail())
                ->setHtmlId('customer_email');

        $fieldset->addField('customer_email', 'label', array(
            'label' => Mage::helper('points')->__('Customer Email'),
            'value' => $transaction->getCustomer()->getEmail()
        ))->setRenderer($customerEmailLinkRenderer);

        $fieldset->addField('customer_group', 'label', array(
            'label' => Mage::helper('points')->__('Customer Group'),
            'value' => Mage::getModel('customer/group')->load($transaction->getCustomer()->getGroupId())->getCustomerGroupCode()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}