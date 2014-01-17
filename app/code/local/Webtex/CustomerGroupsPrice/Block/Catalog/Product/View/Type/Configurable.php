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

class Webtex_CustomerGroupsPrice_Block_Catalog_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
	public function getJsonConfig()
    {
		//if(!Mage::helper('customer')->isLoggedIn()) {
		//    return parent::getJsonConfig();
		//}
		
		$config = parent::getJsonConfig();
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$config = Mage::helper('core')->jsonDecode(parent::getJsonConfig());
		$usedProducts = array();
		$websiteId = Mage::app()->getStore()->getWebsiteId();
		foreach($config['attributes'] as $attrId => $attr){
			$superAttr = Mage::getModel('catalog/product_type_configurable_attribute')->getCollection();
			$superAttr->getSelect()
					->where('product_id='.$this->getProduct()->getId().' and attribute_id='.$attrId);
			$superAttrId = $superAttr->getData();
			$basePrice   = $this->getProduct()->getFinalPrice();
			foreach($attr['options'] as $k => $value){
			    if($this->helper('customergroupsprice')->isUseScp()) {
			     // Check if applayed discount
			     if(!in_array($value['products'][0], $usedProducts)) { 
			         $usedProducts[] = $value['products'][0];
                                 $prod = Mage::getModel('catalog/product')->load($value['products'][0]);
                                 $pr  = $prod->getData('price');
                                 $spr = $prod->getData('special_price');
                                 // Check PriceRule
                                 $rpr  = Mage::getModel('catalogrule/rule')->calcProductPriceRule($prod, $pr);
                                 $rspr = Mage::getModel('catalogrule/rule')->calcProductPriceRule($prod, $spr);
                                 // if exists: $price = PriceRule
                                 if($rpr > 0) {
                                    $pr = $rpr;
                                 }
                                 if($rspr > 0) {
                                    $spr = $rspr;
                                 }
                                 // end PriceRule
                                 if($spr > 0) {
                                   $price = $spr - $basePrice;
                                 } else {
                                   $price = $pr - $basePrice;
                                 }
                                $config['attributes'][$attrId]['options'][$k]['price'] = $price;
			     } else {
			        $config['attributes'][$attrId]['options'][$k]['price'] = 0;
                             }
                            } else {
		                if(!$customer->getEntityId() || !$this->helper('customergroupsprice')->isGroupActive($customer->getGroupId())){
			            return Mage::helper('core')->jsonEncode($config);
		                }
				$price = Mage::getModel('customergroupsprice/attributes')->loadByData($superAttrId[0]['product_super_attribute_id'], $value['id'], $customer->getGroupId(), $websiteId);
				$price = $price->getData();
				
			        if(isset($price['price'])){
				    $config['attributes'][$attrId]['options'][$k]['price'] = $price['price'];
			        } else {
				    $price = Mage::getModel('customergroupsprice/attributes')->loadByData($superAttrId[0]['product_super_attribute_id'], $value['id'], $customer->getGroupId(), $websiteId);
				    $price = $price->getData();
				
        		            if(isset($price['price'])){
				        $config['attributes'][$attrId]['options'][$k]['price'] = $price['price'];
			            }
			        }
			    }
			}
		}
		return Mage::helper('core')->jsonEncode($config);
	}
}