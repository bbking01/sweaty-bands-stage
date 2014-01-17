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


class AW_Points_Block_Adminhtml_Rate_Earn_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getDirection() {
        return AW_Points_Model_Rate::CURRENCY_TO_POINTS;
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('points/rate')->getCollection();
        $collection->addFieldToFilter('direction', $this->_getDirection());

        $this->setCollection($collection);
        parent::_prepareCollection();

        foreach ($collection as $item) {
            $item->setData('customer_group_ids', explode(',', $item->getData('customer_group_ids')));
            $item->setData('website_ids', explode(',', $item->getData('website_ids')));
        }

        return;
    }

    protected function _prepareColumns() {
        $helper = Mage::helper('points');
        $this->addColumn('id', array(
            'header' => $helper->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
            'type' => 'int',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_ids', array(
                'header' => Mage::helper('customer')->__('Website'),
                'align' => 'left',
                'width' => '200px',
                'type' => 'options',
                'options' => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(),
                'index' => 'website_ids',
                'filter_condition_callback' => array($this, 'filterCallback'),
                'sortable' => false,
            ));
        }

        $groups = Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionHash();

        $this->addColumn('customer_group_ids', array(
            'header' => $helper->__('Customer Group IDs'),
            'align' => 'left',
            'index' => 'customer_group_ids',
            'type' => 'options',
            'width' => '200px',
            'sortable' => false,
            'options' => $groups,
            'filter_condition_callback' => array($this, 'filterCallback'),
        ));

        $this->addColumn('rate', array(
            'header' => $helper->__('Rate'),
            'align' => 'left',
            'index' => 'rate',
            'filter' => false,
            'sortable' => false,
            'getter' => 'getRateText',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function filterCallback($collection, $column) {
        $val = $column->getFilter()->getValue();

        if (is_null(@$val))
            return;
        else
            $cond = "FIND_IN_SET('$val', {$column->getIndex()})";

        $collection->getSelect()->where($cond);
    }

}
