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


class AW_Affiliate_Helper_Notify extends Mage_Core_Helper_Abstract
{

    public function sendNotifyAboutNewWithdrawalRequest($request, $storeId = null)
    {
        $template = Mage::helper('awaffiliate/config')->getNotifyTemplateWithdrawalNew($storeId);
        try {
            $affiliateData = $request->getAffiliate()->getData();
            $vars = array('request_id' => $request->getData('id'),
                'customer_firstname' => $affiliateData['firstname'],
                'customer_lastname' => $affiliateData['lastname'],
                'amount_in_currency' => Mage::helper('core')->currency($request->getData('amount')),
                'customer_email' => $affiliateData['email'],
                'request_details' => nl2br($request->getData('description')),
                'link_to_withdrawal' => Mage::helper('adminhtml')->getUrl('affiliate_admin/adminhtml_affiliate/edit', array(
                    'id' => $request->getData('affiliate_id'),
                    'back' => 'edit',
                    'tab' => 'balance_section'
                ))
            );

            $result = $this->_sendNotify($template, $storeId, $vars);
        }
        catch (Exception $e) {
            //TODO: log
            return false;
        }
        return $result;
    }

    public function sendNotifyAboutSuccessWithdrawalRequest($request, $storeId = null)
    {
        $template = Mage::helper('awaffiliate/config')->getNotifyTemplateWithdrawalSuccess($storeId);
        try {
            $affiliateData = $request->getAffiliate()->getData();
            $vars = array('request_id' => $request->getData('id'),
                'customer_firstname' => $affiliateData['firstname'],
                'customer_lastname' => $affiliateData['lastname'],
                'amount_in_currency' => Mage::helper('core')->currency($request->getData('amount')),
                'customer_email' => $affiliateData['email'],
                'link_to_withdrawal' => Mage::helper('adminhtml')->getUrl('affiliate/customer_affiliate/view'),
            );
            $result = $this->_sendNotify($template, $storeId, $vars, $affiliateData['email']);
        }
        catch (Exception $e) {
            //TODO: log
            return false;
        }
        return $result;
    }

    public function sendNotifyAboutFailedWithdrawalRequest($request, $storeId = null)
    {
        $template = Mage::helper('awaffiliate/config')->getNotifyTemplateWithdrawalFailed($storeId);
        try {
            $affiliateData = $request->getAffiliate()->getData();
            $vars = array('request_id' => $request->getData('id'),
                'customer_firstname' => $affiliateData['firstname'],
                'customer_lastname' => $affiliateData['lastname'],
                'amount_in_currency' => Mage::helper('core')->currency($request->getData('amount')),
                'customer_email' => $affiliateData['email'],
                'link_to_withdrawal' => Mage::helper('adminhtml')->getUrl('affiliate/customer_affiliate/view'),
            );

            $result = $this->_sendNotify($template, $storeId, $vars, $affiliateData['email']);
        }
        catch (Exception $e) {
            //TODO: log
            return false;
        }
        return $result;
    }

    protected function _sendNotify($templateName, $storeId = null, $vars = array(), $recipientEmail = null)
    {
        if (!Mage::helper('awaffiliate/config')->isNotifyEnabled($storeId)) {
            return false;
        }
        $sender = Mage::helper('awaffiliate/config')->getNotifySender($storeId);
        if (is_null($recipientEmail)) {
            $recipientEmail = Mage::helper('awaffiliate/config')->getNotifyEmailTo($storeId);
        }
        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate
            ->setDesignConfig(array('area' => 'backend', 'store' => $storeId))
            ->sendTransactional(
            $templateName,
            $sender,
            $recipientEmail,
            $recipientEmail,
            $vars,
            $storeId
        );
        return $mailTemplate->getSentSuccess();
    }
}
