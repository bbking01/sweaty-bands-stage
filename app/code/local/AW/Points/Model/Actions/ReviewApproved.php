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


class AW_Points_Model_Actions_ReviewApproved extends AW_Points_Model_Actions_Abstract {

    protected $_action = 'review_approved';
    protected $_comment = 'Reward for reviewing product %s';

    protected function _applyLimitations($amount) {

        $review = $this->getObjectForAction();

        $pointLimitForAction = Mage::helper('points/config')
                ->getPointsLimitForReviewingProduct($review->getStoreId());

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

    public function getCommentHtml($area = self::ADMIN) {
        return Mage::helper('points')->__($this->_transaction->getComment());
    }

    public function getComment() {
        if (isset($this->_commentParams['product_name'])) {
            return Mage::helper('points')->__($this->_comment, $this->_commentParams['product_name']);
        }
        return $this->_comment;
    }

}

?>
