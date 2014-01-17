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


class AW_Affiliate_Block_Adminhtml_Affiliate_Edit_Tab_Balance_Withdrawals_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->__affiliateInitialization();
        $this->setId('awAffiliateWithdrawalsGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
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
        $affiliate = Mage::registry('current_affiliate');
        $collection = $affiliate->getWithdrawalRequests();
        $collection->joinWithTransactions();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('awaffiliate');
        $this->addColumn('created_at', array(
            'header' => $helper->__('Date created'),
            'column_css_class' => 'withdr-created-at',
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            'width' => '150px',
        ));
        $this->addColumn('resolved_at', array(
            'header' => $helper->__('Date resolved'),
            'index' => 'transaction_created_at',
            'filter_index' => 'transactions.created_at',
            'type' => 'datetime',
            'width' => '150px',
        ));
        $this->addColumn('amount_requested', array(
            'header' => $helper->__('Amount requested'),
            'column_css_class' => 'withdr-amount-requested',
            'index' => 'amount',
            'filter_index' => 'main_table.amount',
            'type' => 'currency',
            'currency_code' => Mage::app()->getStore()->getBaseCurrencyCode(),
            'width' => '100px',
        ));
        $this->addColumn('amount_paid', array(
            'header' => $helper->__('Amount paid'),
            'index' => 'transaction_amount',
            'filter_index' => 'transactions.amount',
            'type' => 'currency',
            'currency_code' => Mage::app()->getStore()->getBaseCurrencyCode(),
            'width' => '100px',
        ));
        $this->addColumn('details', array(
            'header' => $helper->__('Details'),
            'column_css_class' => 'withdr-details',
            'index' => 'description',
            'filter_index' => 'main_table.description',
            'type' => 'text',
            'width' => '250px',
        ));
        $this->addColumn('withdrawal_status', array(
            'header' => $helper->__('Status'),
            'column_css_class' => 'withdr-status',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('awaffiliate/source_withdrawal_status')->toShortOptionArray(),
            'width' => '100px',
        ));

        $this->addColumn('withdrawal_details', array(
            'column_css_class' => 'withdr-withdrawal-details withdrawal-hidden',
            'header_css_class' => 'withdrawal-hidden',
            'index' => 'notice',
            'filter_index' => 'main_table.notice',
            'type' => 'text',
            'width' => '0px',
        ));
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        $affiliate = Mage::registry('current_affiliate');
        return $this->getUrl('*/*/withdrawalsGrid', array('_current' => true, 'affiliate_id' => $affiliate->getId()));
    }

    public function getRowUrl($row)
    {
        return $row->getId();
    }

    private function __affiliateInitialization()
    {
        if (is_null(Mage::registry('current_affiliate'))) {
            $affiliate = Mage::getModel('awaffiliate/affiliate');
            $id = $this->getRequest()->getParam('affiliate_id', 0);
            $affiliate->load($id);
            Mage::register('current_affiliate', $affiliate);
        }
    }

    protected function _toHtml()
    {
        return '<div id="' . $this->helper('awaffiliate')->getWithdrawalContainerId() . '">' . parent::_toHtml() . '</div>';
    }

    protected function _beforeToHtml()
    {
        if (Mage::helper('awaffiliate')->checkExtensionVersion('Mage_Core', '0.8.28', '<=')) {
            $this->setTemplate('aw_affiliate/widget/grid.phtml');
        }
        return parent::_beforeToHtml();
    }
}
