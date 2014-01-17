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
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_FBIntegrator
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_FBIntegrator_Block_Fb extends Mage_Core_Block_Template
{
    protected $_defaultCode = 'en_US'; //default js code for facebook api
    protected $_facebookLocaleCode = array(
        'af_ZA', 'az_AZ', 'id_ID', 'ms_MY', 'bs_BA', 'ca_ES', 'cs_CZ',
        'cy_GB', 'da_DK', 'de_DE', 'et_EE', 'en_PI', 'en_GB', 'en_UD',
        'en_US', 'es_LA', 'es_ES', 'eo_EO', 'eu_ES', 'tl_PH', 'fo_FO',
        'fr_CA', 'fr_FR', 'fy_NL', 'ga_IE', 'gl_ES', 'ko_KR', 'hr_HR',
        'is_IS', 'it_IT', 'ka_GE', 'sw_KE', 'ku_TR', 'lv_LV', 'fb_LT',
        'lt_LT', 'la_VA', 'hu_HU', 'nl_NL', 'ja_JP', 'nb_NO', 'nn_NO',
        'pl_PL', 'pt_BR', 'pt_PT', 'ro_RO', 'ru_RU', 'sq_AL', 'sk_SK',
        'sl_SI', 'fi_FI', 'sv_SE', 'th_TH', 'vi_VN', 'tr_TR', 'zh_CN',
        'zh_TW', 'zh_HK', 'el_GR', 'bg_BG', 'mk_MK', 'sr_RS', 'uk_UA',
        'hy_AM', 'he_IL', 'ar_AR', 'ps_AF', 'fa_IR', 'ne_NP', 'hi_IN',
        'bn_IN', 'pa_IN', 'ta_IN', 'te_IN', 'ml_IN',
    );


    public function _toHtml()
    {
        if (!Mage::helper('fbintegrator')->extEnabled()) {
            return false;
        }
        return parent::_toHtml();
    }

    public function getLocaleFile()
    {
        return Mage::app()->getRequest()->getScheme() . '://connect.facebook.net/' . $this->getLocaleCode() . '/all.js';
    }

    public function getLocaleCode()
    {
        $localCode = Mage::app()->getLocale()->getLocaleCode();
        $localCode = in_array($localCode, $this->_facebookLocaleCode) ? $localCode : $this->_defaultCode;
        return $localCode;
    }
}
