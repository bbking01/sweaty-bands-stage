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
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Block_Adminhtml_Report_Grid
    extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    protected $_columnGroupBy = 'period';
    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }


    public function getResourceCollectionName()
    {
        return 'ugiftcert/report_collection';
    }

    protected function _prepareColumns()
    {
        $hlp = Mage::helper('ugiftcert');
        $this->addColumn('period', array(
                                        'header'            => $hlp->__('Period'),
                                        'index'             => 'period',
                                        'width'             => 100,
                                        'sortable'          => false,
                                        'period_type'       => $this->getPeriodType(),
                                        'renderer'          => 'adminhtml/report_sales_grid_column_renderer_date',
                                        'totals_label'      => $hlp->__('Total'),
                                        'subtotals_label'   => $hlp->__('Subtotal'),
                                        'html_decorators' => array('nobr'),
                                   ));

        $this->addColumn('cert_number', array(
                                             'header'    => $hlp->__('Certificate Code'),
                                             'sortable'  => false,
                                             'index'     => 'cert_number'
                                        ));

        $this->addColumn('cert_uses', array(
                                             'header'    => $hlp->__('Number of Uses'),
                                             'sortable'  => false,
                                             'index'     => 'action_code',
                                             'total'     => 'count',
                                             'type'      => 'number',
                                             'getter'    => 'getCertUses',
                                        ));

        $currencyCode = $this->getCurrentCurrencyCode();


        $this->addColumn('amount', array(
                                                 'header'        => $hlp->__('Used Certificate Amount'),
                                                 'sortable'      => false,
                                                 'type'          => 'currency',
                                                 'renderer'      => 'ugiftcert/adminhtml_report_renderer_currency',
                                                 'currency_code' => $currencyCode,
                                                 'total'         => 'sum',
                                                 'index'         => 'amount'
                                            ));


        $this->addColumn('subtotal_amount', array(
                                                 'header'        => $hlp->__('Order grand total remainder'),
                                                 'sortable'      => false,
                                                 'type'          => 'currency',
                                                 'renderer'      => 'ugiftcert/adminhtml_report_renderer_currency',
                                                 'currency_code' => $currencyCode,
                                                 'index'         => 'sfo.grand_total',
                                                 'total'         => 'sum',
                                                 'getter'        => 'getSubtotalAmount',
                                            ));

        $this->addColumn('total_amount', array(
                                              'header'        => $hlp->__('Order Total Amount'),
                                              'sortable'      => false,
                                              'type'          => 'currency',
                                              'renderer'      => 'ugiftcert/adminhtml_report_renderer_currency',
                                              'currency_code' => $currencyCode,
                                              'index'         => 'SUM(main_table.amount) + SUM(sfo.grand_total)',
                                              'total'         => '',
                                              'getter'        => 'getTotalAmount',
                                         ));
        $filterData = $this->getFilterData();

        if ($filterData->getData('from') !== null && $filterData->getData('to') !== null) {
            $this->addExportType('*/*/exportCouponsCsv', Mage::helper('adminhtml')->__('CSV'));
            $this->addExportType('*/*/exportCouponsExcel', Mage::helper('adminhtml')->__('Excel XML'));
        }

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
