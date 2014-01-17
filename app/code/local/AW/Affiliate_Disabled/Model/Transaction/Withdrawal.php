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


class AW_Affiliate_Model_Transaction_Withdrawal extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        $this->_init('awaffiliate/transaction_withdrawal');
    }

    public function createTransaction($affiliateId)
    {
        if ($this->_isValidForCreation()) {
            $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
            if (is_null($affiliate->getId())) {
                Mage::throwException($this->__('Affiliate has not been found'));
            }
            $this->save();
        }
        else {
            Mage::throwException($this->__('Not valid for create transaction'));
        }
        return $this;
    }

    public function getWithdrawalRequest()
    {
        $transactionId = $this->getData('transaction_id');
        if (is_null($transactionId) || intval($transactionId) < 1) {
            return null;
        }
        $requestModel = Mage::getModel('awaffiliate/withdrawal_request')->loadByTransactionId($transactionId);
        return $requestModel;
    }

    public function getAffiliate()
    {
        $withdrawalRequest = $this->getWithdrawalRequest();
        if (is_null($withdrawalRequest)) {
            return null;
        }
        $affiliateId = $withdrawalRequest->getData('affiliate_id');
        $affiliate = Mage::getModel('awaffiliate/affiliate')->load($affiliateId);
        return $affiliate;
    }

    protected function _isValidForCreation()
    {
        if (is_null($this->getData('amount'))) {
            return false;
        }
        if (is_null($this->getData('currency_code'))) {
            return false;
        }
        return true;
    }

    protected function _beforeSave()
    {
        $res = parent::_beforeSave();
        if (is_null($this->getData('currency_code'))) {
            Mage::throwException(Mage::helper('awaffiliate')->__('Currency code is not specified'));
        }
        return $res;
    }

    protected function _afterSave()
    {
        $res = parent::_afterSave();
        $affiliate = $this->getAffiliate();
        if (!is_null($affiliate)) {
            $affiliate->recollectBalances();
        }
        return $res;
    }
}
