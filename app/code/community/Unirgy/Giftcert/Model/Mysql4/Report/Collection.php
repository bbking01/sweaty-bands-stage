<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Petar
 */
class Unirgy_GiftCert_Model_Mysql4_Report_Collection
    extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('ugiftcert/history');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    protected function _getSelectedColumns()
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = 'DATE_FORMAT(ts, \'%Y-%m\')';
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = 'EXTRACT(YEAR FROM ts)';
        } else {
            $this->_periodFormat = 'DATE_FORMAT(ts, \'%Y-%m-%d\')';
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = array(
                'period'                    => $this->_periodFormat,
                'cert_uses'                 => 'COUNT(main_table.cert_id)',
                'amount'                    => 'SUM(main_table.amount)',
                'subtotal_amount'           => 'SUM(sfo.grand_total)',
                'cert_number'               => 'gc.cert_number',
                'total_amount'              => 'SUM(main_table.amount) + SUM(sfo.grand_total)',
            );
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns() + array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }


    /**
     * Add selected data
     * join cert_number
     * join order subtotal (subtotal_amount)
     * add subtotal amount to discount amount
     *
     * @return Mage_SalesRule_Model_Mysql4_Report_Collection
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(
            array('main_table' => $this->getResource()->getMainTable()),
            $this->_getSelectedColumns()
        )
            ->where('main_table.action_code=?', 'order');
        $this->getSelect()->join(
            array('gc' => $this->getTable('ugiftcert/cert')), 'main_table.cert_id=gc.cert_id', array()
        );
        $this->getSelect()->join(
            array('sfo' => $this->getTable('sales/order')), 'main_table.order_id=sfo.entity_id', array()
        );
        if ($this->isSubTotals()) {
            $this->getSelect()->group($this->_periodFormat);
        } else {
            if (!$this->isTotals()) {
                $this->getSelect()->group(
                    array(
                         $this->_periodFormat,
                         'cert_number'
                    )
                );
            }
        }
        return $this;
    }

    /**
     * Apply order status filter
     *
     * @return Mage_Sales_Model_Mysql4_Report_Collection_Abstract
     */
    protected function _applyOrderStatusFilter()
    {
        if (is_null($this->_orderStatus)) {
            return $this;
        }
        $orderStatus = $this->_orderStatus;
        if (!is_array($orderStatus)) {
            $orderStatus = array($orderStatus);
        }
        $this->getSelect()->where('sfo.status IN(?)', $orderStatus);
        return $this;
    }

    protected function _applyStoresFilter()
    {
        return $this;
    }

    /**
     * Apply date range filter
     *
     * @return Mage_Sales_Model_Mysql4_Report_Collection_Abstract
     */
    protected function _applyDateRangeFilter()
    {
        if (!is_null($this->_from)) {
            $this->getSelect()->where('ts >= ?', $this->_from);
        }
        if (!is_null($this->_to)) {
            $this->getSelect()->where('ts <= ?', $this->_to);
        }
        return $this;
    }
}
