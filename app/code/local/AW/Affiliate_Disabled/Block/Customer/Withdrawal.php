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


class AW_Affiliate_Block_Customer_Withdrawal extends Mage_Core_Block_Template
{
    protected $_statusSourceModel = null;

    public function __construct()
    {
        parent::__construct();
        $_template = 'aw_affiliate/customer/withdrawal.phtml';
        $this->setTemplate($_template);
        return $this;
    }

    private $_affiliateWithdrawalsCollection = null;

    public function getWithdrawalCollection()
    {
        if (!$this->_affiliateWithdrawalsCollection instanceof AW_Affiliate_Model_Resource_Withdrawal_Request_Collection) {
            $__affiliateId = Mage::registry('current_affiliate')->getId();
            $this->_affiliateWithdrawalsCollection = Mage::getModel('awaffiliate/withdrawal_request')->getCollection();
            $this->_affiliateWithdrawalsCollection
                ->joinWithTransactions()
                ->addAffiliateFilter($__affiliateId)
                ->setOrder('created_at', 'DESC');
        }
        return $this->_affiliateWithdrawalsCollection;
    }

    public function setWithdrawalCollection(AW_Affiliate_Model_Resource_Withdrawal_Request_Collection $collection)
    {
        $this->_affiliateWithdrawalsCollection = $collection;
        return $this;
    }

    public function getPendingRequestsCount()
    {
        $count = Mage::helper('awaffiliate/affiliate')->getPendingWithdrawalRequestsSize(Mage::registry('current_affiliate'));
        return $count;
    }

    public function formatCurrency($value)
    {
        return Mage::helper('core')->currency($value);
    }

    public function formatDate($date = null, $format = Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, $showTime = true)
    {
        return Mage::helper('core')->formatDate($date, $format, $showTime);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'awaffiliate.customer.withdrawals.pager')
            ->setCollection($this->getWithdrawalCollection());
        $this->setChild('pager', $pager);
        $this->getWithdrawalCollection()->load();

        return $this;
    }

    public function canRequestWithdrawal()
    {
        $activeBalance = Mage::registry('current_affiliate')->getActiveBalance();
        $minimumAmountToWithdraw = Mage::helper('awaffiliate/config')->getMinimumAmountToWithdraw();
        return ($minimumAmountToWithdraw && $activeBalance >= $minimumAmountToWithdraw) || (!$minimumAmountToWithdraw && $activeBalance);
    }

    public function getStatusLabel($item)
    {
        if (is_null($this->_statusSourceModel)) {
            $this->_statusSourceModel = Mage::getModel('awaffiliate/source_withdrawal_status');
        }
        return $this->_statusSourceModel->getOptionLabel($item->getStatus());

    }
}
