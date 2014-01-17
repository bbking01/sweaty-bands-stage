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
 * @package    AW_Checkoutpromo
 * @version    1.2.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Checkoutpromo_Block_Adminhtml_Checkoutpromo_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('checkoutpromo_grid');
        $this->setDefaultSort('sort_order');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('checkoutpromo/rule')
                ->getResourceCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('rule_id', array(
            'header' => $this->__('ID'),
            'align'  => 'right',
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'rule_id',
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Rule Name'),
            'index'  => 'name',
        ));

        $options = array();
        $cmsBlockArray = Mage::getModel('cms/block')->getCollection()->toOptionArray();
        foreach ($cmsBlockArray as $cmsBlock) {
            $options[$cmsBlock['value']] = $this->__($cmsBlock['label']);
        }

        $this->addColumn('cms_block_id', array(
            'header'  => $this->__('CMS Block Name'),
            'index'   => 'cms_block_id',
            'type'    => 'options',
            'options' => $options,
        ));

        $this->addColumn('show_on_shopping_cart', array(
            'header'  => $this->__('Show on Shopping Cart'),
            'align'   => 'right',
            'index'   => 'show_on_shopping_cart',
            'width'   => '120px',
            'type'    => 'options',
            'options' => array(
                0 => $this->__('No'),
                1 => $this->__('Yes'),
            ),
        ));

        $this->addColumn('show_on_checkout', array(
            'header'  => $this->__('Show on Checkout'),
            'align'   => 'right',
            'index'   => 'show_on_checkout',
            'width'   => '120px',
            'type'    => 'options',
            'options' => array(
                0 => $this->__('No'),
                1 => $this->__('Yes'),
            ),
        ));

        $dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        $this->addColumn('from_date', array(
            'header'  => $this->__('Date Start'),
            'align'   => 'left',
            'width'   => '120px',
            'type'    => 'datetime',
            'default' => '--',
            'index'   => 'from_date',
            'format'  => $dateFormat,
        ));

        $this->addColumn('to_date', array(
            'header'  => $this->__('Date Expire'),
            'align'   => 'left',
            'width'   => '120px',
            'type'    => 'datetime',
            'default' => '--',
            'index'   => 'to_date',
            'format'  => $dateFormat,
        ));

        $this->addColumn('is_active', array(
            'header'  => $this->__('Status'),
            'align'   => 'left',
            'width'   => '80px',
            'index'   => 'is_active',
            'type'    => 'options',
            'options' => array(
                1 => 'Active',
                0 => 'Inactive',
            ),
        ));

        $this->addColumn('customer_group_ids', array(
            'header'   => $this->__('Customer Groups'),
            'index'    => 'customer_group_ids',
            'width'    => '100px',
            'sortable' => false,
            'type'     => 'options',
            'options'  => Mage::getResourceModel('customer/group_collection')->load()->toOptionHash(),
            'renderer' => 'checkoutpromo/adminhtml_widget_grid_multiselect_renderer',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_ids', array(
                'header'   => $this->__('Websites'),
                'index'    => 'website_ids',
                'width'    => '100px',
                'sortable' => false,
                'type'     => 'options',
                'options'  => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                'renderer' => 'checkoutpromo/adminhtml_widget_grid_multiselect_renderer',
            ));
        }

        $this->addColumn('sort_order', array(
            'header' => $this->__('Priority'),
            'align'  => 'right',
            'index'  => 'sort_order',
            'width'  => '80px',
            'type'   => 'number',
        ));

        $this->addColumn('action', array(
            'header'   => $this->__('Actions'),
            'width'    => '50px',
            'type'     => 'action',
            'getter'   => 'getId',
            'actions'  => array(
                array(
                    'caption' => $this->__('Edit'),
                    'url'     => array('base' => '*/*/edit'),
                    'field'   => 'id',
                )
            ),
            'filter'   => false,
            'sortable' => false,
            'index'    => 'stores',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('rule_id');
        $this->getMassactionBlock()->setFormFieldName('rule_ids');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => $this->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));

        $statuses = array(
            array(
                'value' => '',
                'label' => '',
            ),
            array(
                'value' => 1,
                'label' => $this->__('Active'),
            ),
            array(
                'value' => 0,
                'label' => $this->__('Inactive'),
            ),
        );

        $this->getMassactionBlock()->addItem('status', array(
            'label'      => $this->__('Change Status'),
            'url'        => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $this->__('Status'),
                    'values' => $statuses,
                )
            )
        ));

        return $this;
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getRuleId()));
    }

}
