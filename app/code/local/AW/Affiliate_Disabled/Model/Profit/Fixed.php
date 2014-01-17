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


class AW_Affiliate_Model_Profit_Fixed extends Varien_Object implements AW_Affiliate_Model_Profit_Interface
{
    protected $_rate = null;

    protected function _construct()
    {
        parent::_construct();
    }

    public function getRate()
    {
        return $this->_rate;
    }

    public function getProfitAmount($attractionAmount)
    {
        $amount = $this->_rate * $attractionAmount;
        return $amount;
    }

    public function setRateSettings($rateSettings)
    {
        if (array_key_exists('profit_rate', $rateSettings)) {
            $this->_rate = $rateSettings['profit_rate'];
            unset($rateSettings['profit_rate']);
        }
        $this->setData('rate_settings', $rateSettings);
        return true;
    }

}
