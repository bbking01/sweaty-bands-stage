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


class AW_FBIntegrator_Model_Like_Validator extends Varien_Object {

    public function isAlreadyLiked($customerId, $url) {
        $like = Mage::getModel('fbintegrator/like')->getStoredLike($customerId, $url);
        return (bool) $like->getId();
    }

    public function getTimeToWait($customerId) {

        $timeToWait = 0;
        $delay = Mage::helper('fbintegrator/like')->getDelay();
        if ($delay) {
            $now = Mage::getModel('core/date')->gmtTimestamp();
            $lastLike = Mage::getModel('fbintegrator/like')
                    ->getCollection()
                    ->getLastLike($customerId);
            $timeToWait = max(0, $lastLike->getLikeTime() + $delay - $now);
        }
        return $timeToWait;
    }

    public function limitLikesPerTimeReached($customerId) {

        $maxLikeCount = Mage::helper('fbintegrator/like')->getMaxCount();
        $time = Mage::helper('fbintegrator/like')->getTime();

        if ($maxLikeCount && $time) {

            $now = Mage::getModel('core/date')->gmtTimestamp();
            $likeCount = Mage::getModel('fbintegrator/like')->getCollection()
                    ->addFieldToFilter('customer_id', array("eq" => $customerId))
                    ->addFieldToFilter('like_time', array("gt" => ($now - $time)))
                    ->getSize();

            if ($likeCount >= $maxLikeCount) {
                return true;
            }
        }
        return false;
    }

}