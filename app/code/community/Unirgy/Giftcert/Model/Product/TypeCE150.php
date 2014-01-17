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

class Unirgy_GiftCert_Model_Product_TypeCE150 extends Mage_Catalog_Model_Product_Type_Abstract
{
    const DELIVERY_TYPE = 'delivery_type';
    const DELIVERY_TYPE_PHYSICAL = 'physical';
    const DELIVERY_TYPE_VIRTUAL = 'virtual';
    /**
     * Generate product options for cart item
     *
     * @param Varien_Object               $buyRequest
     * @param Mage_Catalog_Model_Product  $product
     * @param string                      $processMode
     *
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        if ($multiple = $buyRequest->getData('multiple-recipients')) {
            $result   = array();
            $multiple = explode(',', $multiple);
            foreach ($multiple as $i) {
                $temp = $this->_addCertificate($buyRequest, $product, $i, $processMode);
                if (is_string($temp)) {
                    return $temp;
                }
                $result = array_merge($result, $temp);
            }
            return $result;
        } else {
            $result = parent::_prepareProduct($buyRequest, $product, $processMode);
        }

        if (is_string($result)) {
            return $result;
        }

        $store = Mage::app()->getStore();

//        $amountConfig = $hlp->getAmountConfig($product);

        if ($store->isAdmin()) {
            $amount = $product->getPrice();
            // Attempt to add to cart from product list
            // Need to update info_buyRequest[amount] somehow
            //if ($amountConfig['type']=='fixed') {
            //    $amount = $amountConfig['amount'];
        } else {
            $amount = Mage::app()->getLocale()->getNumber($buyRequest->getAmount());

            if (!$amount) {
                return Mage::helper('ugiftcert')->__('Please enter gift certificate information');
            } elseif (!Mage::helper('ugiftcert')->validateAmount($product, $amount)) {
                return Mage::helper('ugiftcert')->__('Supplied amount is invalid.');
            }
        }

        // maintain same price for not base currency
        $amount /= $store->getCurrentCurrencyRate();

        if (!$buyRequest->getAmount()) {
            $buyRequest->setAmount($amount);
        }

        $product->addCustomOption('amount', $amount);

        $fields = $this->_getFields();
        $options = array();
        foreach ($fields as $p=> $k) {
            if ($v = $buyRequest->getData($p)) {
                switch ($p) {
                    case 'recipient_email':
                        $valid = filter_var($v, FILTER_VALIDATE_EMAIL);
                        if (!$valid) {
                            return Mage::helper('ugiftcert')->__('Supplied recipient email is invalid.');
                        }
                        break;
                    case 'recipient_address':
                    case 'recipient_name':
                        if (!trim($v)) {
                            return Mage::helper('ugiftcert')->__('Supplied recipient data is invalid.');
                        }
                        break;
                    case 'sender_email':
                        if(trim($v)){
                            $valid = filter_var($v, FILTER_VALIDATE_EMAIL);
                            if (!$valid) {
                                return Mage::helper('ugiftcert')->__('Supplied sender email is invalid.');
                            }
                        }
                        break;
                }
                $options[$k] = $v;
            }
        }

        if(!isset($options[self::DELIVERY_TYPE])){
            return Mage::helper('ugiftcert')->__('Missing delivery type.');
        } else if(!in_array($options[self::DELIVERY_TYPE], array(self::DELIVERY_TYPE_PHYSICAL, self::DELIVERY_TYPE_VIRTUAL))){
            return Mage::helper('ugiftcert')->__('Unknown delivery type.');
        } else if($options[self::DELIVERY_TYPE] == self::DELIVERY_TYPE_PHYSICAL && !isset($options['recipient_address'])){
            return Mage::helper('ugiftcert')->__('Recipient address is required.');
        } else if($options[self::DELIVERY_TYPE] == self::DELIVERY_TYPE_VIRTUAL && !isset($options['recipient_email'])){
            return Mage::helper('ugiftcert')->__('Recipient email is required.');
        }

        foreach ($options as $k => $v) {
            $product->addCustomOption($k, $v);
        }

        return $result;
    }

    protected function _getFields()
    {
        $hlp    = Mage::helper('ugiftcert');
        $fields = array();
        foreach ($hlp->getGiftcertOptionVars() as $k=> $l) {
            $fields[$k] = $k;
        }
        $fields['message'] = 'recipient_message'; // legacy templates (before 0.7.5)
        return $fields;
    }

    /**
     * Check whether quote item is virtual
     *
     * @param null|Mage_Catalog_Model_Product $product
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

        $item = $this->_getProductItem($product);

        if (!$item) {
            return false;
        }

        $options = array();
        foreach ($item->getOptions() as $option) {
            $options[$option->getCode()] = $option->getValue();
        }
        if(isset($options[self::DELIVERY_TYPE])){
            return $options[self::DELIVERY_TYPE] == self::DELIVERY_TYPE_VIRTUAL;
        }

        if ((!empty($options['recipient_email']) && empty($options['recipient_address']))
            || (empty($options['recipient_name']) && empty($options['toself_printed']))
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get product's quote item,
     * if product has been added to cart, then it has some configuration and
     * it can be determined if it is virtual or not. If we get quote from session
     * we risk getting into a loop, that is why we only check for quote id, if we
     * have one, load a quote object with it, but do not collect totals, since this
     * starts endless loop.
     * If no quote id, no loop will be started, so no extra logic is needed.
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return boolean|Mage_Sales_Model_Quote_Item
     */
    protected function _getProductItem($product)
    {
        $item = false;

        $quoteId = Mage::getModel('checkout/session')->getQuoteId();
        if ($quoteId) {
            /*  @var $quote Mage_Sales_Model_Quote*/
            $quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId());
            $quote->getResource()->loadActive($quote, $quoteId); //Ben added this line
            // there is a quote started already, do not load

            $items = Mage::getModel('sales/quote_item')->getCollection()->setQuote($quote);
            foreach ($items as $i) {
                /* @var $i Mage_Sales_Model_Quote_Item */
                if ($i->representProduct($product)) {
                    $item = $i;
                    break;
                }
            }
        } else {
            $item = Mage::getModel('checkout/session')->getQuote()->getItemByProduct($product);
        }
        return $item;
    }

    public function getEditableAttributes($product = null)
    {
        $editableAttributes = parent::getEditableAttributes($product);
        if (isset($editableAttributes['price'])) {
            unset($editableAttributes['price']);
        }
        return $editableAttributes;
    }

    /**
     * @param Varien_Object                 $buyRequest
     * @param Mage_Catalog_Model_Product    $product
     * @param int                           $i - counter
     * @param string                        $processMode
     *
     * @return array|string
     */
    private function _addCertificate(Varien_Object $buyRequest, $product, $i, $processMode)
    {
        $fields          = $this->_getFields();
        $clone           = clone $product;
        $cloneBuyRequest = clone $buyRequest;
        $cloneBuyRequest->unsetData('multiple-recipients');
        $cloneBuyRequest->setData('qty', 1);
        foreach ($fields as $k => $v) {
            $key = $k . '-' . $i;
            $val = $buyRequest->getDataUsingMethod($key);
            $cloneBuyRequest->setData($k, $val);
        }
        return $this->_prepareProduct($cloneBuyRequest, $clone, $processMode);
    }
}
