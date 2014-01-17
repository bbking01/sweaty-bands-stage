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


class AW_Affiliate_Block_Customer_Withdrawal_Form extends Mage_Core_Block_Template
{
    const DEFAULT_AMOUNT = 0;

    private $_defaultFormData = array();

    public function __construct()
    {
        parent::__construct();
        $_template = 'aw_affiliate/customer/form.phtml';
        $this->setTemplate($_template);
        $this->_defaultFormData = Mage::getSingleton('customer/session')->getWithdrawalRequestCreateFormData();
        Mage::getSingleton('customer/session')->unsetData('withdrawal_request_create_form_data');
        return $this;
    }

    public function getDefaultAmount()
    {
        //is session contain form data
        if (is_array($this->_defaultFormData) && array_key_exists('amount', $this->_defaultFormData)) {
            return $this->_defaultFormData['amount'];
        }
        $affiliate = Mage::registry('current_affiliate');
        if (!$affiliate->hasId()) {
            return self::DEFAULT_AMOUNT;
        }
        //get total requested amount without paid requests
        $totalRequested = Mage::helper('awaffiliate/affiliate')->getTotalRequested($affiliate);
        $defaultAmount = $affiliate->getActiveBalance() - $totalRequested;
        if (intval($defaultAmount) < 0) {
            return self::DEFAULT_AMOUNT;
        }
        return intval($defaultAmount);
    }

    public function getDefaultDetails()
    {
        //is session contain form data
        if (is_array($this->_defaultFormData) && array_key_exists('details', $this->_defaultFormData)) {
            return $this->_defaultFormData['details'];
        }
        $details = Mage::helper('awaffiliate/affiliate')->getLastWithdrawalRequestDetails(Mage::registry('current_affiliate'));
        return $details;
    }

    public function getCurrencySymbol()
    {
        return Mage::helper('awaffiliate')->getDefaultCurrencySymbol();
    }

    public function getAction()
    {
        return Mage::getUrl('awaffiliate/customer_affiliate/withdrawalRequestCreate');
    }
}
