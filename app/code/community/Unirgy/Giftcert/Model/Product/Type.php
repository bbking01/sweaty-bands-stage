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

class Unirgy_GiftCert_Model_Product_Type extends Mage_Catalog_Model_Product_Type_Abstract
{
    /**
     * Generate product options for cart item
     *
     * @param Varien_Object $buyRequest
     * @param null          $product
     *
     * @return array|string
     */
    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

//        $result = parent::prepareForCart($buyRequest, $product);

        if ($multiple = $buyRequest->getData('multiple-recipients')) {
            $result = array();
            $multiple = explode(',', $multiple);
            foreach ($multiple as $i) {
                $temp = $this->_addCertificate($buyRequest, $product, $i);
                if (is_string($temp)) {
                    return $temp;
                }
                $result = array_merge($result, $temp);
            }
            return $result;
        } else {
            $result = parent::prepareForCart($buyRequest, $product, $processMode);
        }

        if (is_string($result)) {
            return $result;
        }

        $hlp = Mage::helper('ugiftcert');
        $store = Mage::app()->getStore();

        $amountConfig = $hlp->getAmountConfig($product);

        if ($store->isAdmin()) {
            $amount = $product->getPrice();
        // Attempt to add to cart from product list
        // Need to update info_buyRequest[amount] somehow
        //if ($amountConfig['type']=='fixed') {
        //    $amount = $amountConfig['amount'];
        } else {
            $amount = $buyRequest->getAmount();
            if (!$amount) {
                return Mage::helper('ugiftcert')->__('Please enter gift certificate information');
            }
        }

        // maintain same price for not base currency
        $amount /= $store->getCurrentCurrencyRate();

        if (!$buyRequest->getAmount()) {
            $buyRequest->setAmount($amount);
        }

        $product->addCustomOption('amount', $amount);

        $fields = array();
        foreach ($hlp->getGiftcertOptionVars() as $k=>$l) {
            $fields[$k] = $k;
        }
        $fields['message'] = 'recipient_message'; // legacy templates (before 0.7.5)

        foreach ($fields as $p=>$k) {
            if ($v = $buyRequest->getData($p)) {
                $product->addCustomOption($k, $v);
            }
        }

        return $result;
    }

    /**
     * Check whether quote item is virtual
     *
     * @param null $product
     *
     * @return boolean
     */
    public function isVirtual($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        if (Mage::getStoreConfig('ugiftcert/address/always_virtual', $product->getStoreId())) {
            return true;
        }

        $item = Mage::getSingleton('checkout/session')->getQuote()->getItemByProduct($product);
        if (!$item) {
            return false;
        }

        $options = array();
        foreach ($item->getOptions() as $option) {
            $options[$option->getCode()] = $option->getValue();
        }
        if ((!empty($options['recipient_email']) && empty($options['recipient_address']))
            || (empty($options['recipient_name']) && empty($options['toself_printed']))
            ) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getEditableAttributes($product = null)
    {
        $editableAttributes = parent::getEditableAttributes($product);
        if(isset($editableAttributes['price'])) {
            unset($editableAttributes['price']);
        }
        return $editableAttributes;
    }

    protected function _getFields()
    {
        $hlp    = Mage::helper('ugiftcert');
        $fields = array();
        foreach ($hlp->getGiftcertOptionVars() as $k=> $l) {
            $fields[$k] = $k;
        }
        $fields['message'] = 'recipient_message';// legacy templates (before 0.7.5)
        return $fields;
    }

    /**
     * @param Varien_Object                 $buyRequest
     * @param Mage_Catalog_Model_Product    $product
     * @param int                           $i - counter
     *
     * @return array|string
     */
    private function _addCertificate(Varien_Object $buyRequest, $product, $i)
    {
        $fields = $this->_getFields();
        $clone  = clone $product;
        $cloneBuyRequest = clone $buyRequest;
        $cloneBuyRequest->unsetData('multiple-recipients');
        $cloneBuyRequest->setData('qty', 1);
        foreach ($fields as $k => $v) {
            $key = $k . '-' . $i;
            $val = $buyRequest->getDataUsingMethod($key);
            $cloneBuyRequest->setData($k, $val);
        }
        return $this->prepareForCart($cloneBuyRequest, $clone);
    }
}
