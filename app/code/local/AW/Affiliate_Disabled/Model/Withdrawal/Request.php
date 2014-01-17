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


class AW_Affiliate_Model_Withdrawal_Request extends Mage_Core_Model_Abstract
{
    const ENTITY = 'awaffiliate_withdrawal_request';

    protected $_affiliate = null;

    protected $_eventPrefix = self::ENTITY;
    protected $_eventObject = 'request';

    public function _construct()
    {
        $this->_init('awaffiliate/withdrawal_request');
    }

    public function loadByTransactionId($id)
    {
        return $this->load($id, 'transaction_id');
    }

    protected function _beforeSave()
    {
        $res = parent::_beforeSave();
        Mage::getSingleton('index/indexer')->logEvent($this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE);
        if ($this->isStatusChangedToPaid()) {
            if ($this->getData('transaction_id') > 0) {
                return $res;
            }
            $withdrawalTrx = Mage::getModel('awaffiliate/transaction_withdrawal');
            $withdrawalTrx->setData(array(
                'amount' => $this->getData('amount'),
                'created_at'=>Mage::getModel('core/date')->gmtDate(),
                'currency_code' => Mage::helper('awaffiliate')->getDefaultCurrencyCode()
            ));
            try {
                $withdrawalTrx->createTransaction($this->getData('affiliate_id'));
                $this->setData('transaction_id', $withdrawalTrx->getId());
            }
            catch (Exception $e) {
                throw $e;
            }
        }
        if ($this->isObjectNew()) {
            $this->setStatus(AW_Affiliate_Model_Source_Withdrawal_Status::INITIAL_STATUS);
        }
        return $res;
    }

    public function afterCommitCallback()
    {
        parent::afterCommitCallback();
        Mage::getSingleton('index/indexer')->indexEvents(self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE);
        return $this;
    }

    protected function _isStatusChanged()
    {
        if ($this->isObjectNew()) {
            return false;
        }
        return ($this->getOrigData('status') != $this->getData('status'));
    }

    public function isStatusChangedToPaid()
    {
        if ($this->_isStatusChanged() && $this->getData('status') == AW_Affiliate_Model_Source_Withdrawal_Status::PAID) {
            return true;
        }
        return false;
    }

    public function isStatusChangedToFailed()
    {
        if ($this->_isStatusChanged() && $this->getData('status') == AW_Affiliate_Model_Source_Withdrawal_Status::FAILED) {
            return true;
        }
        return false;
    }

    public function isStatusChangedToRejected()
    {
        if ($this->_isStatusChanged() && $this->getData('status') == AW_Affiliate_Model_Source_Withdrawal_Status::REJECTED) {
            return true;
        }
        return false;
    }

    public function isStatusChangedFromPaid()
    {
        if ($this->_isStatusChanged() && $this->getOrigData('status') == AW_Affiliate_Model_Source_Withdrawal_Status::PAID) {
            return true;
        }
        return false;
    }

    public function getAffiliate()
    {
        if ($this->_affiliate === null) {
            if ($affiliateId = $this->getData('affiliate_id')) {
                $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
                if ($affiliate->getData()) {
                    $this->_affiliate = $affiliate;
                }
            }
        }
        return $this->_affiliate;
    }

    public function joinCustomerFullName()
    {
        if ($this->getData('customer_full_name') === null && ($affiliate = $this->getAffiliate())) {
            $fullName = $affiliate->getData('firstname') . ' ' . $affiliate->getData('lastname');
            if ($fullName) {
                $this->setData('customer_full_name', $fullName);
            }
        }
        return $this;
    }
}
