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


class AW_Affiliate_Customer_AffiliateController extends Mage_Core_Controller_Front_Action
{
    protected function _initAffiliate()
    {
        $customerId = Mage::getSingleton('customer/session')->getId();
        $affiliate = Mage::getModel('awaffiliate/affiliate');

        if ($customerId) {
            $affiliate->loadByCustomerId($customerId);
        }

        Mage::register('current_affiliate', $affiliate);
        return $this;
    }

    protected function _initCampaign($paramKey = 'id')
    {
        $campaignId = $this->getRequest()->getParam($paramKey);
        $campaign = Mage::getModel('awaffiliate/campaign')->load($campaignId);

        if (is_null($campaign->getId())) {
            $this->_getSession()->addError($this->__("Couldn't load compaign by given id"));
            return $this->_redirect('*/*/view');
        }
        Mage::register('current_campaign', $campaign);
        return $this;
    }

    protected function _initAction($title = 'Magento Affiliate')
    {
        // Redirecting to login page when there is no authorized customer
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, TRUE);
        }
        if (is_null(Mage::registry('current_affiliate')->getId())) {
            $this->_redirect('customer/account/index');
            return $this;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('customer/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__($title));

        if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
            $navigationBlock->setActive('awaffiliate/customer_affiliate/view');
        }
        return $this;
    }

    protected function indexAction()
    {
        $this->_redirect('*/*/view');
    }

    protected function viewAction()
    {
        $this->_initAffiliate()
            ->_initAction($this->__('Affiliate Program'))
            ->renderLayout();
        return;
    }

    protected function campaignAction()
    {
        $this->_initAffiliate()
            ->_initCampaign();
        if (!$this->_hasErrors()) {
            $campaign = Mage::registry('current_campaign');
            $this->_initAction($campaign->getName())
                ->renderLayout();
        }
        return;
    }

    protected function reportAction()
    {
        Mage::helper('awaffiliate')->updatePrototypeJS();
        $this->_initAffiliate()
            ->_initAction($this->__('Reports'))
            ->renderLayout();
        return;
    }

    protected function downloadreportAction()
    {
        $filename = $this->__('Report_as_CSV') . '.csv';
        $this->getResponse()->setHeader('Content-Type', 'application/octet-stream');
        $this->getResponse()->setHeader('Content-Disposition', "attachment; filename=\"" . $filename . "\"");
        $this->getResponse()->setHeader('Content-Transfer-Encoding', 'binary');

        $this->_initAffiliate();
        $gridData = Mage::getSingleton('customer/session')->getAffiliateGridForDownload();
        $csvContent = '';
        $element = current($gridData);
        if (is_null($element)) {
            $this->getResponse()->setBody($csvContent);
            return;
        }
        $data = array();
        foreach (array_keys($element) as $key) {
            $data[] = "\"" . $key . "\"";
        }
        $csvContent .= implode(',', $data) . "\n";
        foreach ($gridData as $row) {
            $data = array();
            foreach ($row as $field) {
                $data[] = "\"" . str_replace(array("\"", '\\'), array("\"\"", '\\\\'), $field) . "\"";
            }
            $csvContent .= implode(',', $data) . "\n";
        }
        $this->getResponse()->setBody($csvContent);
        return;
    }

    protected function withdrawalRequestCreateAction()
    {
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Customer is not logged in'));
            $this->_redirect('customer/account/login');
            return;
        }

        $isError = false;
        $this->_initAffiliate();
        $affiliate = Mage::registry('current_affiliate');

        $affiliateId = intval($affiliate->getId());
        if ($affiliateId < 1) {
            $isError = true;
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Unable to get the affiliate ID'));
        }
        $amount = intval($this->getRequest()->getParam('amount', null));
        if (is_null($amount) || ($amount < 1)) {
            $isError = true;
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Incorrect amount'));
        }

        if (!$isError && !Mage::helper('awaffiliate/affiliate')->isWithdrawalRequestAvailableOn($affiliate, $amount)) {
            $isError = true;
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('This amount is not available for request'));
        }
        if (Mage::helper('awaffiliate/config')->getMinimumAmountToWithdraw(Mage::app()->getStore(true)->getId()) > $amount) {
            $isError = true;
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Requested amount is insufficient to withdraw. Minimal request amount is %d %s',
                Mage::helper('awaffiliate/config')->getMinimumAmountToWithdraw(Mage::app()->getStore(true)->getId()), Mage::app()->getBaseCurrencyCode()));
        }
        if (!$isError) {
            $withdrawalRequest = Mage::getModel('awaffiliate/withdrawal_request');
            $withdrawalRequest->setAmount($amount);
            $withdrawalRequest->setDescription(strip_tags($this->getRequest()->getParam('details')));
            $withdrawalRequest->setAffiliateId($affiliateId);
            $withdrawalRequest->setData('created_at', Mage::getModel('core/date')->gmtDate());
            try {
                $withdrawalRequest->save();
                $this->_getSession()->addSuccess(Mage::helper('awaffiliate')->__('Withdrawal request saved'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->setWithdrawalRequestCreateFormData($this->getRequest()->getParams());
        }
        $__defaultUrl = "awaffiliate/customer_affiliate/view";
        $this->_redirectReferer($__defaultUrl);
    }

    protected function getReportAsJsonAction()
    {
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Customer is not logged in'));
            $this->_redirect('customer/account/login');
            return;
        }

        $postData = $this->getRequest()->getParams();
        $this->_getSession()->setCreateReportFormData($postData);

        $messages = array();
        $response = new Varien_Object();
        $response->setError(0);
        $this->_initAffiliate();
        $affiliate = Mage::registry('current_affiliate');

        $affiliateId = intval($affiliate->getId());
        if ($affiliateId < 1) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Unable to get the affiliate ID');
        }

        if (!isset($postData['report_type'])) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Report type is not specified');
        }

        if ($response->getError() == 0) {
            if ($postData['report_type'] == AW_Affiliate_Model_Source_Report_Type::SALES) {
                $block = $this->getLayout()->createBlock('awaffiliate/report_view_sales');
                $block->addData($postData);
                $response->setHtml($block->toHtml());
            } elseif ($postData['report_type'] == AW_Affiliate_Model_Source_Report_Type::TRANSACTIONS) {
                $block = $this->getLayout()->createBlock('awaffiliate/report_view_transactions');
                $block->addData($postData);
                $response->setHtml($block->toHtml());
            } elseif ($postData['report_type'] == AW_Affiliate_Model_Source_Report_Type::TRAFFIC) {
                $block = $this->getLayout()->createBlock('awaffiliate/report_view_traffic');
                $block->addData($postData);
                $response->setHtml($block->toHtml());
            } else {
                $response->setError(1);
                $messages[] = Mage::helper('awaffiliate')->__('Invalid report type');
            }
        }
        $response->setMessages($messages);
        $this->getResponse()->setBody($response->toJson());
        return;
    }

    protected function generateLinkAction()
    {
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Customer is not logged in'));
            $this->_redirect('customer/account/login');
            return;
        }
        $postData = $this->getRequest()->getParams();
        $this->_getSession()->setGenerateLinkFormData($postData);

        $messages = array();
        $response = new Varien_Object();
        $response->setError(0);
        $this->_initAffiliate();
        $this->_initCampaign('campaign_id');

        $affiliate = Mage::registry('current_affiliate');
        if (intval($affiliate->getId()) < 1) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Unable to get the affiliate ID');
        }

        $campaign = Mage::registry('current_campaign');
        if (is_null($campaign)) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Unable to get the campaign ID');
        }

        if (!isset($postData['link_to_generate']) || (strlen($postData['link_to_generate']) == 0)) {
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('Tracking Link is not specified');
        }

        if ($response->getError() == 0) {
            $collection = Mage::getModel('awaffiliate/traffic_source')->getCollection();
            $collection->addFieldToFilter('main_table.traffic_name', array("eq" => $postData['traffic_source_generate']));
            $collection->addFieldToFilter('main_table.affiliate_id', array("eq" => $affiliate->getId()));
            $collection->setPageSize(1);

            if (!$collection->getSize()) {
                $trafficItem = Mage::getModel('awaffiliate/traffic_source');
                $trafficItem->setData(array(
                    'affiliate_id' => $affiliate->getId(),
                    'traffic_name' => $postData['traffic_source_generate']
                ));
                $trafficItem->save();
                $trafficId = $trafficItem->getId();
            } else {
                $trafficId = $collection->getFirstItem()->getId();
            }
        }

        if ($response->getError() == 0) {
            $baseUrl = trim($postData['link_to_generate']);
            $params = array(
                AW_Affiliate_Helper_Affiliate::CAMPAIGN_REQUEST_KEY => $campaign->getId(),
                AW_Affiliate_Helper_Affiliate::AFFILIATE_REQUEST_KEY => $affiliate->getId(),
                AW_Affiliate_Helper_Affiliate::AFFILIATE_TRAFFIC_SOURCE => $trafficId
            );
            $resultUrl = Mage::helper('awaffiliate/affiliate')->generateAffiliateLink($baseUrl, $params);
            $response->setData('result', $resultUrl);
        }
        $response->setMessages($messages);
        $this->getResponse()->setBody($response->toJson());
        return;
    }

    private function _getSession()
    {
        return Mage::getSingleton('core/session');
    }

    protected function _hasErrors()
    {
        return (bool)count($this->_getSession()->getMessages()->getItemsByType('error'));
    }
}
