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


class AW_Affiliate_Model_Profit extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('awaffiliate/profit');
    }

    public function loadByCampaignId($id)
    {
        $this->load($id, 'campaign_id');
        return $this;
    }

    public function getAmountForAffiliate(AW_Affiliate_Model_Affiliate $affiliate, $baseAmount)
    {
        $rate = $this->getRateForAffiliate($affiliate);

        switch ($this->getType()) {
            case AW_Affiliate_Model_Source_Profit_Type::FIXED :
            case AW_Affiliate_Model_Source_Profit_Type::TIER :
                $calculatedAmount = $baseAmount * ($rate / 100);
                break;
            case AW_Affiliate_Model_Source_Profit_Type::FIXED_CUR :
            case AW_Affiliate_Model_Source_Profit_Type::TIER_CUR :
                $calculatedAmount = $rate;
                break;
        }
        $calculatedAmount = round($calculatedAmount, 2);

        return $calculatedAmount;
    }

    public function getRateForAffiliate(AW_Affiliate_Model_Affiliate $affiliate)
    {
        $_calculateInstance = $this->getCalculateInstance();
        $_campaignId = $this->getData('campaign_id');
        if (is_null($_calculateInstance) || is_null($_campaignId)) {
            return null;
        }
        $rateCalculationType = $this->getData('rate_settings/rate_calculation_type');
        switch ($rateCalculationType) {
            case AW_Affiliate_Model_Source_Profit_Calculation_Type::ALL_TIME:
                $attractionAmount = Mage::helper('awaffiliate/affiliate')->getTotalAmountForAffiliate($affiliate);
                break;
            case AW_Affiliate_Model_Source_Profit_Calculation_Type::LAST_MONTH:
                $attractionAmount = Mage::helper('awaffiliate/affiliate')->getLastMonthAmountForAffiliate($affiliate);
                break;
            default:
                return null;
        }
        $rateSettings = array(
            'attraction_amount' => $attractionAmount,
            'customer_group' => $affiliate->getCustomerGroupId(),
        );

        $_calculateInstance->setRateSettings($rateSettings);
        return $_calculateInstance->getRate();
    }

    protected function getCalculateInstance()
    {
        switch ($this->getType()) {
            case AW_Affiliate_Model_Source_Profit_Type::FIXED :
                $model = Mage::getModel('awaffiliate/profit_fixed');
                break;
            case AW_Affiliate_Model_Source_Profit_Type::TIER :
                $model = Mage::getModel('awaffiliate/profit_tier');
                $model->loadRatesByProfitId($this->getId());
                break;
            case AW_Affiliate_Model_Source_Profit_Type::FIXED_CUR :
                $model = Mage::getModel('awaffiliate/profit_fixedcur');
                break;
            case AW_Affiliate_Model_Source_Profit_Type::TIER_CUR :
                $model = Mage::getModel('awaffiliate/profit_tier');
                $model->loadRatesByProfitId($this->getId());
                break;
            default:
                return null;
        }
        $model->setRateSettings($this->getData('rate_settings'));
        return $model;
    }


    protected function _beforeSave()
    {
        $rateSettings = array(
            'rate_calculation_type' => $this->getRateCalculationType(),
            'profit_rate' => $this->getProfitRate(),
            'profit_rate_cur' => $this->getProfitRateCur()
        );
        $this->setRateSettings($rateSettings);
        return parent::_beforeSave();
    }

}
