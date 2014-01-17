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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     * Set page to redirect
     */
    const PAGE_TO_REDIRECT = "/";  // redirect to homepage
    //const PAGE_TO_REDIRECT = "customer/account/login/"; //redirect to loginpage

    const MAGENTO_VERSION_13 = "mag13";

    const MAGENTO_VERSION_14 = "mag14";
    
    const MSS_HOME_PAGE = 'http://ecommerce.aheadworks.com/magento-extensions/market-segmentation-suite.html';

    public function magentoLess14() {
        return version_compare(Mage::getVersion(), '1.4', '<');
    }
    
    public function getMssLink()
    {
        return self::MSS_HOME_PAGE;
    }

    public function magentoLess142() {
        return version_compare(Mage::getVersion(), '1.4.2', '<');
    }

    public function getRateText($direction, $points, $money) {
        $currencyCode = $this->getStoreCurrencySymbol(Mage::app()->getStore());
        $text = '%s %s = %s %s';
        if ($direction == AW_Points_Model_Rate::POINTS_TO_CURRENCY) {
            $text = sprintf($text, $points, Mage::helper('points/config')->getPointUnitName(), $money, $currencyCode);
        } else {
            $text = sprintf($text, $money, $currencyCode, $points, Mage::helper('points/config')->getPointUnitName());
        }
        return $text;
    }

    public function getStoreCurrencySymbol($store) {
        return Mage::app()->getLocale()->currency($store->getCurrentCurrencyCode())->getSymbol();
    }

    public function getNeededPoints($amount, $customer = null, $storeId = null) {
        $neededPoints = 0;
        try {
            $rate = Mage::getModel('points/rate');
            if (!is_null($storeId)) {
                $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
                $rate->setCurrentWebsite(Mage::app()->getWebsite($websiteId));
            }
            if (!is_null($customer)) {
                $rate->setCurrentCustomer($customer);
            }
            $rate->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY);
            $neededPoints = floor($amount * $rate->getPoints() / $rate->getMoney());
        } catch (Exception $ex) {
            
        }
        return $neededPoints;
    }

    public function getLimitedPoints($sum = 0, $customer = null, $storeId = null) {
        $limitPercent = Mage::helper('points/config')->getPayingAmountPercentLimit($storeId);

        if ($limitPercent) {
            $limitPercent = max(0, intval($limitPercent));
            $sum = $sum * $limitPercent / 100;
        }
        return self::getNeededPoints($sum, $customer, $storeId);
    }
    
    public function extensionEnabled($extensionName)
    {
        $modules = (array) Mage::getConfig()->getNode('modules')->children();
        if (!isset($modules[$extensionName])
                || $modules[$extensionName]->descend('active')->asArray() == 'false'
                || Mage::getStoreConfig("advanced/modules_disable_output/{$extensionName}")
        )
            return false;
        return true;
    }

    public function isAvailableToRedeem($pointsAmount) {
        return Mage::helper('points/config')->getMinimumPointsToRedeem() <= $pointsAmount;
    }

    public function getPageToRedirect() {
        return self::PAGE_TO_REDIRECT;
    }

    public function getBaseUrl($stringToAdd = "") {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . $stringToAdd;
    }

    public function addSelectAll($id) {
        $html = '<span>';
        $html .= '<a href="#" onclick="$$(\'#' . $id . ' option\').each(function(option){option.selected = true})">';
        $html .= 'Select All';
        $html .= '</a>';
        $html .= '</span>';
        return $html;
    }

    public function getInvitationCode($referrer) {

        if (!$referrer) {
            return false;
        }
        $invitationCode = array(
            'referrer_id' => $referrer->getId(),
            'store_id' => $referrer->getData('store_id'),
        );

        $invitationCode = Mage::helper('core/url')->urlEncode(serialize($invitationCode));

        return $invitationCode;
    }

    public function decodeInvitationCode($invitationCode) {

        $invitationData = @unserialize(Mage::helper('core/url')->urlDecode($invitationCode));

        return $invitationData;
    }

    public function getInvitationLink() {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $code = Mage::helper('core/url')->urlEncode($customer->getId());
        return Mage::getUrl('points/invitation/new', array('from' => $code));
    }

}
