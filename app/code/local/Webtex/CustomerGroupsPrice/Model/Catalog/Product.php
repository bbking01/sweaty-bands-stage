<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Groups Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @author     Webtex Dev Team <dev@webtex.com>
 */
class Webtex_CustomerGroupsPrice_Model_Catalog_Product extends Mage_Catalog_Model_Product
{
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Get product price throught type instance
     *
     * @return unknown
     */
    public function getPrice()
    {
        $prices = Mage::getModel('customergroupsprice/prices');
        $price = $prices->getProductPrice($this);
        return $price;
    }
    

    public function setGroupedPrice($product)
    {
        $prices  = array();
        $prices[]= $product->getPrice();
        $pr      = Mage::getModel('customergroupsprice/prices');
        $spr     = Mage::getModel('customergroupsprice/specialprices');

        $collection = $this->getTypeInstance(true)
            ->getAssociatedProducts($this);

         foreach($collection as $item){
            $price  = $pr->getProductPrice($item);
            $sprice = $spr->getProductPrice($item);
            if($sprice > 0){
               $prices[] = min($price,$sprice);
            } else {
               $prices[] = $price;
            }
         }
        $this->setMinPrice(min($prices));
        $this->setMinimalPrice(min($prices));
        return $this;
    }

    public function setConfPrice($product,$oldPrice)
    {
        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $groupId = Mage::helper('customer')->getCustomer()->getGroupId();
        $minPrices  = array();
        $basePrice = $product->getPrice();
        $minPrices[] = $basePrice;
        $attributes = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);

        if(Mage::helper('customergroupsprice')->isUseScp()) {
            $maxDelta = array();
            $maxDelta[] = 0;
            $options = $product->getProductOptionsCollection();
            foreach($options as $option){
                foreach($option->getValues() as $value) {
                    $v = $value->getData();
                    if($v['price_type'] == 'fixed'){
                        $maxDelta[] = abs($v['price']);
                    } else {
                        $maxDelta[] = abs($basePrice / 100 * $v['price']);
                    }
                }
            }
            $delta = max($maxDelta);
            $collection = $product->getTypeInstance()->getUsedProductCollection($product);
            foreach($collection as $simple) {
                  $prod = Mage::getModel('catalog/product')->load($simple->getId());
                  $minPrices[] =  $prod->getFinalPrice() - $delta;
            }
        } else {
            foreach ($attributes as $attribute) {
              foreach ($attribute['values'] as $value){
                if($price = Mage::getModel('customergroupsprice/attributes')->loadByData($value['product_super_attribute_id'], $value['value_index'], $groupId, $websiteId)){
                    if($price['price']) {
                        $minPrices[] = $basePrice + $price['price'];
                    }
                } else {
                    $price = Mage::getModel('customergroupsprice/attributes')->loadByData($value['product_super_attribute_id'], $value['value_index'], $groupId, 0);
                    if($price['price']) {
                        $minPrices[] = $basePrice + $price['price'];
                    }
                }
              }
            }
        }
        foreach($minPrices as $key => $value) {
            if($value < 0){
                $minPrices[$key] = $basePrice;
            }
        }
        $product->setMinPrice(min($minPrices));
        $product->setMinimalPrice(min($minPrices));
        return $this;
    }

}
