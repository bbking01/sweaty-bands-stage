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


class AW_Affiliate_Model_Profit_Tier extends Varien_Object implements AW_Affiliate_Model_Profit_Interface
{
    protected $_rates = null;
    protected $_attractionAmount = null;
    protected $_customerGroup = null;

    protected function _construct()
    {
        parent::_construct();
    }

    public function getRate()
    {
        if (is_null($this->_rates) || is_null($this->_attractionAmount) || is_null($this->_customerGroup)) {
            return null;
        }
        $_minimalDifference = array();
        foreach ($this->_rates as $rate) {
            if (intval($rate->getProfitAmount()) < 0 || !$rate->isValidGroupId($this->_customerGroup)) {
                continue;
            }
            if (empty($_minimalDifference)) {
                $__difference = $this->_attractionAmount - intval($rate->getProfitAmount());
                if ($__difference >= 0) {
                    $_minimalDifference = array('id' => $rate->getId(), 'difference' => $__difference);
                }
            }
            else {
                $__difference = $this->_attractionAmount - intval($rate->getProfitAmount());
                if (($__difference >= 0) && ($_minimalDifference['difference'] > $__difference)) {
                    $_minimalDifference = array('id' => $rate->getId(), 'difference' => $__difference);
                }
            }

        }
        if (empty($_minimalDifference)) {
            return AW_Affiliate_Model_Profit_Tier_Rate::DEFAULT_RATE_AMOUNT;
        }
        $__rateModel = $this->_rates->getItemById($_minimalDifference['id']);
        $this->_rate = $__rateModel->getProfitRate();
        return $this->_rate;
    }

    public function loadRatesByProfitId($id)
    {
        if (is_null($id)) {
            return null;
        }
        $this->_rates = Mage::getModel('awaffiliate/profit_tier_rate')->loadByProfitId($id);
        return $this;
    }

    public function getProfitAmount($attractionAmount)
    {
        $rate = $this->getRate();
        if (is_null($rate)) {
            return null;
        }
        $amount = $rate * $attractionAmount;
        return $amount;
    }

    public function setRateSettings($rateSettings)
    {
        if (array_key_exists('attraction_amount', $rateSettings)) {
            $this->_attractionAmount = $rateSettings['attraction_amount'];
            unset($rateSettings['attraction_amount']);
        }
        if (array_key_exists('customer_group', $rateSettings)) {
            $this->_customerGroup = $rateSettings['customer_group'];
            unset($rateSettings['customer_group']);
        }
        $this->setData('rate_settings', $rateSettings);
        return true;
    }

}
