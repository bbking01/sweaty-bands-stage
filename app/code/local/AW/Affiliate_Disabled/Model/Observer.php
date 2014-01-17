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


class AW_Affiliate_Model_Observer
{
    const MINIMUM_WITHDRAWAL_PERIOD_REGISTRY_KEY = '_awaff_mwrk';

    public function allFrontControllersPredispatch($observer)
    {
        $controller = $observer->getControllerAction();
        $cmid = $controller->getRequest()->getParam(AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY, null);
        $afid = $controller->getRequest()->getParam(AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY, null);
        $ats = $controller->getRequest()->getParam(AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE, null);

        if (!is_null($cmid) && !is_null($afid) && !is_null($ats)) {
            //params detect
            $campaignId = Mage::helper('core')->decrypt(Mage::helper('core')->urlDecode($cmid));
            $affiliateId = Mage::helper('core')->decrypt(Mage::helper('core')->urlDecode($afid));
            $trafficSourceId = Mage::helper('core')->decrypt(Mage::helper('core')->urlDecode($ats));
            $cookieModel = Mage::getSingleton('core/cookie');
            $clientId = $cookieModel->get(AW_Affiliate_Helper_Config::COOKIE_NAME);
            if (
                $this->_isCookieFree($clientId) &&
                $this->_isAffiliateAccessible($affiliateId) &&
                $this->_isCampaignAccessible($campaignId) &&
                $this->_isTrafficSourceAvailable($trafficSourceId) &&
                $this->_isAffiliateAllowedForCampaign($affiliateId, $campaignId)
            ) {
                $newClient = Mage::getModel('awaffiliate/client');
                $newClient->setData(array(
                    'campaign_id' => $campaignId,
                    'affiliate_id' => $affiliateId,
                    'traffic_id' => $trafficSourceId,
                    'customer_id' => Mage::getSingleton('customer/session')->getId()
                ));
                if ($this->_isNewClientNotEqualCurrentClient($newClient, $clientId)) {
                    try {
                        $newClient->save();
                        $cookieModel->set(AW_Affiliate_Helper_Config::COOKIE_NAME, $newClient->getId(), true); //set cookie on one year
                    } catch (Exception $e) {
                    }
                }
            }
            //redirect
            $currentUrl = Mage::helper('core/url')->getCurrentUrl();
            $redirectUrl = Mage::helper('awaffiliate')->removeRequestParam($currentUrl, AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY);
            $redirectUrl = Mage::helper('awaffiliate')->removeRequestParam($redirectUrl, AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY);
            $redirectUrl = Mage::helper('awaffiliate')->removeRequestParam($redirectUrl, AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE);
            $controller->getResponse()->setRedirect($redirectUrl)->sendResponse();
            die();
        }
        return;
    }

    public function customerLogin($observer)
    {
        $customerId = Mage::getSingleton('customer/session')->getId();

        if (!$customerId) return;
        $cookieModel = Mage::getSingleton('core/cookie');
        $campaingCollection = Mage::getModel('awaffiliate/campaign')->getCollection();

        foreach ($campaingCollection as $campaingItem) {
            $clientId = $cookieModel->get(AW_Affiliate_Helper_Config::COOKIE_NAME);
            if ($clientId) {
                $clientItem = Mage::getModel('awaffiliate/client')->load($clientId);
                if (($clientItem->getData()) && ($clientItem->getCustomerId() == NULL)) {
                    $clientItem->setData('customer_id', $customerId);
                    $clientItem->save();
                }
            }
        }
    }

    public function orderPlaced($observer)
    {
        $order = $observer->getOrder();
        $client = Mage::helper('awaffiliate')->getAffiliateCookie();

        //add client history
        if (!is_null($client)) {
            $clientModel = Mage::getModel('awaffiliate/client')->load($client);
            $affiliateModel = Mage::getModel('awaffiliate/affiliate')->load($clientModel->getAffiliateId());

            if (is_null($clientModel->getId())) {
                return;
            }
            if ($order->getCustomerId() == $affiliateModel->getCustomerId()) {
                return;
            }
            $historyModel = Mage::getModel('awaffiliate/client_history');
            $historyModel->setData(array(
                'client_id' => $clientModel->getId(),
                'action' => AW_Affiliate_Model_Source_Client_Actions::ORDER_PLACED,
                'linked_item_id' => $order->getId(),
                'created_at' => Mage::getModel('core/date')->gmtDate(),
                'params' => array()
            ));
            try {
                $historyModel->save();
            } catch (Exception $e) {
                //TODO: log
            }
        }
    }

    public function invoicePay($observer)
    {
        /** @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getInvoice();
        /** @var $order Mage_Sales_Model_Order */
        $order = $invoice->getOrder();
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote');

        if ($invoice->getOrigData() !== null) {
            // Exit if invoice isn't new
            return;
        }

        if (method_exists($quote, 'loadByIdWithoutStore')) {
            $quote->loadByIdWithoutStore($order->getQuoteId());
        } else {
            $quote
                ->setStoreId($order->getStoreId())
                ->load($order->getQuoteId());
        }
        //Order placed type
        $historyCollection = Mage::getModel('awaffiliate/client_history')->getCollection();
        $historyCollection->onlyOrderPlacedAction();
        $historyCollection->addLinkedItemIdFilter($invoice->getOrder()->getId());
        if ($historyCollection->getSize() == 0) {
            return;
        }

        foreach ($historyCollection as $action) {
            $client = $action->getClient();

            if (is_null($client->getId())) {
                //TODO: log
                continue;
            }
            if (!$this->_isAffiliateAccessible($client->getAffiliateId())) {
                //TODO: log
                continue;
            }
            if (!$this->_isCampaignAccessible($client->getCampaignId())) {
                //TODO: log
                continue;
            }
            if (!$this->_isAffiliateAllowedForCampaign($client->getAffiliateId(), $client->getCampaignId(), true)) {
                //TODO: log
                continue;
            }

            $campaign = $client->getCampaign();
            $conditionsModel = $campaign->getConditionsModel();

            $order->setQuote($quote);
            if ($conditionsModel->getActions()->validate($order)) {
                /** @var $trx AW_Affiliate_Model_Transaction_Profit */
                $trx = Mage::getModel('awaffiliate/transaction_profit');
                $trx->setData(array(
                    'campaign_id' => $client->getData('campaign_id'),
                    'affiliate_id' => $client->getData('affiliate_id'),
                    'traffic_id' => $client->getData('traffic_id'),
                    'client_id' => $client->getId(),
                    'linked_entity_type' => AW_Affiliate_Model_Source_Transaction_Profit_Linked::INVOICE_ITEM,
                    'linked_entity_id' => $order->getIncrementId(),
                    'linked_entity_invoice' => $invoice,
                    'linked_entity_order' => $order,
                    'created_at' => Mage::getModel('core/date')->gmtDate(),
                    'type' => AW_Affiliate_Model_Source_Transaction_Profit_Type::CUSTOMER_PURCHASE
                ));
                try {
                    $trx->createTransaction();
                } catch (Exception $e) {
                    var_dump($e->getMessage());
                    //TODO: log
                }
            }
        }
    }

    public function customerSaveAfter($observer)
    {
        $customer = $observer->getCustomer();
        $groupId = $customer->getGroupId();
        $targetGroups = Mage::helper('awaffiliate/config')->getGroupsForAutoAffiliateCreating($customer->getStoreId());
        $affiliate = Mage::getModel('awaffiliate/affiliate')->loadByCustomerId($customer->getId());
        if (is_null($affiliate->getId()) && in_array($groupId, $targetGroups)) {
            $affiliate->setData(array(
                'customer_id' => $customer->getId(),
                'status' => AW_Affiliate_Model_Source_Affiliate_Status::ACTIVE
            ));
            $affiliate->save();
        }
    }

    public function withdrawalRequestSaveAfter($observer)
    {
        $request = $observer->getRequest();
        $notify = Mage::helper('awaffiliate/notify');
        if ($request->isStatusChangedToPaid()) {
            $notify->sendNotifyAboutSuccessWithdrawalRequest($request);
        }
        if ($request->isStatusChangedToFailed() || $request->isStatusChangedToRejected()) {
            $notify->sendNotifyAboutFailedWithdrawalRequest($request);
        }
        if ($request->isObjectNew()) {
            $notify->sendNotifyAboutNewWithdrawalRequest($request);
        }
    }

    private function _isCookieFree($cookieValue)
    {
        $client = Mage::getModel('awaffiliate/client')->load($cookieValue);
        $__isRewriteCookieEnabled = Mage::helper('awaffiliate/config')->isRewriteCookieEnabled();
        return is_null($client->getId()) || $__isRewriteCookieEnabled;
    }

    private function _isAffiliateAccessible($affiliateId)
    {
        $_affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
        if (is_null($_affiliate->getId())) {
            return false;
        }
        if ($_affiliate->getStatus() == AW_Affiliate_Model_Source_Affiliate_Status::INACTIVE) {
            return false;
        }
        return true;
    }

    private function _isCampaignAccessible($campaignId)
    {
        $_campaign = Mage::getModel('awaffiliate/campaign')->load($campaignId);
        return (!is_null($_campaign->getId()) && $_campaign->isActive());
    }

    private function _isAffiliateAllowedForCampaign($affiliateId, $campaignId, $checkDate = false)
    {
        $_campaign = Mage::getModel('awaffiliate/campaign')->load($campaignId);
        if ($checkDate) {
            $curTimestamp = Mage::app()->getLocale()->storeTimeStamp();
            if (!((is_null($_campaign->getActiveFrom()) OR (strtotime($_campaign->getActiveFrom()) < $curTimestamp)) &&
                (is_null($_campaign->getActiveTo()) OR (strtotime($_campaign->getActiveTo()) + 86400 > $curTimestamp)))
            ) {

                return false;
            }
        }
        return $_campaign->isAffiliateAllowed($affiliateId);
    }

    private function _isTrafficSourceAvailable($trafficId)
    {
        $_traffic = Mage::getModel('awaffiliate/traffic_source')->load($trafficId);
        return (!is_null($_traffic->getId()) ? $_traffic->getId() : false);
    }

    /*check duplicate object*/
    private function _isNewClientNotEqualCurrentClient($newClient, $currentClientId)
    {
        $client = Mage::getModel('awaffiliate/client')->load($currentClientId);
        if (
            $newClient->getCampaignId() != $client->getCampaignId() ||
            $newClient->getAffiliateId() != $client->getAffiliateId()
        ) {
            return true;
        }
        return false;
    }

    public function beforeConfigurationSave($observer)
    {
        $controller = $observer->getControllerAction();
        if ($controller->getRequest()->getParam('section') === 'awaffiliate') {
            $previousMinimumWithdrawalPeriod = Mage::helper('awaffiliate/config')->getMinimumWithdrawalPeriod();
            Mage::register(self::MINIMUM_WITHDRAWAL_PERIOD_REGISTRY_KEY, $previousMinimumWithdrawalPeriod);
        }
    }

    public function afterConfigurationSave($observer)
    {
        if (($previousMinimumWithdrawalPeriod = Mage::registry(self::MINIMUM_WITHDRAWAL_PERIOD_REGISTRY_KEY)) !== null) {
            $currentMinimumWithdrawalPeriod = Mage::helper('awaffiliate/config')->getMinimumWithdrawalPeriod();
            if ($previousMinimumWithdrawalPeriod != $currentMinimumWithdrawalPeriod) {
                // Invalidate Affiliates balance index
                Mage::getSingleton('index/indexer')->getProcessByCode('awaffiliate_affiliate_balance')
                    ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            }
        }
    }

    public function checkPrototype($observer)
    {
        if ((($block = $observer->getBlock()) instanceof Mage_Page_Block_Html_Head)
            && (Mage::helper('awaffiliate')->isNewPrototypeRequired())
        ) {
            $items = $block->getData('items');
            foreach ($items as $k => &$v) {
                if (strcmp($v['name'], 'prototype/prototype.js') === 0) {
                    $v['name'] = 'aw_affiliate/prototype.js';
                }
            }
            $block->setData('items', $items);
        }
    }
}
