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


class AW_Affiliate_Helper_Data extends Mage_Core_Helper_Abstract
{
    const WITHDRAWAL_GRID_CONTAINER_ID = 'withdrawal-grid-container';
    const SESSION_BACKURL_KEY = '_aw_back_url';
    const USE_AW_BACKURL_FLAG = '_use_aw_backurl';
    const NEW_PROTOTYPE_REQUIRED = '_awaff_new_prototype_required';

    public static function isEnabled()
    {
        return !((bool)Mage::getStoreConfig('advanced/modules_disable_output/AW_Affiliate'));
    }

    public function formatCurrency($value)
    {
        return Mage::helper('core')->currency($value);
    }

    public function formatDate($date = null, $format = Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, $showTime = true)
    {
        if (is_null($date)) {
            return null;
        }
        return Mage::helper('core')->formatDate($date, $format, $showTime);
    }

    /*check magento version*/
    public function isMageVersionGreathOrEqual($version)
    {
        return version_compare(Mage::getVersion(), $version, '>=');
    }

    public function isExtensionInstalled($name)
    {
        $modules = (array)Mage::getConfig()->getNode('modules')->children();
        return array_key_exists($name, $modules)
            && 'true' == (string)$modules[$name]->active
            && !(bool)Mage::getStoreConfig('advanced/modules_disable_output/' . $name);
    }

    public function checkExtensionVersion($extensionName, $extVersion, $operator = '>=')
    {
        if ($this->isExtensionInstalled($extensionName) && ($version = Mage::getConfig()->getModuleConfig($extensionName)->version)) {
            return version_compare($version, $extVersion, $operator);
        }
        return false;
    }

    /*$data = AW_Affiliate_Model_Profit->getData()
        or AW_Affiliate_Model_Profit->getRateSettings()*/
    public function getRateSettingsByProfitData($data)
    {
        if (array_key_exists('rate_settings', $data)) {
            $data = $data['rate_settings'];
        }
        $rateSettings = unserialize($data);
        return $rateSettings;
    }

    public function campaignEditPreSaveValidateData($data)
    {
        if (array_key_exists('rate_settings', $data)) {
            $data = $data + $data['rate_settings'];
            unset($data['rate_settings']);
        }

        $keys = array_keys($data);
        $requiredKeys = array(
            'name',
            'store_ids',
            'active_from',
            'active_to',
            'status',
            'allowed_groups',
            'rate_type',
            'rate_calculation_type',
        );
        $result = array_diff($requiredKeys, $keys);
        if (count($result)) {
            return false;
        }
        return true;
    }

    public function affiliateEditPreSaveValidateData($data)
    {
        $keys = array_keys($data);
        $requiredKeys = array(
            'status'
        );
        $result = array_diff($requiredKeys, $keys);
        if (count($result)) {
            return false;
        }
        return true;
    }

    public function getDefaultCurrencyCode()
    {
        return Mage::app()->getStore()->getBaseCurrencyCode();
    }

    public function getDefaultCurrencySymbol()
    {
        $__currencyCode = $this->getDefaultCurrencyCode();
        $symbol = Mage::app()->getLocale()->currency($__currencyCode)->getSymbol();
        return $symbol;
    }

    public function getAffiliateCookie()
    {
        $cookie = Mage::getSingleton('core/cookie')->get(AW_Affiliate_Helper_Config::COOKIE_NAME);
        return $cookie;
    }

    protected function _getAdminhtmlSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    public function setBackUrl($url)
    {
        $this->_getAdminhtmlSession()->setData(self::SESSION_BACKURL_KEY, $url);
    }

    public function getBackUrl()
    {
        return $this->_getAdminhtmlSession()->getData(self::SESSION_BACKURL_KEY);
    }

    public function getWithdrawalContainerId()
    {
        return self::WITHDRAWAL_GRID_CONTAINER_ID;
    }

    /**
     * Remove request parameter from url
     *
     * @param string $url
     * @param string $paramKey
     * @return string
     */
    public function removeRequestParam($url, $paramKey, $caseSensitive = false)
    {
        $regExpression = '/\\?[^#]*?(' . preg_quote($paramKey, '/') . '\\=[^#&]*&?)/' . ($caseSensitive ? '' : 'i');
        while (preg_match($regExpression, $url, $mathes) != 0) {
            $paramString = $mathes[1];
            if (preg_match('/&$/', $paramString) == 0) {
                $url = preg_replace('/(&|\\?)?' . preg_quote($paramString, '/') . '/', '', $url);
            } else {
                $url = str_replace($paramString, '', $url);
            }
        }
        return $url;
    }

    public function updatePrototypeJS()
    {
        if ($this->checkExtensionVersion('Mage_Core', '1.6.0.2', '<=')) {
            Mage::register(self::NEW_PROTOTYPE_REQUIRED, true);
        }
    }

    public function isNewPrototypeRequired()
    {
        return Mage::registry(self::NEW_PROTOTYPE_REQUIRED);
    }
}
