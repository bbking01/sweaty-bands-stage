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


class AW_Affiliate_Block_Adminhtml_Affiliate_Edit_Tab_Balance_Profits_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->__affiliateInitialization();
        $this->setId('awAffiliateProfitsGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $additionalJavaScript = "
            " . $this->getJsObjectName() . ".rowClickCallback = function(data, click) {
                    if(Event.findElement(click,'TR').title){
                        return;
                    }
                }
        ";
        $this->setAdditionalJavaScript($additionalJavaScript);
    }

    protected function _prepareCollection()
    {
        $affiliate = Mage::registry('current_affiliate');
        $collection = $affiliate->getProfitTransactions();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('awaffiliate');
        $this->addColumn('created_at', array(
            'header' => $helper->__('Date'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '150px',
        ));
        $this->addColumn('campaign_id', array(
            'header' => $helper->__('Campaign Id'),
            'index' => 'campaign_id',
            'type' => 'number',
            'width' => '25px',
        ));
        $_currencyCode = Mage::helper('awaffiliate')->getDefaultCurrencyCode();
        $this->addColumn('amount', array(
            'header' => $helper->__('Amount'),
            'index' => 'amount',
            'type' => 'currency',
            'currency' => 'currency_code',
            'width' => '50px',
            'default' => Mage::app()->getLocale()->currency($_currencyCode)->toCurrency(0.00),
        ));
        $this->addColumn('notice', array(
            'header' => $helper->__('Comment'),
            'index' => 'notice',
            'type' => 'text',
            'width' => '200px',
        ));
        $this->addColumn('customer', array(
            'header' => $helper->__('Customer'),
            'index' => 'type',
            'renderer' => 'awaffiliate/adminhtml_widget_grid_column_renderer_trxtype',
            'width' => '25px',
        ));
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        $affiliate = Mage::registry('current_affiliate');
        return $this->getUrl('*/*/profitsGrid', array('_current' => true, 'affiliate_id' => $affiliate->getId()));
    }

    public function getRowUrl($row)
    {
        return false;
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
    protected function _beforeToHtml()
    {
        if (Mage::helper('awaffiliate')->checkExtensionVersion('Mage_Core', '0.8.28', '<=')) {
            $this->setTemplate('aw_affiliate/widget/grid.phtml');
        }
        return parent::_beforeToHtml();
    }
}
