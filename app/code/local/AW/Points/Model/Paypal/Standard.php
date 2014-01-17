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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/* Magento 1.3 stab */
class AW_Points_Model_Paypal_Standard extends Mage_Paypal_Model_Standard {

    public function getStandardCheckoutFormFields() {
        if ($this->getQuote()->getIsVirtual()) {
            $a = $this->getQuote()->getBillingAddress();
            $b = $this->getQuote()->getShippingAddress();
        } else {
            $a = $this->getQuote()->getShippingAddress();
            $b = $this->getQuote()->getBillingAddress();
        }
        //getQuoteCurrencyCode
        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        /*
          //we validate currency before sending paypal so following code is obsolete

          if (!in_array($currency_code,$this->_allowCurrencyCode)) {
          //if currency code is not allowed currency code, use USD as default
          $storeCurrency = Mage::getSingleton('directory/currency')
          ->load($this->getQuote()->getStoreCurrencyCode());
          $amount = $storeCurrency->convert($amount, 'USD');
          $currency_code='USD';
          }
         */

        $sArr = array(
            'charset' => self::DATA_CHARSET,
            'business' => Mage::getStoreConfig('paypal/wps/business_account'),
            'return' => Mage::getUrl('paypal/standard/success', array('_secure' => true)),
            'cancel_return' => Mage::getUrl('paypal/standard/cancel', array('_secure' => false)),
            'notify_url' => Mage::getUrl('paypal/standard/ipn'),
            'invoice' => $this->getCheckout()->getLastRealOrderId(),
            'currency_code' => $currency_code,
            'address_override' => 1,
            'first_name' => $a->getFirstname(),
            'last_name' => $a->getLastname(),
            'address1' => $a->getStreet(1),
            'address2' => $a->getStreet(2),
            'city' => $a->getCity(),
            'state' => $a->getRegionCode(),
            'country' => $a->getCountry(),
            'zip' => $a->getPostcode(),
            'bn' => 'Varien_Cart_WPS_US'
        );

        $logoUrl = Mage::getStoreConfig('paypal/wps/logo_url');
        if ($logoUrl) {
            $sArr = array_merge($sArr, array(
                'cpp_header_image' => $logoUrl
                    ));
        }

        if ($this->getConfigData('payment_action') == self::PAYMENT_TYPE_AUTH) {
            $sArr = array_merge($sArr, array(
                'paymentaction' => 'authorization'
                    ));
        }

        $transaciton_type = $this->getConfigData('transaction_type');
        /*
          O=aggregate cart amount to paypal
          I=individual items to paypal
         */
        if ($transaciton_type == 'O') {
            $businessName = Mage::getStoreConfig('paypal/wps/business_name');
            $storeName = Mage::getStoreConfig('store/system/name');
            $amount = ($a->getBaseSubtotal() + $b->getBaseSubtotal()) - ($a->getBaseDiscountAmount() + $b->getBaseDiscountAmount());

            $session = Mage::getSingleton('checkout/session');
            if ($session->getData('use_points')) {
                if ($a->getBaseGrandTotal() || $b->getBaseGrandTotal()) {
                    $pointsAmountUsed = $session->getData('points_amount');
                    $rate = Mage::getModel('points/rate')->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY);
                    $moneyBaseCurrencyForPoints = $rate->exchange($pointsAmountUsed);
                    $amount -= $moneyBaseCurrencyForPoints;
                    if ($amount <= 0)
                        $amount = 0;
                }
            }

            $sArr = array_merge($sArr, array(
                'cmd' => '_ext-enter',
                'redirect_cmd' => '_xclick',
                'item_name' => $businessName ? $businessName : $storeName,
                'amount' => sprintf('%.2f', $amount),
                    ));
            $_shippingTax = $this->getQuote()->getShippingAddress()->getBaseTaxAmount();
            $_billingTax = $this->getQuote()->getBillingAddress()->getBaseTaxAmount();
            $tax = sprintf('%.2f', $_shippingTax + $_billingTax);
            if ($tax > 0) {
                $sArr = array_merge($sArr, array(
                    'tax' => $tax
                        ));
            }
        } else {
            $sArr = array_merge($sArr, array(
                'cmd' => '_cart',
                'upload' => '1',
                    ));

            //TODO: Individual items discount
            $session = Mage::getSingleton('checkout/session');
            if ($session->getData('use_points')) {
                if ($a->getBaseGrandTotal() || $b->getBaseGrandTotal()) {
                    $pointsAmountUsed = $session->getData('points_amount');
                    $rate = Mage::getModel('points/rate')->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY);
                    $moneyBaseCurrencyForPoints = $rate->exchange($pointsAmountUsed);
                    $sArr = array_merge($sArr, array(
                        'discount_amount_cart' => sprintf('%.2f', $moneyBaseCurrencyForPoints)
                            ));
                }
            }

            $items = $this->getQuote()->getAllItems();
            if ($items) {
                $i = 1;
                $summaryTax = 0;
                foreach ($items as $item) {
                    if ($item->getParentItem()) {
                        continue;
                    }
                    //echo "<pre>"; print_r($item->getData()); echo"</pre>";
                    $sArr = array_merge($sArr, array(
                        'item_name_' . $i => $item->getName(),
                        'item_number_' . $i => $item->getSku(),
                        'quantity_' . $i => $item->getQty(),
                        'amount_' . $i => sprintf('%.2f', ($item->getBaseCalculationPrice() - $item->getBaseDiscountAmount())),
                            ));
                    if ($item->getBaseTaxAmount() > 0) {
                        $summaryTax += $item->getBaseTaxAmount() / $item->getQty();
                    }
                    $i++;
                }
            }
        }

        $totalArr = $a->getTotals();
        $shipping = sprintf('%.2f', $this->getQuote()->getShippingAddress()->getBaseShippingAmount());
        if ($shipping > 0 && !$this->getQuote()->getIsVirtual()) {
            if ($transaciton_type == 'O') {
                $sArr = array_merge($sArr, array(
                    'shipping' => $shipping
                        ));
            } else {
                $shippingTax = $this->getQuote()->getShippingAddress()->getBaseShippingTaxAmount();
                $sArr = array_merge($sArr, array(
                    'item_name_' . $i => $totalArr['shipping']->getTitle(),
                    'quantity_' . $i => 1,
                    'amount_' . $i => sprintf('%.2f', $shipping),
                        ));
                $summaryTax += $shippingTax;
                $i++;
            }
        }

        if ($transaciton_type != 'O') {
            $sArr = array_merge($sArr, array(
                'tax_cart' => sprintf('%.2f', $summaryTax),
                    ));
        }

        $sReq = '';
        $sReqDebug = '';
        $rArr = array();


        foreach ($sArr as $k => $v) {
            /*
              replacing & char with and. otherwise it will break the post
             */
            $value = str_replace("&", "and", $v);
            $rArr[$k] = $value;
            $sReq .= '&' . $k . '=' . $value;
            $sReqDebug .= '&' . $k . '=';
            if (in_array($k, $this->_debugReplacePrivateDataKeys)) {
                $sReqDebug .= '***';
            } else {
                $sReqDebug .= $value;
            }
        }

        if ($this->getDebug() && $sReq) {
            $sReq = substr($sReq, 1);
            $debug = Mage::getModel('paypal/api_debug')
                    ->setApiEndpoint($this->getPaypalUrl())
                    ->setRequestBody($sReq)
                    ->save();
        }

        return $rArr;
    }

}
