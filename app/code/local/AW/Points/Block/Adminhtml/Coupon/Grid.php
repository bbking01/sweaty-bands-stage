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


class AW_Points_Block_Adminhtml_Coupon_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('coupon_grid');
        $this->setDefaultSort('coupon_id', 'desc');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('points/coupon')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $helper = Mage::helper('points');

        $this->addColumn('id', array(
            'header' => $helper->__('ID'),
            'index' => 'coupon_id',
            'type' => 'number'
        ));

        $this->addColumn('coupon_name', array(
            'header' => $helper->__('Coupon Name'),
            'index' => 'coupon_name',
        ));
        $this->addColumn('coupon_code', array(
            'header' => $helper->__('Coupon Code'),
            'index' => 'coupon_code',
        ));

        $this->addColumn('points_amount', array(
            'header' => $helper->__('Points Amount'),
            'index' => 'points_amount',
            'type' => 'number'
        ));

        $this->addColumn('uses_per_coupon', array(
            'header' => $helper->__('Uses per Coupon'),
            'index' => 'uses_per_coupon',
            'type' => 'number'
        ));

        $this->addColumn('activation_cnt', array(
            'header' => $helper->__('Activation Count'),
            'index' => 'activation_cnt',
            'type' => 'number',
        ));


        $this->addColumn('status', array(
            'header' => $helper->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));

        $this->addColumn('from_date', array(
            'header' => $helper->__('From Date'),
            'index' => 'from_date',
            'type' => 'date'
        ));

        $this->addColumn('to_date', array(
            'header' => $helper->__('To Date'),
            'index' => 'to_date',
            'type' => 'date'
        ));


        $this->addColumn('action', array(
            'header' => Mage::helper('points')->__('Action'),
            'filter' => false,
            'sortable' => false,
            'no_link' => true,
            'width' => '100px',
            'renderer' => 'points/adminhtml_coupon_grid_renderer_action'
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('coupon_id');
        $this->getMassactionBlock()->setFormFieldName('coupon_ids');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('points')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('points')->__('Are you sure?'),
        ));

        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
