<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-7-6
 * Time: 19:55
 */

class Unirgy_Giftcert_Block_Payment_Methods
    extends Mage_Checkout_Block_Onepage_Payment_Methods
{
    public function getMethods()
    {
        $version = Mage::getVersion();
        if(version_compare($version, '1.6.0', '>=')) {
            $totalMethod = 'getGrandTotal';
        } elseif (version_compare($version, '1.4.0','>=') && version_compare($version, '1.5.0', '<')){
            $totalMethod = 'getBaseGrandTotal';
        } else {
            return parent::getMethods();
        }
        return $this->_getMethods($totalMethod);
    }

    protected function _getMethods($totalMethod = 'getGrandTotal')
    {
        $methods = $this->getData('methods');
        if (is_null($methods)) {
            $quote = $this->getQuote();
            $store = $quote ? $quote->getStoreId() : null;
            $methods = $this->helper('payment')->getStoreMethods($store, $quote);
            $total = $quote->$totalMethod();
            foreach ($methods as $key => $method) {
                if ($this->_canUseMethod($method)
                    && ($total != 0
                        || in_array($method->getCode(), array('free', 'ugiftcert'))
                        || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))
                ) {
                    $this->_assignMethod($method);
                } else {
                    unset($methods[$key]);
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }
}
