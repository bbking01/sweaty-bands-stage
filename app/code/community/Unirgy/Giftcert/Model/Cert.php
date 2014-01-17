<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Model_Cert extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ugiftcert/cert');
        parent::_construct();
    }

    public function getStatus()
    {
        if ($this->getData('status')=='A' && $this->getData('expire_at')) {
            if (strtotime($this->getData('expire_at'))<time()) {
                $this->setData('status', 'I');
                if ($this->getId()) {
                    $this->save();
                }
            }
        }
        return $this->getData('status');
    }

    public function getCurrencyRate()
    {
        if (!$this->hasData('currency_rate')) {
            $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
            // when getting rate using code, there is chance of fatal error in case of non defined currency rate, so we use currency object
            $rate = Mage::helper('directory')->currencyConvert(1, $baseCurrencyCode, Mage::getModel('directory/currency')->load($this->getCurrencyCode()));
            $this->setData('currency_rate', $rate);
        }
        return $this->getData('currency_rate');
    }

    public function getBaseBalance()
    {
        return $this->getBalance()/$this->getCurrencyRate();
    }

    public function addHistory($data)
    {
        Mage::getModel('ugiftcert/history')->setCertId($this->getCertId())->addData($data)->save();
        return $this;
    }

    public function getHistory($actionCode=null)
    {
        $collection = Mage::getModel('ugiftcert/history')->getCollection()
            ->addCertFilter($this->getId());
        if (!is_null($actionCode)) {
            $collection->addActionFilter($actionCode);
        }
        return $collection;
    }

    public function addToQuote($quote=null)
    {
        if (is_null($quote)) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        $codes = $quote->getGiftcertCode();
        if ($codes) {
            $c = array_map('trim', explode(',', $codes));
            $c[] = $this->getCertNumber();
            $codes = join(', ', array_unique($c));
        } else {
            $codes = $this->getCertNumber();
        }
        $quote->setGiftcertCode($codes);
        return $this;
    }

    public function removeFromQuote($quote = null, $code = null)
    {
        if (is_null($quote)) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        if (is_null($code)) {
            $code = $this->getCertNumber();
        }

        $codes = $quote->getGiftcertCode();

        if(!$code || !$codes || strpos($codes, $code) === false) {
            return;
        }

        $gcCodes = array();
        foreach (explode(',', $codes) as $gc1) {
            if (trim($gc1) !== $code) {
                $gcCodes[] = $gc1;
            }
        }
        $quote->setGiftcertCode(implode(',', $gcCodes))->save();
    }

    public function _afterLoad()
    {
        parent::_afterLoad();
        $config = Mage::getStoreConfig('ugiftcert/default');
        if (!$this->getData('cert_number') && $config['auto_cert_number']) {
            $this->setData('cert_number', $config['cert_number']);
        }
        if ($config['use_pin'] && $config['auto_pin'] && !$this->getData('pin')) {
            $this->setData('pin', $config['pin']);
        }
        if(Mage::getStoreConfig('ugiftcert/default/use_conditions')){
            $conditionsArr = unserialize($this->getConditionsSerialized());
            if (!empty($conditionsArr) && is_array($conditionsArr)) {
                $this->getConditions()->setConditions(array())->loadArray($conditionsArr);
            }
        }
    }

    public function getPdfSettings()
    {
        $pdfSettings = $this->getData('pdf_template_id');
        if (empty($pdfSettings)) {
            return array();
        }
        $settings = Mage::getModel('ugiftcert/pdf_model')->load($pdfSettings);

        if (!$settings->getId()) {
            throw new Exception("Could not get PDF settings.");
        }

        $pdfSettings = $settings->getData('settings');
        if(!is_array($pdfSettings)){
            $pdfSettings = Zend_Json::decode($pdfSettings);
        }

        return $pdfSettings;
    }

    public function setPdfSettings($pdfSettings)
    {
        if (is_array($pdfSettings)) {
            Mage::throwException(Mage::helper("ugiftcert")->__("Direct setting of PDF settings is not supported."));
        } elseif(is_numeric($pdfSettings)) {
            $this->setData('pdf_template_id', $pdfSettings);
        }
        return $this;
    }

    public function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->getIsMassAction()) {
            return $this;
        }
        $hlp = Mage::helper('ugiftcert');

        if ($hlp->isPattern($this->getData('cert_number'))) {
            $pattern = $this->getData('cert_number');
            $dup = Mage::getModel('ugiftcert/cert');
            $i = 0;
            while ($i++<10) { // 10 times can't find free slot - a problem
                $num = $hlp->processRandomPattern($pattern);
                $dup->unsetData()->load($num, 'cert_number');
                if (!$dup->getCertId()) {
                    break;
                }
                $num = false;
            }
            if ($num===false) {
                throw new Mage_Core_Exception('Exceeded maximum retries to find available random certificate number');
            }
            $this->setData('cert_number', $num);
        }

        if (Mage::getStoreConfig('ugiftcert/default/use_pin') && $hlp->isPattern($this->getData('pin'))) {
            $this->setData('pin', $hlp->processRandomPattern($this->getData('pin')));
        }

        if ($date = $this->getExpireAt()) {
            $this->setExpireAt($this->prepareDate($date));
        } else {
            $this->setExpireAt(null);
        }

        if ($date = $this->getData('send_on')) {
            $this->setData('send_on', $this->prepareDate($date, Varien_Date::DATE_INTERNAL_FORMAT));
        } else {
            $this->setData('send_on', null);
        }

        if (Mage::getStoreConfig('ugiftcert/default/use_conditions') && $this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->unsetData('_conditions');
        }
        return $this;
    }

    /***********************
     *  conditions
     *************************/

    public function getConditionsInstance()
    {
        return Mage::getModel('salesrule/rule_condition_combine');
    }

    public function _resetConditions($conditions=null)
    {
        if (is_null($conditions)) {
            $conditions = $this->getConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('conditions');
        $this->setConditions($conditions);

        return $this;
    }

    public function setConditions($conditions)
    {
        $this->setData('_conditions', $conditions);
        return $this;
    }

    /**
     * Retrieve Condition model
     *
     * @return Mage_SalesRule_Model_Rule_Condition_Combine
     */
    public function getConditions()
    {
        if (!$this->getData('_conditions')) {
            $this->_resetConditions();
        }
        return $this->getData('_conditions');
    }

    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
        }
        return $this;
    }

    protected function _convertFlatToRecursive($rule)
    {
        $arr = array();
        foreach ($rule as $key=>$value) {
            if (($key==='conditions') && is_array($value)) {
                foreach ($value as $id=>$data) {
                    $path = explode('--', $id);
                    $node =& $arr;
                    for ($i=0, $l=sizeof($path); $i<$l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = array();
                        }
                        $node =& $node[$key][$path[$i]];
                    }
                    foreach ($data as $k=>$v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                if (in_array($key, array('from_date', 'to_date')) && $value) {
                    $value = Mage::app()->getLocale()->date($value, Varien_Date::DATE_INTERNAL_FORMAT, null, false);
                }
                $this->setData($key, $value);
            }
        }
        return $arr;
    }

    public function validate(Varien_Object $object)
    {
        return $this->getConditions()->validate($object);
    }

    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = new Varien_Data_Form();
        }
        return $this->_form;
    }

    public function prepareDate($date, $formatOut = Varien_Date::DATETIME_INTERNAL_FORMAT,
                                $formatIn = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
    {
        if(empty($date)){
            return null;
        }
        $locale     = Mage::app()->getLocale();
        $isDate = Zend_Date::isDate($date, $formatOut, $locale->getLocale());
        if($isDate){
            return $date;
        }
        try {
            $format     = $locale->getDateFormat($formatIn);
            $dateObject = $locale->date($date, $format, null, false);
            return $dateObject->toString($formatOut);
        } catch (Zend_Date_Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
            Mage::log($e->getTraceAsString(), Zend_Log::ERR, Unirgy_Giftcert_Helper_Data::LOG_NAME, true);
//            throw new Exception(Mage::helper('ugiftcert')->__("Date cannot be parsed"));
        }
        return null;
    }

    public function setCustomerGroups($groups)
    {
        if(is_array($groups)){
            $groups = join(',', $groups);
        }
        $this->setData('customer_groups', $groups);
        return $this;
    }
}
