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
class Unirgy_Giftcert_Model_Quote_Total extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('giftcert');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $address->setGiftcertAmount(0);
        $address->setBaseGiftcertAmount(0);

        $quote = $address->getQuote();
        $addressType = $address->getAddressType();
        if ($addressType=='billing' && !$quote->isVirtual()) {
            return $this;
        }

        if (!$quote->getGiftcertCode()) {
            return $this;
        }
        /* @var $hlp Unirgy_Giftcert_Helper_Data */
        $hlp = Mage::helper('ugiftcert');
        $store = $address->getQuote()->getStore();
        $cert = Mage::getModel('ugiftcert/cert');

        $certCodes = array_unique(preg_split('#\s*,\s*#', $quote->getGiftcertCode()));
        $finalCertCodes = array();
        $baseBalances = array();
        $balances = array();

        $totalBaseAmount = 0;
        $totalLocalAmount = 0;

        $excluded = explode(',', Mage::getStoreConfig('ugiftcert/totals/exclude'));
        if ($address->getAllBaseTotalAmounts()) {
            $total_amounts = $address->getAllBaseTotalAmounts();
            $baseTotal = array_sum($total_amounts);
            if (!empty($excluded)) {
                foreach ($excluded as $excluded_total) {
                    if (isset($total_amounts[$excluded_total])) {
                        $baseTotal -= $total_amounts[$excluded_total];
                    }
                }
            }
        } else {
            $baseTotal = $address->getBaseGrandTotal();
        }


        $qFinalCertCodes = $certCodes;
        $qBaseBalances   = explode(',', $quote->getBaseGiftcertBalances());
        $qBalances       = explode(',', $quote->getGiftcertBalances());
        if (empty($qBaseBalances)) {
            $qBaseBalances   = array_flip($qFinalCertCodes);
            foreach ($qBaseBalances as &$bb) $bb = 0;
            unset($bb);
            $qBalances = $qBaseBalances;
        } else {
            while (sizeof($qBaseBalances)<sizeof($qFinalCertCodes)) array_push($qBaseBalances, 0);
            while (sizeof($qBalances)<sizeof($qFinalCertCodes)) array_push($qBalances, 0);
            $qBaseBalances   = array_combine($qFinalCertCodes, $qBaseBalances);
            $qBalances       = array_combine($qFinalCertCodes, $qBalances);
        }

        foreach ($certCodes as $certCode) {
            $cert->load($certCode, 'cert_number');
            if (!$cert->getId() || $cert->getStatus()!='A') {
                continue; // not found or not active
            }

            if (!($balance = $cert->getBalance())) {
                continue; // no funds
            }

            if(Mage::getStoreConfig('ugiftcert/default/use_conditions')) {
                $virt = $quote->isVirtual();
                if(($virt && $addressType == 'billing') || !$virt) {
                    $hlp->loadProducts($address);
                    $valid = $cert->getConditions()->validate($address);
                    if(!$valid) {
                        $cert->removeFromQuote($quote);
                        Mage::getSingleton('core/session')->addError($hlp->__("Gift certificate '%s' cannot be used with your cart items", $cert->getCertNumber()));
                        continue;
                    }
                }
            }
            $certNumber = $cert->getCertNumber();
            $bbLeft = $cert->getBaseBalance()-$qBaseBalances[$certNumber];

            if ($baseTotal == 0 || abs($bbLeft)<=0.001) {
                continue;
            }

            $finalCertCodes[] = $certNumber;

#Mage::log($cert->getBalance().','.$cert->getBaseBalance().','.$baseTotal, null, Unirgy_Giftcert_Helper_Data::LOG_NAME);
            $baseAmount = min($bbLeft, $baseTotal);
            $totalBaseAmount += $baseAmount;
            $baseTotal -= $baseAmount;

            $baseBalances[] = $baseAmount;
            $balances[] = $store->convertPrice($baseAmount, false);
            $qBaseBalances[$certNumber] += $baseAmount;
            $qBalances[$certNumber]     += $store->convertPrice($baseAmount, false);
#Mage::log($baseAmount.','.$totalBaseAmount.','.$baseTotal, null, Unirgy_Giftcert_Helper_Data::LOG_NAME);
        }

        $address->setGiftcertCode(join(', ', $finalCertCodes));
        $address->setBaseGiftcertBalances(join(',', $baseBalances));
        $address->setGiftcertBalances(join(',', $balances));

        $quote->setBaseGiftcertBalances(join(',', array_values($qBaseBalances)));
        $quote->setGiftcertBalances(join(',', array_values($qBalances)));

        $address->setBaseGiftcertAmount($totalBaseAmount);
        $address->setGiftcertAmount($store->convertPrice($totalBaseAmount, false));

        if (version_compare(Mage::getVersion(), '1.4', '>=')) {
            $this->_addAmount(-$address->getGiftcertAmount());
            $this->_addBaseAmount(-$address->getBaseGiftcertAmount());
        } else {
            $address->setBaseGrandTotal($baseTotal);
            $address->setGrandTotal($store->convertPrice($baseTotal, false));
        }

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getGiftcertAmount();
        if ($amount!=0) {
            $quote = $address->getQuote();
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => Mage::helper('ugiftcert')->__('Gift Certificates'),
                'value' => $amount,
                'giftcert_code' => $address->getGiftcertCode(),
                'base_balances' => $address->getBaseGiftcertBalances(),
                'balances' => $address->getGiftcertBalances(),
            ));
        }
        return $this;
    }
}
