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


class AW_Affiliate_Model_Source_Report_Period extends AW_Affiliate_Model_Source_Abstract
{
    const TODAY = 'today';
    const YESTERDAY = 'yesterday';
    const LAST_SEVEN_DAYS = 'last_seven_days';
    const THIS_MONTH = 'this_month';
    const ALL_TIME = 'all_time';
    const CUSTOM_PERIOD = 'custom_period';
    const CUSTOM_PERIOD_DEFAULT = 'custom_period_default';

    const TODAY_LABEL = 'Today';
    const YESTERDAY_LABEL = 'Yesterday';
    const LAST_SEVEN_DAYS_LABEL = 'Last 7 days';
    const THIS_MONTH_LABEL = 'This month';
    const ALL_TIME_LABEL = 'All time';
    const CUSTOM_PERIOD_LABEL = 'Custom period';

    public function toOptionArray()
    {
        $helper = $this->_getHelper();
        return array(
            array('value' => self::TODAY, 'label' => $helper->__(self::TODAY_LABEL)),
            array('value' => self::YESTERDAY, 'label' => $helper->__(self::YESTERDAY_LABEL)),
            array('value' => self::LAST_SEVEN_DAYS, 'label' => $helper->__(self::LAST_SEVEN_DAYS_LABEL)),
            array('value' => self::THIS_MONTH, 'label' => $helper->__(self::THIS_MONTH_LABEL)),
            array('value' => self::ALL_TIME, 'label' => $helper->__(self::ALL_TIME_LABEL)),
            array('value' => self::CUSTOM_PERIOD, 'label' => $helper->__(self::CUSTOM_PERIOD_LABEL)),
        );
    }
}
