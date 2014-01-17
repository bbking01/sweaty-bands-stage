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


class AW_Affiliate_Block_Customer_Campaigns extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $_template = 'aw_affiliate/customer/campaigns.phtml';
        $this->setTemplate($_template);
        return $this;
    }

    private $_campaignsCollection = null;

    public function getCampaignCollection()
    {
        if (!$this->_campaignsCollection instanceof AW_Affiliate_Model_Resource_Campaign_Collection) {
            $__group = Mage::registry('current_affiliate')->getCustomerGroupId();
            $this->_campaignsCollection = Mage::getModel('awaffiliate/campaign')->getCollection();
            $this->_campaignsCollection
                ->joinProfitCollection()
                ->addFilterByWebsite(Mage::app()->getWebsite()->getId())
                ->addFilterByCustomerGroup($__group)
                ->addStatusFilter()
                ->addDateFilter()
                ->setOrder('active_to ', 'DESC');
        }
        return $this->_campaignsCollection;
    }

    public function setCampaignCollection(AW_Affiliate_Model_Resource_Campaign_Collection $collection)
    {
        $this->_campaignsCollection = $collection;
        return $this;
    }

    public function getRate($item)
    {
        $rate = $item->getProfitModel()->getRateForAffiliate(Mage::registry('current_affiliate'));
        switch ($item->getRateType()) {
            case AW_Affiliate_Model_Source_Profit_Type::TIER:
            case AW_Affiliate_Model_Source_Profit_Type::FIXED:
                $rate = $rate . ' %';
                break;
            case AW_Affiliate_Model_Source_Profit_Type::TIER_CUR:
            case AW_Affiliate_Model_Source_Profit_Type::FIXED_CUR:
                $rate = $this->formatCurrency($rate);
                break;
        }

        return $rate;
    }

    public function getCampaignStatusLabel($value)
    {
        $option = Mage::getModel('awaffiliate/source_campaign_status')->getOption($value);
        return $option['label'];
    }

    public function getTotalAmountByCampaignId($campaignId)
    {
        $amount = Mage::helper('awaffiliate/affiliate')->getAmountForAffiliate(Mage::registry('current_affiliate'), $campaignId);
        return $this->formatCurrency($amount);
    }

    public function getLastMonthAmountByCampaignId($campaignId)
    {
        $amount = Mage::helper('awaffiliate/affiliate')->getLastMonthAmountForAffiliate(Mage::registry('current_affiliate'), $campaignId);
        return $this->formatCurrency($amount);
    }

    public function getUrlToCampaignView($campaignId)
    {
        return Mage::getUrl('awaffiliate/customer_affiliate/campaign', array('id' => $campaignId));
    }

    public function formatCurrency($value)
    {
        return Mage::helper('core')->currency($value);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'awaffiliate.customer.withdrawals.pager')
            ->setCollection($this->getCampaignCollection());
        $this->setChild('pager', $pager);
        $this->getCampaignCollection()->load();

        return $this;
    }
}
