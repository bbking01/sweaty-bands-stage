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


class AW_Affiliate_Helper_Config extends Mage_Core_Helper_Abstract
{
    /*NOT CHANGE THIS!!!*/
    const COOKIE_NAME = 'awaffiliate-client';

    const PATH_CONFIG_REWRITE_COOKIE = "awaffiliate/general/rewrite_affiliate_cookie";
    const PATH_CONFIG_CONSIDER_TAX = "awaffiliate/general/consider_tax";
    const PATH_CONFIG_AUTO_AFFILIATE_CREATE = "awaffiliate/general/account_automatically_creating";

    const PATH_CONFIG_NOTIFY_ENABLED = "awaffiliate/notification/enable";
    const PATH_CONFIG_NOTIFY_SENDER = "awaffiliate/notification/email_sender";
    const PATH_CONFIG_NOTIFY_EMAIL_TO = "awaffiliate/notification/send_to";

    const PATH_CONFIG_NOTIFY_TEMPLATE_WTHD_NEW = "awaffiliate/notification/new_withdrawal_template";
    const PATH_CONFIG_NOTIFY_TEMPLATE_WTHD_SUCCESS = "awaffiliate/notification/withdrawal_succeed_template";
    const PATH_CONFIG_NOTIFY_TEMPLATE_WTHD_FAILED = "awaffiliate/notification/withdrawal_failed_template";

    const PATH_CONFIG_WITHDRAWALS_MINIMUM_AMOUNT = 'awaffiliate/withdrawals/minimum_amount';
    const PATH_CONFIG_WITHDRAWALS_MINIMUM_PERIOD = 'awaffiliate/withdrawals/minimum_period';

    public function isRewriteCookieEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::PATH_CONFIG_REWRITE_COOKIE, $storeId);
    }

    public function isConsiderTax($storeId = null)
    {
        return Mage::getStoreConfig(self::PATH_CONFIG_CONSIDER_TAX, $storeId);
    }

    public function getGroupsForAutoAffiliateCreating($storeId = null)
    {
        $groupsAsString = Mage::getStoreConfig(self::PATH_CONFIG_AUTO_AFFILIATE_CREATE, $storeId);
        return @explode(',', $groupsAsString);
    }

    public function isNotifyEnabled($storeId = null)
    {
        return Mage::getStoreConfig(self::PATH_CONFIG_NOTIFY_ENABLED, $storeId);
    }

    public function getNotifySender($storeId = null)
    {
        return Mage::getStoreConfig(self::PATH_CONFIG_NOTIFY_SENDER, $storeId);
    }

    public function getNotifyEmailTo($storeId = null)
    {
        return Mage::getStoreConfig(self::PATH_CONFIG_NOTIFY_EMAIL_TO, $storeId);
    }

    public function getNotifyTemplateWithdrawalNew($storeId = null)
    {
        return Mage::getStoreConfig(self::PATH_CONFIG_NOTIFY_TEMPLATE_WTHD_NEW, $storeId);
    }

    public function getNotifyTemplateWithdrawalSuccess($storeId = null)
    {
        return Mage::getStoreConfig(self::PATH_CONFIG_NOTIFY_TEMPLATE_WTHD_SUCCESS, $storeId);
    }

    public function getNotifyTemplateWithdrawalFailed($storeId = null)
    {
        return Mage::getStoreConfig(self::PATH_CONFIG_NOTIFY_TEMPLATE_WTHD_FAILED, $storeId);
    }

    public function getMinimumAmountToWithdraw($storeId = null)
    {
        return Mage::getStoreConfig(self::PATH_CONFIG_WITHDRAWALS_MINIMUM_AMOUNT, $storeId);
    }

    public function getMinimumWithdrawalPeriod($storeId = null)
    {
        return Mage::getStoreConfig(self::PATH_CONFIG_WITHDRAWALS_MINIMUM_PERIOD, $storeId);
    }
}
