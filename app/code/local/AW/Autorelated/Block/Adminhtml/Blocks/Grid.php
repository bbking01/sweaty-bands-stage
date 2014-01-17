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
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Autorelated_Block_Adminhtml_Blocks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('autorelatedBlocoksGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('awautorelated/blocks')->getCollection();
        $collection->getSelect()->reset(Zend_Db_Select::ORDER);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('awautorelated');

        $this->addColumn('id', array(
            'header' => $helper->__('ID'),
            'align' => 'right',
            'width' => '5',
            'index' => 'id'
        ));

        $this->addColumn('name', array(
            'header' => $helper->__('Block Name'),
            'align' => 'left',
            'index' => 'name'
        ));

        $this->addColumn('type', array(
            'header' => $helper->__('Type'),
            'align' => 'center',
            'width' => '100',
            'index' => 'type',
            'type' => 'options',
            'options' => array(
                AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK => $helper->__(AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK_SHORT_LABEL),
                AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK => $helper->__(AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK_SHORT_LABEL),
                AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK => $helper->__(AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK_SHORT_LABEL)
            )
        ));

        $positions = new AW_Autorelated_Model_Source_Position();
        $this->addColumn('position', array(
            'header' => $helper->__('Position'),
            'align' => 'center',
            'width' => '120',
            'index' => 'position',
            'type' => 'options',
            'options' => $positions->getOptionArray()
        ));

        $this->addColumn('priority', array(
            'header' => $helper->__('Priority'),
            'align' => 'right',
            'index' => 'priority',
            'width' => '50'
        ));

        $this->addColumn('date_from', array(
            'header' => $helper->__('Date Start'),
            'align' => 'center',
            'width' => '120',
            'index' => 'date_from',
            'type' => 'date',
            'renderer' => 'AW_Autorelated_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('date_to', array(
            'header' => $helper->__('Date Expire'),
            'align' => 'center',
            'width' => '120',
            'index' => 'date_to',
            'type' => 'date',
            'renderer' => 'AW_Autorelated_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('status', array(
            'header' => $helper->__('Status'),
            'align' => 'center',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                AW_Autorelated_Model_Source_Status::ENABLED => $helper->__(AW_Autorelated_Model_Source_Status::ENABLED_LABEL),
                AW_Autorelated_Model_Source_Status::DISABLED => $helper->__(AW_Autorelated_Model_Source_Status::DISABLED_LABEL),
            )
        ));

        if (!Mage::app()->isSingleStoreMode())
            $this->addColumn('store', array(
                'header' => $this->__('Store View'),
                'width' => '200',
                'index' => 'store',
                'sortable' => FALSE,
                'type' => 'store',
                'store_all' => TRUE,
                'store_view' => TRUE,
                'renderer' => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
                'filter_condition_callback' => array($this, '_filterStoreCondition')
            ));

        if ($helper->isEditAllowed()) {
            $this->addColumn('action', array(
                'header' => $helper->__('Action'),
                'width' => '100px',
                'align' => 'center',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $helper->__('Edit'),
                        //'url'       => array('base'=> '*/adminhtml_productblock/edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    ),
                    array(
                        'caption' => $helper->__('Delete'),
                        //'url'       => array('base'=> '*/adminhtml_productblock/delete'),
                        'url' => array('base' => '*/*/delete'),
                        'field' => 'id',
                        'confirm' => $helper->__('Are you sure that you want to delete this block?')
                    ),
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));
        }

        $ret = parent::_prepareColumns();

        return $ret;
    }

    protected function _prepareMassaction()
    {
        if (!Mage::helper('awautorelated')->isEditAllowed())
            return $this;
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('awautorelated')->__('Delete'),
            'url' => $this->getUrl('*/*/delete'),
            'confirm' => Mage::helper('awautorelated')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('awautorelated/source_status')->toOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('awautorelated')->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('awautorelated')->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit/', array('id' => $row->getId()));
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!($value = $column->getFilter()->getValue()))
            return;
        $collection->addStoreFilter($value);
    }
}
