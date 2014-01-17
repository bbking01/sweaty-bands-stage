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

class Webtex_CustomerGroupsPrice_Model_Catalog_Product_Type_Configurable_Price extends Mage_Catalog_Model_Product_Type_Configurable_Price
{

    public function getFinalPrice($qty=null, $product)
    {
        if(!Mage::helper('customer')->isLoggedIn()) {
            return parent::getFinalPrice($qty,$product);
        }
        
	$customer = Mage::getSingleton('customer/session')->getCustomer();

        if(!Mage::helper('customergroupsprice')->isGroupActive($customer->getGroupId())) {
            return parent::getFinalPrice($qty,$product);
        }

        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }
        
        $finalPrice = parent::getFinalPrice($qty,$product);

        $product->getTypeInstance(true)
            ->setStoreFilter($product->getStore(), $product);
        $attributes = $product->getTypeInstance(true)
            ->getConfigurableAttributes($product);
		
        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {
            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
        }

        $websiteId = Mage::app()->getStore()->getWebsiteId();
        $basePrice = $finalPrice;
        $originalPrice = $product->getPrice();

        // Make array for used simple products
        $usedProducts = array();

        foreach ($attributes as $attribute) {
            $attributeId = $attribute->getProductAttribute()->getId();
            $value = $this->_getValueByIndex(
                $attribute->getPrices() ? $attribute->getPrices() : array(),
                isset($selectedAttributes[$attributeId]) ? $selectedAttributes[$attributeId] : null
            );
            $delta = false;
            if($value) {
                if(isset($value['pricing_value'])){
                    $delta = true;
                }
    		if($price = Mage::getModel('customergroupsprice/attributes')->loadByData($value['product_super_attribute_id'], $value['value_index'], $customer->getGroupId(),$websiteId)){
		     if($price['price']) {
		          $value['pricing_value'] = $price['price'];
		     } else {
		         $price = Mage::getModel('customergroupsprice/attributes')->loadByData($value['product_super_attribute_id'], $value['value_index'], $customer->getGroupId(),0);
		         if($price['price']) {
		              $value['pricing_value'] = $price['price'];
		         } else
		              $value['pricing_value'] = 0;
		         }
		    }
            }
            if($value['pricing_value'] != 0){
	        $finalPrice += $this->_calcSelectionPrice($value, $basePrice);
	        if($delta) {
	            $finalPrice -= $value['pricing_value'];
	        }
	    }
        }
        if(!$customer || !$customer->getId() || !Mage::helper('customergroupsprice')->isEnabled()) {
            return parent::getFinalPrice($qty,$product);
        } else {
            $product->setFinalPrice($finalPrice);
            return max(0, $product->getData('final_price'));
        }
    }

    public function getValueByIndex($prices,$attributeId)
    {
       return $this->_getValueByIndex($prices,$attributeId);
    }
}