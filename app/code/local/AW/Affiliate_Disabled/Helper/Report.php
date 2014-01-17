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


class AW_Affiliate_Helper_Report extends Mage_Core_Helper_Abstract
{

    public function getPeriodRange($periodType, $customDate = null)
    {
        $dateEnd = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);
        switch ($periodType) {
            case AW_Affiliate_Model_Source_Report_Period::TODAY :
                //already setted
                break;
            case AW_Affiliate_Model_Source_Report_Period::YESTERDAY :
                $dateStart->subDay(1);
                $dateEnd->subDay(1);
                break;
            case AW_Affiliate_Model_Source_Report_Period::LAST_SEVEN_DAYS :
                $dateStart->subDay(6);
                break;
            case AW_Affiliate_Model_Source_Report_Period::THIS_MONTH :
                $dateStart->setDay(1);
                break;
            case AW_Affiliate_Model_Source_Report_Period::ALL_TIME:
                return NULL;
                break;
            case AW_Affiliate_Model_Source_Report_Period::CUSTOM_PERIOD :
                if (is_null($customDate)) {
                    //then set as this_day
                    break;
                }
                try {
                    $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                    $dateStart->setDate($customDate['from'], $format);
                    $dateEnd->setDate($customDate['to'], $format);
                }
                catch (Exception $e) {
                    //then set as this_day
                }
                break;

            case AW_Affiliate_Model_Source_Report_Period::CUSTOM_PERIOD_DEFAULT :

                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $dateStart->subDay(1);
                $range = array(
                    'from' => $dateStart->toString($format),
                    'to' => $dateEnd->toString($format),
                );
                return $range;
                break;

            default:
                //then set as this_day
        }
        $range = array(
            'from' => $dateStart->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
            'to' => $dateEnd->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
        );
        return $range;
    }

    public function getFormatByDetalization($detalization)
    {
        switch ($detalization) {
            case 'day':
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                break;
            case 'month': /*
                $replace = array('dd\\' => '', '\dd' => '', 'dd,' => '', ',dd' => '',
                    'dd/' => '', '/dd' => '', 'dd.' => '', '.dd' => '', 'dd-' => '',
                    '-dd' => '', 'dd ' => '', ' dd' => '');
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $format = str_replace(array_keys($replace), array_values($replace), $format);
                */
                $format = Zend_Date::YEAR . ',' . Zend_Date::MONTH_SHORT;
                break;
            case 'year':
                $format = Zend_Date::YEAR;
                break;
            default:
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        }
        return $format;
    }
}
