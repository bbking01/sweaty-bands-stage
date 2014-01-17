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


class AW_Points_Block_Adminhtml_Rule_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('rule_id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('points/rule')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $helper = Mage::helper('points');
        $this->addColumn('rule_id', array(
            'header' => $helper->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'rule_id',
            'type' => 'number'
        ));

        $this->addColumn('name', array(
            'header' => $helper->__('Rule Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('from_date', array(
            'header' => $helper->__('Date Start'),
            'align' => 'left',
            'index' => 'from_date',
            'type' => 'date'
        ));

        $this->addColumn('to_date', array(
            'header' => $helper->__('Date Expire'),
            'align' => 'left',
            'index' => 'to_date',
            'type' => 'date'
        ));

        $this->addColumn('is_active', array(
            'header' => $helper->__('Status'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'is_active',
            'type' => 'options',
            'options' => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));

        $this->addColumn('priority', array(
            'header' => $helper->__('Priority'),
            'align' => 'left',
            'index' => 'priority',
            'type' => 'number'
        ));

        $this->addColumn('points_change', array(
            'header' => $helper->__('Points'),
            'width' => '100px',
            'align' => 'right',
            'index' => 'points_change',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getRuleId()));
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('points')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete')
        ));

        $this->getMassactionBlock()->addItem('activate', array(
            'label' => Mage::helper('points')->__('Activate'),
            'url' => $this->getUrl('*/*/massActivate')
        ));

        $this->getMassactionBlock()->addItem('deactivate', array(
            'label' => Mage::helper('points')->__('Deactivate'),
            'url' => $this->getUrl('*/*/massDeactivate')
        ));

        return $this;
    }

}
