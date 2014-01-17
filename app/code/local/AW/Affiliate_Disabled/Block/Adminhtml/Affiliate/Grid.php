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


class AW_Affiliate_Block_Adminhtml_Affiliate_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awaffAffiliateGrid');
        $this->setDefaultSort('type');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $customerCollection = Mage::getResourceModel('awaffiliate/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAffiliateInfo()
            ->addWithdrawalRequestInfo();

        $customerCollection->getSelect()->group('e.entity_id');
        $this->setCollection($customerCollection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('awaffiliate');
        $collection=  Mage::getResourceModel('awaffiliate/customer_collection');
        $this->addColumn('affiliate_id', array(
            'header' => $helper->__('Affiliate ID'),
            'index' => 'affiliate_id',
            'type' => 'number',
            'width' => '25px',
            'filter_index' => 'affiliate_table.id',
            'filter_condition_callback' => array($this, '_filterIdCondition')
        ));

        $this->addColumn('first_name', array(
            'header' => Mage::helper('customer')->__('First Name'),
            'index' => 'firstname',
            'filter_index' => $collection->getAttributeTableAlias('firstname').'.value',
        ));

        $this->addColumn('last_name', array(
            'header' => Mage::helper('customer')->__('Last Name'),
            'index' => 'lastname',
            'filter_index' => $collection->getAttributeTableAlias('lastname').'.value',
        ));

        $this->addColumn('email', array(
            'header' => Mage::helper('customer')->__('Email'),
            'index' => 'email',
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt' => 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header' => $this->__('Customer Group'),
            'index' => 'group_id',
            'type' => 'options',
            'options' => $groups
        ));

        $this->addColumn('status', array(
            'header' => $helper->__('Status'),
            'index' => 'affiliate_status',
            'type' => 'options',
            'options' => Mage::getModel('awaffiliate/source_affiliate_status')->toShortOptionArray(),
            'filter_index' => 'affiliate_table.status'
        ));

        $_currencyCode = Mage::helper('awaffiliate')->getDefaultCurrencyCode();
        $this->addColumn('current_balance', array(
            'header' => $helper->__('Current Balance'),
            'index' => 'current_balance',
            'filter_index' => 'affiliate_table.current_balance',
            'type' => 'currency',
            'currency_code' => $_currencyCode,
            'default' => Mage::app()->getLocale()->currency($_currencyCode)->toCurrency(0.00),
        ));

        $yesnoSource = array(1 => Mage::helper('adminhtml')->__('Yes'), 0 => Mage::helper('adminhtml')->__('No'));
        $this->addColumn('withdrawal_requested', array(
            'header' => $helper->__('Withdrawal Requested'),
            'index' => 'withdrawal_requested',
            'type' => 'options',
            'options' => $yesnoSource,
            'width' => '25px',
            'filter_condition_callback' => array($this, '_filterByWithdrawalRequest')
        ));

        $this->addColumn('action', array(
            'header' => $helper->__('Action'),
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getAffiliateId',
            'actions' => array(
                array(
                    'caption' => $helper->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
                array(
                    'caption' => $helper->__('View Customer'),
                    'url' => array('base' => '*/*/customerView'),
                    'field' => 'id'
                ),
                array(
                    'caption' => $helper->__('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                    'confirm' => $this->__('Are you sure you want to remove this affiliate?')
                ),
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit/', array('id' => $row->getAffiliateId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('e.entity_id');
        $this->getMassactionBlock()->setFormFieldName('customer_ids');

        $statuses = Mage::getModel('awaffiliate/source_affiliate_status')->toOptionArray();
        array_unshift($statuses, array('label' => '', 'value' => ''));

        $this->getMassactionBlock()->addItem('status', array(
            'label' => $this->__('Change Status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->__('Status'),
                    'values' => $statuses
                )
            )
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'index' => 'affiliate_id',
            'label' => Mage::helper('awaffiliate')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('awaffiliate')->__('Are you sure?'),
        ));

        return $this;
    }

    protected function _filterIdCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) return;
        /** @var $collection AW_Affiliate_Model_Resource_Customer_Collection */
        $collection->addFieldFilterToHaving('affiliate_id', $value);
        return $this;
    }

    protected function _filterByWithdrawalRequest($collection, $column)
    {
        if (!in_array($value = $column->getFilter()->getValue(), array(0, 1))) return;
        /** @var $collection AW_Affiliate_Model_Resource_Customer_Collection */
        $collection->getSelect()->having('withdrawal_requested = ?', $value);
        return $this;
    }
}
