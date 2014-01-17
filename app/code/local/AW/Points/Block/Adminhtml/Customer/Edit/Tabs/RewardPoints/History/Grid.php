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


class AW_Points_Block_Adminhtml_Customer_Edit_Tabs_RewardPoints_History_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    /**
     * Default sort field
     *
     * @var string
     */
    protected $_defaultSort = 'change_date';

    /**
     * Initialize Grid
     *
     */
    public function __construct() {
        parent::__construct();
        $this->setId('pointsHistoryGrid');
        $this->setUseAjax(true);
        $this->setEmptyText(Mage::helper('points')->__('No Transactions Found'));
    }

    /**
     * Retrieve current customer object
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer() {

        if ($customerId = $this->getCustomerId()) {

            return Mage::getModel('customer/customer')->load($customerId);
        }

        return Mage::registry('current_customer');
    }

    protected function _prepareCollection() {

        $collection = Mage::getModel('points/api')
                ->getCustomerTransactions($this->_getCustomer());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {


        $this->addColumn('id', array(
            'header' => Mage::helper('points')->__('ID'),
            'index' => 'id',
            'type' => 'number',
        ));

        $this->addColumn('store_id', array(
            'header' => Mage::helper('points')->__('Store View'),
            'index' => 'store_id',
            'type' => 'options',
            'options' => Mage::getModel('adminhtml/system_store')->getStoreOptionHash(true)
        ));

        $this->addColumn('balance_change', array(
            'header' => Mage::helper('points')->__('Points'),
            'index' => 'balance_change',
            'type' => 'number',
        ));

        $this->addColumn('change_date', array(
            'header' => Mage::helper('points')->__('Date'),
            'index' => 'change_date',
            'type' => 'datetime'
        ));

        $this->addColumn('expiration_date', array(
            'header' => Mage::helper('points')->__('Date Expire'),
            'index' => 'expiration_date',
            'type' => 'datetime'
        ));

        $this->addColumn('comment', array(
            'header' => Mage::helper('points')->__('Comment'),
            'index' => 'comment',
            'type' => 'text'
        ));
        return parent::_prepareColumns();
    }

    public function getGridUrl() {
        return $this->getUrl('points_admin/adminhtml_history/transactionHistoryGrid', array('_current' => true));
    }

}