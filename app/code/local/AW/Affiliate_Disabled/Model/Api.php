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


class AW_Affiliate_Model_Api
{
    /**
     * @return AW_Affiliate_Model_Resource_Affiliate_Collection
     */
    public function getAffiliates()
    {
        return Mage::getModel('awaffiliate/affiliate')->getCollection()
            ->addStatusFilter();
    }

    /**
     * @param $customerId
     * @return null|AW_Affiliate_Model_Affiliate
     */
    public function getAffiliateByCustomerId($customerId)
    {
        $affiliates = $this->getAffiliates();
        $affiliates->addFieldToFilter('customer_id', array('eq' => $customerId));
        $affiliate = $affiliates->getFirstItem();
        return $affiliate->getId() ? $affiliate : null;
    }

    public function getAffiliateCampaigns($affiliate)
    {
        if (($affiliate instanceof AW_Affiliate_Model_Affiliate) && ($customer = $affiliate->getCustomer())) {
            $groupId = $customer->getGroupId();
            $websiteId = $customer->getWebsiteId();
            /** @var $campaigns AW_Affiliate_Model_Resource_Affiliate_Collection */
            $campaigns = Mage::getModel('awaffiliate/campaign')->getCollection();
            $campaigns->addFilterByWebsite($websiteId)
                ->addFilterByCustomerGroup($groupId)
                ->addStatusFilter()
                ->addDateFilter()
                ->setOrder('active_to ', Varien_Data_Collection::SORT_ORDER_DESC);
            return $campaigns->toArray(array('id', 'name'));
        } else {
            return array();
        }
    }

    /**
     * @param $campaignId
     *
     * @return AW_Affiliate_Model_Campaign|null
     */
    public function getCampaign($campaignId)
    {
        /** @var $campaign AW_Affiliate_Model_Campaign */
        $campaign = Mage::getModel('awaffiliate/campaign')->load($campaignId);
        if ($campaign->getId() && $campaign->isActive()) {
            return $campaign;
        }
        return null;
    }

    /**
     * @param $affiliateId
     * @param $trafficName
     * @return AW_Affiliate_Model_Traffic_Source
     */
    public function getTrafficSourceByAffiliateAndName($affiliateId, $trafficName)
    {
        return Mage::getModel('awaffiliate/traffic_source')->loadByAffiliateAndName($affiliateId, $trafficName);
    }

    public function getAffiliateLink($baseUrl, $campaignId, $affiliateId, $trafficId = null)
    {
        if ($trafficId === null) {
            $trafficId = $this->getTrafficSourceByAffiliateAndName($affiliateId, '')->getId();
        }
        return Mage::helper('awaffiliate/affiliate')->generateAffiliateLink(
            $baseUrl, array(
                AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY => $campaignId,
                AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY => $affiliateId,
                AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE => $trafficId
            )
        );
    }
}
