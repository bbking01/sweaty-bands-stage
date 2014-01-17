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


class AW_Affiliate_Block_Adminhtml_Campaign_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awaffCampaignGrid');
        $this->setDefaultSort('type');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('awaffiliate/campaign')->getCollection();
        $collection->joinProfitCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('awaffiliate');
        $this->addColumn('id', array(
            'header' => $helper->__('Campaign ID'),
            'index' => 'id',
            'type' => 'number',
            'width' => '25px',
            'filter_condition_callback' => array($this, '_filterIdCondition')
        ));
        $this->addColumn('name', array(
            'header' => $helper->__('Name'),
            'index' => 'name',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store', array('header' => Mage::helper('customer')->__('Website'),
                'width' => '200px',
                'index' => 'store_ids',
                'sortable' => TRUE,
                'type' => 'options',
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'filter_condition_callback' => array($this, 'websiteFilterCallback')
            ));
        }
        $this->addColumn('active_from', array(
            'header' => $helper->__('Active From'),
            'index' => 'active_from',
            'type' => 'date',
            'width' => '200px',
        ));
        $this->addColumn('active_to', array(
            'header' => $helper->__('Active To'),
            'index' => 'active_to',
            'type' => 'date',
            'width' => '200px',
        ));
        $this->addColumn('status', array(
            'header' => $helper->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('awaffiliate/source_campaign_status')->toShortOptionArray(),
            'width' => '100px',
        ));
        $this->addColumn('rate_type', array(
            'header' => $helper->__('Rate Type'),
            'index' => 'rate_type',
            'type' => 'options',
            'options' => Mage::getModel('awaffiliate/source_profit_type')->toShortOptionArray(),
            'width' => '100px',
            'filter_condition_callback' => array($this, 'rateTypeFilterCallback')
        ));
        $this->addColumn('action', array(
            'header' => $helper->__('Action'),
            'width' => '100px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => $helper->__('Delete'),
                    'url' => array('base' => '*/*/delete'),
                    'field' => 'id',
                    'confirm' => $this->__('Are you sure you want to remove this campaign?')
                ),
                array(
                    'caption' => $helper->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        return parent::_prepareMassaction();
    }

    public function getGridUrl()
    {
        return parent::getGridUrl();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit/', array('id' => $row->getId()));
    }

    public function websiteFilterCallback($collection, $column)
    {
        $collection->addFilterByWebsite($column->getFilter()->getValue());
        return $this;
    }

    public function rateTypeFilterCallback($collection, $column)
    {
        $collection->addFilterByRateType($column->getFilter()->getValue());
        return $this;
    }

    protected function _filterIdCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) return;
        /** @var $collection AW_Affiliate_Model_Resource_Campaign_Collection */
        $collection->addFieldToFilter('main_table.id', $value);
        return $this;
    }
}
