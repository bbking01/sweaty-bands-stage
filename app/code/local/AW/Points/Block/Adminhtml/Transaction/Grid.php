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


class AW_Points_Block_Adminhtml_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('id', 'desc');
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('points/transaction')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $helper = Mage::helper('points');
        $this->addColumn('id', array(
            'header' => $helper->__('ID'),
            'index' => 'id',
            'type' => 'number'
        ));

        $this->addColumn('customer_name', array(
            'header' => $helper->__('Customer Name'),
            'index' => 'customer_name',
        ));

        $this->addColumn('customer_email', array(
            'header' => $helper->__('Customer Email'),
            'index' => 'customer_email',
        ));

        $this->addColumn('comment', array(
            'header' => $helper->__('Comment'),
            'index' => 'comment',
            'renderer' => 'points/adminhtml_transaction_grid_renderer_comment'
        ));

        $this->addColumn('notice', array(
            'header' => $helper->__('Notice'),
            'index' => 'notice',
            'renderer' => 'points/adminhtml_transaction_grid_renderer_notice'
        ));

        $this->addColumn('balance_change', array(
            'header' => $helper->__('Balance Change'),
            'index' => 'balance_change',
            'type' => 'number'
        ));

        $this->addColumn('change_date', array(
            'header' => $helper->__('Date'),
            'index' => 'change_date',
            'type' => 'datetime'
        ));

        $this->addColumn('expiration_date', array(
            'header' => $helper->__('Expiration Date'),
            'index' => 'expiration_date',
            'type' => 'datetime'
        ));

        $this->addColumn('store_id', array(
            'header' => Mage::helper('points')->__('Store View'),
            'index' => 'store_id',
            'type' => 'options',
            'options' => Mage::getModel('adminhtml/system_store')->getStoreOptionHash(true)
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
