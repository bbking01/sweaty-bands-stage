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


class AW_Affiliate_Block_Adminhtml_Affiliate_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        /* @var $model AW_Affiliate_Model_Campaign */
        $affiliate = Mage::registry('current_affiliate');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('general_');

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => Mage::helper('awaffiliate')->__('General Information'))
        );

        $fieldset->addField('customer_id', 'hidden', array(
            'name' => 'customer_id',
        ));

        if ($affiliate->hasId()) {
            $__name = Mage::helper('awaffiliate/affiliate')->getFullCustomerNameForAffiliate($affiliate);
            $affiliate->setData('customer_name', $__name);
            $__group = Mage::getModel('customer/group')->load($affiliate->getCustomerGroupId());
            $affiliate->setData('customer_group', $__group->getCode());
        }
        $fieldset->addField('customer_name', 'link', array(
            'html_id' => 'customer_name',
            'href' => $this->getUrl('adminhtml/customer/edit', array('id' => $affiliate->getCustomerId())),
            'label' => Mage::helper('awaffiliate')->__('Customer'),
            'title' => Mage::helper('awaffiliate')->__('Customer'),
            'target' => '_blank',
        ));

        $fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => Mage::helper('awaffiliate')->__('Status'),
            'title' => Mage::helper('awaffiliate')->__('Status'),
            'values' => Mage::getModel('awaffiliate/source_affiliate_status')->toShortOptionArray(),
            'required' => true,
        ));

        $fieldset->addField('customer_group', 'link', array(
            'html_id' => 'customer_group',
            'href' => $this->getUrl('adminhtml/customer_group/edit', array('id' => $affiliate->getCustomerGroupId())),
            'label' => Mage::helper('awaffiliate')->__('Customer Group'),
            'title' => Mage::helper('awaffiliate')->__('Customer Group'),
            'target' => '_blank',
        ));

        if ($affiliate->hasId()) {
            $value = $affiliate->getTotalAffiliated();
            $fieldset->addField('total_affiliated', 'note', array(
                'html_id' => 'total_affiliated',
                'text' => Mage::helper('core')->currency($value),
                'label' => Mage::helper('awaffiliate')->__('Total Affiliated'),
                'title' => Mage::helper('awaffiliate')->__('Total Affiliated'),
            ));

            $value = Mage::helper('awaffiliate/affiliate')->getLastMonthAmountForAffiliate($affiliate);
            $fieldset->addField('aff_last_month', 'note', array(
                'html_id' => 'aff_last_month',
                'text' => Mage::helper('core')->currency($value),
                'label' => Mage::helper('awaffiliate')->__('Affiliated Last Month'),
                'title' => Mage::helper('awaffiliate')->__('Affiliated Last Month'),
            ));
        }

        $form->setValues($affiliate->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return Mage::helper('awaffiliate')->__('General');
    }


    public function getTabTitle()
    {
        return Mage::helper('awaffiliate')->__('General Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
