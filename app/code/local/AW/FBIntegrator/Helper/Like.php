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
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_FBIntegrator
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_FBIntegrator_Helper_Like extends Mage_Core_Helper_Abstract {

    public function getPoints() {
        $points = intval(Mage::getStoreConfig('points/earning_points/fb_like_points'));
        return $points;
    }

    public function getDelay() {
        return intval(Mage::getStoreConfig('points/earning_points/fb_like_delay'));
    }

    public function getMaxCount() {
        return intval(Mage::getStoreConfig('points/earning_points/fb_like_max_like_count'));
    }

    public function getTime() {
        return intval(Mage::getStoreConfig('points/earning_points/fb_like_time'));
    }

    public function canGetPoints() {
        $pointsForLike = Mage::helper('fbintegrator/like')->getPoints();
        $isPointsExtEnabled = Mage::getConfig()->getModuleConfig('AW_Points')->is('active', 'true');

        if (   $pointsForLike && $isPointsExtEnabled
                && Mage::getSingleton('customer/session')->isLoggedIn()) {
            return true;
        }

        return false;
    }
    
    public function isAlreadyLiked($url, $customerId = null)
    {
        if ($customerId === null) {
            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                return false;
            }
            $customerId = Mage::getSingleton('customer/session')
                            ->getCustomer()->getId();
        }
        $validator = Mage::getModel('fbintegrator/like_validator');

        return $validator->isAlreadyLiked($customerId, $url);
    }

}