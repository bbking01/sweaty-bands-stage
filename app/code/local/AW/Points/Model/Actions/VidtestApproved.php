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


class AW_Points_Model_Actions_VidtestApproved extends AW_Points_Model_Actions_Abstract {

    protected $_action = 'vidtest_approved';
    protected $_comment = 'Video Testimonial upload';

    protected function _applyLimitations($amount) {

        $obj = $this->getObjectForAction();

        $pointLimitForAction = Mage::helper('points/config')
                ->getPointsLimitForVideoTestimonial($obj->getStoreId());

        $collection = Mage::getModel('points/transaction')
                ->getCollection()
                ->addFieldToFilter('summary_id', $this->getSummary()->getId())
                ->addFieldToFilter('action', $this->getAction())
                ->limitByDay(Mage::getModel('core/date')->gmtTimestamp());

        /* Current summ getting */
        $summ = 0;
        foreach ($collection as $transaction) {
            $summ += $transaction->getBalanceChange();
        }

        return parent::_applyLimitations($this->_calculateNewAmount($summ, $amount, $pointLimitForAction));
    }

}