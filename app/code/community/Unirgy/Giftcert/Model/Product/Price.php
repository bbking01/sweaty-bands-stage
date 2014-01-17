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
class Unirgy_GiftCert_Model_Product_Price extends Mage_Catalog_Model_Product_Type_Price
{
    public function getPrice($product)
    {
        return Mage::helper('ugiftcert')->getPrice($product);
/*        switch ($amountConfig['type']) {
        case 'range':
            $price = $amountConfig['from'];
            break;
        case 'dropdown':
            $o = $amountConfig['options'];
            $price = $o[0] ? $o[0] : $o[1];
            break;
        case 'fixed':
            $price = $amountConfig['amount'];
            break;
        default:
            $price = 0;
        }
        return $price;*/
    }

    protected function _applyOptionsPrice($product, $qty, $finalPrice)
    {
        $optionsPrice = parent::_applyOptionsPrice($product, $qty, 0); // get only options price
        if ($amountOption = $product->getCustomOption('amount')) {
            // if there is amount already added, it is the final price
            $finalPrice = $amountOption->getValue();
        }

        // if there were options, add them to final price
        return $finalPrice + $optionsPrice;
    }
}
