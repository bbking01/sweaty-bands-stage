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


class AW_Affiliate_Block_Adminhtml_Withdrawal_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awAffiliateWithdrawalGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setDefaultFilter(array(
            'status' => AW_Affiliate_Model_Source_Withdrawal_Status::PENDING
        ));
        $additionalJavaScript = <<<JS
Withdrawal.init({$this->getJsObjectName()}, {
    id: '{$this->getId()}',
    gridContainerId: '{$this->helper('awaffiliate')->getWithdrawalContainerId()}',
    requestUrl: '{$this->getUrl('*/*/jsonWithdrawalRequestSave')}',
    messages: {
        incorrect_response: '{$this->__('Incorrect response')}',
        details_not_defined: '{$this->__('Details were not defined')}'
    }
});
JS;
        $this->setAdditionalJavaScript($additionalJavaScript);

    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('awaffiliate/withdrawal_request')->getCollection();
        $collection->joinWithTransactions();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'index' => 'id',
            'type' => 'number',
            'width' => '100',
            'filter_condition_callback' => array($this, '_filterIdCondition')
        ));

        $this->addColumn('customer_full_name', array(
            'header' => $this->__('Affiliate'),
            'index' => 'customer_full_name',
            'filter' => false,
            'sortable' => false,
            'renderer' => 'AW_Affiliate_Block_Adminhtml_Widget_Grid_Column_Renderer_Affiliate'
        ));

        $this->addColumn('created_at', array(
            'header' => $this->__('Date Created'),
            'index' => 'created_at',
            'type' => 'datetime',
            'column_css_class' => 'withdr-created-at',
            'width' => '160'
        ));

        $this->addColumn('resolved_at', array(
            'header' => $this->__('Date Resolved'),
            'index' => 'transaction_created_at',
            'filter_index' => 'transactions.created_at',
            'type' => 'datetime',
            'width' => '160',
        ));

        $this->addColumn('amount_requested', array(
            'header' => $this->__('Amount requested'),
            'column_css_class' => 'withdr-amount-requested',
            'index' => 'amount',
            'filter_index' => 'main_table.amount',
            'type' => 'currency',
            'currency_code' => Mage::app()->getStore()->getBaseCurrencyCode(),
            'width' => '100px',
        ));

        $this->addColumn('amount_paid', array(
            'header' => $this->__('Amount paid'),
            'index' => 'transaction_amount',
            'filter_index' => 'transactions.amount',
            'type' => 'currency',
            'currency_code' => Mage::app()->getStore()->getBaseCurrencyCode(),
            'width' => '100px',
        ));

        $this->addColumn('details', array(
            'header' => $this->__('Details'),
            'column_css_class' => 'withdr-details',
            'index' => 'description',
            'filter_index' => 'main_table.description',
            'type' => 'text',
            'width' => '250px',
        ));

        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('awaffiliate/source_withdrawal_status')->toShortOptionArray(),
            'column_css_class' => 'withdr-status',
            'width' => '150',
            'sortable' => false,
            'filter_condition_callback' => array($this, '_filterStatusCondition')
        ));

        $this->addColumn('action',
            array(
                'header' => $this->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id',
                        'onclick' => 'Withdrawal.editActionClick(this);return false;'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            )
        );

        $this->addColumn('withdrawal_details', array(
            'column_css_class' => 'withdr-withdrawal-details withdrawal-hidden',
            'header_css_class' => 'withdrawal-hidden',
            'index' => 'notice',
            'filter_index' => 'main_table.notice',
            'type' => 'text',
            'width' => '0',
        ));

        return parent::_prepareColumns();
    }

    public function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('withdrawals');

        $statuses = Mage::getModel('awaffiliate/source_withdrawal_status')->toOptionArray();

        $this->getMassactionBlock()->addItem('assign_status', array(
            'label' => $this->__('Change Status'),
            'url' => $this->getUrl('*/*/massstatus'),
            'additional' => array(
                'status' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => $this->__('Status'),
                    'values' => $statuses
                )
            )
        ));
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('joinCustomerFullName');
        parent::_afterLoadCollection();
    }

    /**
     * Return grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return $row->getId();
    }

    public function _toHtml()
    {
        $output = parent::_toHtml();
        try {
            $columnCount = $this->getColumnCount() - 1;
            $output = preg_replace("|(<td class=[\"']empty-text.*\" colspan=[\"'])([\d]*)([\"'])|", '${1}' . $columnCount . '$3', $output, 1);
        } catch (Exception $ex) {
            $output = null;
        }
        if ($output === null) {
            $output = parent::_toHtml();
        }
        return $output;
    }

    protected function _filterStatusCondition($collection, $column)
    {
        if (!($value = $column->getFilter()->getValue())) return;
        $collection->addStatusFilter($value);
    }

    protected function _beforeToHtml()
    {
        if (Mage::helper('awaffiliate')->checkExtensionVersion('Mage_Core', '0.8.28', '<=')) {
            $this->setTemplate('aw_affiliate/widget/grid.phtml');
        }
        return parent::_beforeToHtml();
    }

    protected function _filterIdCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) return;
        /** @var $collection AW_Affiliate_Model_Resource_Withdrawal_Request_Collection */
        $collection->addFieldToFilter('main_table.id', $value);
        return $this;
    }
}
