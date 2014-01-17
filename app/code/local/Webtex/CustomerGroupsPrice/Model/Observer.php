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

class Webtex_CustomerGroupsPrice_Model_Observer
{
    public function layeredPrice($observer)
    {
        $collection = $observer->getCollection();
        $websiteId  = Mage::app()->getStore()->getWebsiteId();
        $query = Mage::app()->getRequest()->getQuery();
        if(empty($query ) || !isset($query['price'])){
            return $this;
        }

        if(is_array($query['price'])){
            $begin = $query['price']['from'];
            $to    = $query['price']['to']+1;
            if(empty($begin)) {
                return $this;
            }
            if(empty($query['price']['to'])) {
               $to = PHP_INT_MAX;
            }
        } else if(strstr($query['price'],'-')) {
            $pr = explode('-',$query['price']);
            $begin = $pr[0];
            if(empty($begin)) {
                $begin = 0;
            } 
            $to    = $pr[1]*1 > 0 ? $pr[1] : PHP_INT_MAX;
        } else {
            list($index, $range) = explode(',', $query['price']);
            $begin = $index;
            $to = $range;
        }

        if(($customer = Mage::getModel('customer/session')->getCustomer()) && $customer->getGroupId()){
            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            
            $col = "IFNULL(cgp_sprices.price, IFNULL(
                    cgp_prices.price,
                    IF(
                        cgp_global.price IS NULL,
                        IF(price_index.final_price IS NULL,price_index.min_price,price_index.final_price),
                        IF(
                            cgp_global.price_type=2,
                            IF(
                                SUBSTRING(cgp_global.price, 1, 1) IN ('+', '-'),
                                IF(price_index.final_price IS NULL,price_index.min_price * (100 + cgp_global.price) / 100 ,price_index.final_price * (100 + cgp_global.price) / 100),
                                IF(price_index.final_price IS NULL,price_index.min_price * cgp_global.price / 100,price_index.final_price * cgp_global.price / 100)
                            ),
                            IF(
                                SUBSTRING(cgp_global.price, 1, 1) IN ('+', '-'),
                                IF(price_index.final_price IS NULL,price_index.min_price + cgp_global.price,price_index.final_price + cgp_global.price),
                                cgp_global.price
                            )
                        )
                    )))";

            $from = $collection->getSelect()->getPart('from');

            if(!in_array('cgp_prices', array_keys($from))){
                $_where = $collection->getSelect()->getPart('where');
                $collection->getSelect()
                       ->joinLeft(array('cgp_prices' => $tablePrefix.'customergroupsprice_prices'),
                            'cgp_prices.product_id=e.entity_id AND cgp_prices.group_id='.$customer->getGroupId().' and cgp_prices.website_id in (0, '.$websiteId .')',
                            array())
                       ->joinLeft(array('cgp_sprices' => $tablePrefix.'customergroupsprice_special_prices'),
                            'cgp_sprices.product_id=e.entity_id AND cgp_sprices.group_id='.$customer->getGroupId().' and cgp_sprices.website_id in (0, '.$websiteId .')',
                            array())
                       ->joinLeft(array('cgp_global' => $tablePrefix.'customergroupsprice_prices_global'),
                             'cgp_global.group_id='.$customer->getGroupId(),
                            array('cgp_min_price' => $col))
                        ->reset(Zend_Db_Select::WHERE)
                        ->where($col.' >= '.$begin)
                        ->where($col.' < '.$to);

                foreach($_where as $expression){
                    if(!strpos($expression, 'min_price')){
                       if(substr($expression,0,3) == "AND") {
                           $collection->getSelect()->where(substr($expression,4));
                       } else {
                           $collection->getSelect()->where($expression);
                       }
                    }
                }
            }
        }
    }

    public function sortByPrice($observer)
    {
        $websiteId  = Mage::app()->getStore()->getWebsiteId();
        $collection = $observer->getCollection();
        $orderPart = $collection->getSelect()->getPart(Zend_Db_Select::ORDER);
        $isPriceOrder = false;
        foreach($orderPart as $v){
            if(is_array($v) && $v[0] == 'price_index.min_price'){
                $isPriceOrder = true;
                $orderDir = $v[1];
            }
        }

        $customer = Mage::getModel('customer/session')->getCustomer();

        if(!$isPriceOrder || !$customer->getGroupId()){
            return $this;
        }

        $from = $collection->getSelect()->getPart('from');
        if(!in_array('cgp_prices', array_keys($from))){

            $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
            
            $col = "IFNULL(cgp_sprices.price, IFNULL(
                    cgp_prices.price,
                    IF(
                        cgp_global.price IS NULL,
                        IF(price_index.final_price IS NULL,price_index.min_price,price_index.final_price),
                        IF(
                            cgp_global.price_type=2,
                            IF(
                                SUBSTRING(cgp_global.price, 1, 1) IN ('+', '-'),
                                IF(price_index.final_price IS NULL,price_index.min_price * (100 + cgp_global.price) / 100 ,price_index.final_price * (100 + cgp_global.price) / 100),
                                IF(price_index.final_price IS NULL,price_index.min_price * cgp_global.price / 100,price_index.final_price * cgp_global.price / 100)
                            ),
                            IF(
                                SUBSTRING(cgp_global.price, 1, 1) IN ('+', '-'),
                                IF(price_index.final_price IS NULL,price_index.min_price + cgp_global.price,price_index.final_price + cgp_global.price),
                                cgp_global.price
                            )
                        )
                    )))";

            $collection->getSelect()
                   ->joinLeft(array('cgp_prices' => $tablePrefix.'customergroupsprice_prices'),
                        'cgp_prices.product_id=e.entity_id AND cgp_prices.group_id='.$customer->getGroupId().' and cgp_prices.website_id in (0, '.$websiteId. ')',
                        array())
                   ->joinLeft(array('cgp_sprices' => $tablePrefix.'customergroupsprice_special_prices'),
                        'cgp_sprices.product_id=e.entity_id AND cgp_sprices.group_id='.$customer->getGroupId().' and cgp_sprices.website_id in (0, '.$websiteId .')',
                        array())
                   ->joinLeft(array('cgp_global' => $tablePrefix.'customergroupsprice_prices_global'),
                         'cgp_global.group_id='.$customer->getGroupId(),
                        array('cgp_min_price' => $col));

            $collection->getSelect()
                ->reset(Zend_Db_Select::ORDER)
                ->order('CAST(cgp_min_price as signed)'.$orderDir);
        }
    }

	public function productSaveAfter($observer)
	{
		$request   = Mage::app()->getRequest();
		$websiteId = Mage::app()->getStore($request->getParam('store'))->getWebsiteId(); 
		if($websiteId == 0) {
		    $websiteId = 1;
		}
		foreach($request->getParams() as $key => $value){
		        if($key == 'configurable_attributes_data') {
		           $conf_data = Mage::helper('core')->jsonDecode($value);
		        }
			if(strpos($key, 'customergroupsprice_') === false ){
				continue;
			}
			$data = explode('_', $key);
			if(count($data) != 4){
				continue;
			}
			if($data[1]=='') {
			   $data[1] = $conf_data[0]['id'];
			}
			$attrPrices = Mage::getModel('customergroupsprice/attributes');
			$attrPrices->loadByData($data[1], $data[2], $data[3], $websiteId)
					->setAttributeId($data[1])
					->setValueId($data[2])
					->setGroupId($data[3])
					->setWebsiteId($websiteId)
					->setPrice($value);
			if($value != 0) {
				$attrPrices->save();
			} else {
				$attrPrices->delete();
			}
		}
	}

	public function configurablePrice($observer)
	{
		if(Mage::app()->getRequest()->getModuleName() != 'checkout'){
			return $this;
		}
		$product = $observer->getProduct();
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		if(!$customer->getEntityId()){
			return $this;
		}
		if ($product->getCustomOption('attributes')) {
                    $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
                }
		$finalPrice = 0;
		$websiteId  = Mage::app()->getStore()->getWebsiteId();
		foreach($selectedAttributes as $attrId => $valueId){
			$superAttr = Mage::getModel('catalog/product_type_configurable_attribute')->getCollection();
			$superAttr->getSelect()
					->where('product_id='.$product->getId().' and attribute_id='.$attrId);
			$superAttrId = $superAttr->getData();

			$price = Mage::getModel('customergroupsprice/attributes')->loadByData($superAttrId[0]['product_super_attribute_id'], $valueId, $customer->getGroupId(),$websiteId);
			$price = $price->getData();
			if(isset($price['price']) && $price['price'] > 0){
				$finalPrice += $price['price'];
			} else {
			    $price = Mage::getModel('customergroupsprice/attributes')->loadByData($superAttrId[0]['product_super_attribute_id'], $valueId, $customer->getGroupId(),0);
			    $price = $price->getData();
			    if(isset($price['price']) && $price['price'] > 0){
				$finalPrice += $price['price'];
			    }
			}
		}

		$prices = Mage::getModel('customergroupsprice/prices');
        $groupPrice = $prices->getProductPrice($product);
		if($groupPrice){
			$finalPrice -= ($product->getPrice() - $groupPrice);
		}
		$product->setConfigurablePrice($finalPrice);
		return $this;
	}

	public function productPrice($observer)
	{
		$prod = Mage::registry('product');
		if(!$prod || !$prod->getId()){
			return $this;
		}
		$product = Mage::getSingleton('catalog/product')->load($prod->getId());
		$price = Mage::getModel('customergroupsprice/prices')->getProductPrice($product);
		$specialPrice = Mage::getModel('customergroupsprice/specialprices')->getProductPrice($product);
		if($specialPrice) {
	            $observer->getResponseObject()->setAdditionalOptions(array('productPrice'=>$specialPrice));
	        } else if($price) {
	            $observer->getResponseObject()->setAdditionalOptions(array('productPrice'=>$price));
		}
		return $this;
	}

	public function frontCollectionLoadAfter($observer)
	{
	  if(!Mage::helper('customergroupsprice')->isGroupActive(Mage::getSingleton('customer/session')->getCustomer()->getGroupId())) {
	      return $this;
	  }
	      if(!Mage::helper('customer')->isLoggedIn()) { 
	          if(Mage::helper('customergroupsprice')->isUseScp()) {
	              foreach($observer->getCollection() as $_item) {
	                  if($_item->getTypeId() == 'configurable') {
	                      $_item->setConfPrice($_item,$_item->getPrice());
	                  }
	              }
	          }
	          return $this;
	      }
	      $prices = Mage::getModel('customergroupsprice/prices');
	      $specialPrices = Mage::getModel('customergroupsprice/specialprices');
	      foreach($observer->getCollection() as $_item) {
	          $oldPrice        = $_item->getData('price');
	          $price            = $prices->getProductPrice($_item);
	          $specialPrice     = $specialPrices->getProductPrice($_item);
                  $baseSpecialPrice = $_item->getSpecialPrice();
                  if($baseSpecialPrice == $_item->getMinimalPrice()) {
                      $_item->setMinimalPrice(null);
                  }
                  $specialPriceFrom = $_item->getSpecialFromDate();
                  $specialPriceTo   = $_item->getSpecialToDate();
	          $_item->setPrice($price);
	          $_item->setSpecialPrice($specialPrice);
                  $rulePrice  = null ; //Mage::getModel('catalogrule/rule')->calcProductPriceRule($_item, $price);
	          if($_item->getTypeId() != 'bundle') {
	              $finalPrice = $_item->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$_item->getId());
	              $_item->setCalculatedFinalPrice($finalPrice);
	              $_item->setFinalPrice($finalPrice);
	              if($_item->getTypeId() == 'grouped'){
	                 $_item->setGroupedPrice($_item);
	              }
	              if($_item->getTypeId() == 'configurable') {
	                 $_item->setConfPrice($_item,$oldPrice);
	              }
	          } else {
	             $minP = $_item->getMinimalPrice($_item);
	             $maxP = $_item->getMaximalPrice($_item);
	             $_item->setMinPrice($minP);
	             $_item->setMinimalPrice($minP);
	             $_item->setMaxPrice($maxP);
	             $_item->setMaximalPrice($maxP);
	          }
              }
              
          return $this;
	}
	
	public function frontProductLoadAfter($observer)
	{
	  if(!Mage::helper('customergroupsprice')->isGroupActive(Mage::getSingleton('customer/session')->getCustomer()->getGroupId())) {
	      return $this;
	  }
	      $product = $observer->getEvent()->getProduct();
	      $prices = Mage::getModel('customergroupsprice/prices');
	      $specialPrices = Mage::getModel('customergroupsprice/specialprices');
	      $price            = $prices->getProductPrice($product);
	      $specialPrice     = $specialPrices->getProductPrice($product);
              $specialPriceFrom = $product->getSpecialFromDate();
              $specialPriceTo   = $product->getSpecialToDate();
	      $product->setPrice($price);
	      $product->setSpecialPrice($specialPrice);
              $rulePrice  = null; //Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $price);
	      if($product->getTypeId() != 'bundle') {
	          $finalPrice = $product->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$product->getId());
	          $product->setCalculatedFinalPrice($finalPrice);
	          $product->setFinalPrice($finalPrice);
	      }
	      return $this;
	}

	public function processFrontFinalPrice($observer)
	{
	  if(!Mage::helper('customergroupsprice')->isGroupActive(Mage::getSingleton('customer/session')->getCustomer()->getGroupId())) {
	      return $this;
	  }
	    $product = $observer->getEvent()->getProduct();
	    $prices = Mage::getModel('customergroupsprice/prices');
	    $specialPrices = Mage::getModel('customergroupsprice/specialprices');
	    if($product->getTypeId() != 'bundle') {
	          $basePrice        = $prices->getProductPrice($product);
	          $specialPrice     = $specialPrices->getProductPrice($product);
                  $specialPriceFrom = $product->getSpecialFromDate();
                  $specialPriceTo   = $product->getSpecialToDate();
                  $specialPriceFrom = $product->getSpecialFromDate();
                  $specialPriceTo = $product->getSpecialToDate();
                  $rulePrice  = null; //Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $basePrice);
                  $finalPrice = $product->getPriceModel()->calculatePrice(
                                $basePrice,
                                $specialPrice,
                                $specialPriceFrom,
                                $specialPriceTo,
                                $rulePrice,
                                null,
                                null,
                                $product->getId());
                 $product->setCalculatedFinalPrice($finalPrice);
                 $product->setData('final_price', min($product->getData('final_price'),$finalPrice));
            }
            return $this;
    }

    public function processQuoteItem($observer)
	{
        if("Mage_Api" == Mage::app()->getRequest()->getControllerModule() || "Mage_Api2" == Mage::app()->getRequest()->getControllerModule()){
	    $items = $observer->getEvent()->getItems();
        foreach($items as $key=>$item){
            if($key==0 && !Mage::helper('customergroupsprice')->isGroupActive($item->getQuote()->getCustomer()->getGroupId())){
                return $this;
            }else{
                $customerGroup=$item->getQuote()->getCustomer()->getGroupId();
            }
            if(!$item->getCustomPriceSeted()){
                $product = $item->getProduct();
                $prices = Mage::getModel('customergroupsprice/prices');
                $specialPrices = Mage::getModel('customergroupsprice/specialprices');
                if($product->getTypeId() != 'bundle') {
                  $basePrice        = $prices->getGroupProductPrice($product,$customerGroup);
                  $specialPrice     = $specialPrices->getGroupProductPrice($product,$customerGroup);
                      $specialPriceFrom = $product->getSpecialFromDate();
                      $specialPriceTo = $product->getSpecialToDate();
                      $rulePrice  = null; //Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $basePrice);
                      $finalPrice = $product->getPriceModel()->calculatePrice(
                                    $basePrice,
                                    $specialPrice,
                                    $specialPriceFrom,
                                    $specialPriceTo,
                                    $rulePrice,
                                    null,
                                    null,
                                    $product->getId());
                    $item->setCustomPrice($finalPrice);
                    $item->setOriginalCustomPrice($finalPrice);
                    $item->setCustomPriceSeted(true);
                }
            }
        }
    }
        return $this;
    }
        
	public function backProductLoadAfter($observer)
	{
	  $rule_data = Mage::registry('rule_data');
	  if($rule_data) {
	   $data = $rule_data->getData();
	   if($data['customer_group_id'] && Mage::helper('customergroupsprice')->isGroupActive($data['customer_group_id'])) {
	      $product = $observer->getEvent()->getProduct();
	      $prices = Mage::getModel('customergroupsprice/prices');
	      $specialPrices = Mage::getModel('customergroupsprice/specialprices');
	      $price            = $prices->getProductPrice($product);
	      $specialPrice     = $specialPrices->getProductPrice($product);
              $specialPriceFrom = $product->getSpecialFromDate();
              $specialPriceTo   = $product->getSpecialToDate();
	      $product->setPrice($price);
	      $product->setSpecialPrice($specialPrice);
              $rulePrice  = null; //Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $price);
	      if($product->getTypeId() != 'bundle') {
	          $finalPrice = $product->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$product->getId());
	          $product->setCalculatedFinalPrice($finalPrice);
	          $product->setFinalPrice($finalPrice);
	      }
	     }
	    }
	    return $this;
	}

	public function processBackFinalPrice($observer)
	{
	  $rule_data = Mage::registry('rule_data');
	  if($rule_data) {
	   $data = $rule_data->getData();
	   if($data['customer_group_id'] && Mage::helper('customergroupsprice')->isGroupActive($data['customer_group_id'])) {
	    $prod = $observer->getEvent()->getProduct();
	    $product = Mage::getModel('catalog/product')->load($prod->getId());
	    $prices = Mage::getModel('customergroupsprice/prices');
	    $specialPrices = Mage::getModel('customergroupsprice/specialprices');
	    if($product->getTypeId() != 'bundle') {
	          $price     = $prices->getGroupProductPrice($product,$data['customer_group_id']);
	          $sPrice    = $specialPrices->getGroupProductPrice($product,$data['customer_group_id']);
                  $rulePrice  = null; //Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $price);
                  $basePrice = $price;
                  $specialPrice = $sPrice;
                  $prod->setPrice($basePrice);
                  $prod->setSpecialPrice($specialPrice);
	          $finalPrice = $product->getPriceModel()->calculatePrice($price,$specialPrice,$specialPriceFrom,$specialPriceTo,$rulePrice,null,null,$product->getId());
	          $prod->setCalculatedFinalPrice($finalPrice);
	          $prod->setFinalPrice($finalPrice);
                 /* if($specialPrice){
                      $product->setCalculatedFinalPrice(min($basePrice,$specialPrice));
                      $product->setCalculationPrice(min($basePrice,$specialPrice));
                      $product->setData('final_price',min($basePrice,$specialPrice));
                  } else {
                      $product->setCalculatedFinalPrice($basePrice);
                      $product->setCalculationPrice($basePrice);
                      $product->setData('final_price',$basePrice);
                  }*/
            }
           }
          }
          return $this;
        }
}