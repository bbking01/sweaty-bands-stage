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


class AW_Points_Block_Adminhtml_Rate_Earn_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $helper = Mage::helper('points');

        $rate = Mage::registry('points_rate_data');

        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save', array('_current' => true)),
                    'method' => 'post'
                ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $helper->__('Reward Exchange Rate Information')
                ));

        $fieldset->addField('website_ids', 'multiselect', array(
            'name' => 'website_ids',
            'title' => $helper->__('Website'),
            'label' => $helper->__('Website'),
            'values' => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm(),
            'value' => $rate->getWebsiteIds(),
            'required' => true,
            'after_element_html' => $helper->addSelectAll('website_ids')
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionArray();

        $fieldset->addField('customer_group_ids', 'multiselect', array(
            'name' => 'customer_group_ids',
            'title' => $helper->__('Customer Group'),
            'label' => $helper->__('Customer Group'),
            'values' => $groups,
            'value' => $rate->getCustomerGroupIds(),
            'required' => true,
            'after_element_html' => $helper->addSelectAll('customer_group_ids')
        ));

        $fieldset->addField('direction', 'hidden', array(
            'name' => 'direction',
            'value' => $this->_getDirection()
        ));

        $ratesRenderer = $this->getLayout()
                ->createBlock('points/adminhtml_rate_earn_edit_form_renderer_rate')
                ->setDirection($this->_getDirection())
                ->setRate($rate);

        $fieldset->addField('rate_to_currency', 'note', array(
            'title' => $helper->__('Rate'),
            'label' => $helper->__('Rate'),
        ))->setRenderer($ratesRenderer);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getDirection() {
        return AW_Points_Model_Rate::CURRENCY_TO_POINTS;
    }

}