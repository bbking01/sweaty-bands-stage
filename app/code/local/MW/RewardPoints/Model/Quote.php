<?php

class MW_RewardPoints_Model_Quote extends Mage_Core_Model_Abstract
{
    protected function _getSession()
    {
    	return Mage::getSingleton('checkout/session');
    }
    
    protected function _getCustomer()
    {
    	return Mage::getModel('rewardpoints/customer')->load(Mage::getSingleton('customer/session')->getCustomer()->getId());
    }
    
	public function collectTotalBefore($argv)
    {
    	if(Mage::helper('rewardpoints')->moduleEnabled())
		{
			$store_id = Mage::app()->getStore()->getId();
	    	$quote = $argv->getQuote();
	    	$address = $quote->isVirtual()?$quote->getBillingAddress():$quote->getShippingAddress();
			$subtotal = $address->getBaseSubtotal();
			$subtotal += $address->getBaseDiscountAmount();
			$spend_point = $quote->getSpendRewardpointCart();
			
			
			$mw_rewardpoints = (int)$quote->getMwRewardpoint();
			$min = (int)Mage::helper('rewardpoints/data')->getMinPointCheckoutStore($store_id);
			//if($min > 0 && $mw_rewardpoints < $min && $min <= $spend_point){
			if($min > 0 && $mw_rewardpoints < $min){
				$quote->setMwRewardpoint(0);
				$quote->setMwRewardpointDiscount(0)->save();
			}
			
			//$spend_point = (int)Mage::helper('rewardpoints')->getMaxPointToCheckOut();
			$max_points_discount = Mage::helper('rewardpoints')->exchangePointsToMoneys($spend_point,$store_id);
			//echo $max_points_discount.'aaaaaaaa'.$spend_point;die();
			$rewardpoint_discount = (double)$quote->getMwRewardpointDiscount();
			//$subtotal_after_rewardpoint = $subtotal + $rewardpoint_discount;
			$baseGrandTotal_after_rewardpoint = $quote->getBaseGrandTotal() + $rewardpoint_discount;
			if($rewardpoint_discount > $baseGrandTotal_after_rewardpoint) 
			{
				$quote->setMwRewardpointDiscount($baseGrandTotal_after_rewardpoint);
				$points = Mage::helper('rewardpoints')->exchangeMoneysToPoints($baseGrandTotal_after_rewardpoint,$store_id);
				$quote->setMwRewardpoint(Mage::helper('rewardpoints')->roundPoints($points,$store_id))->save();
			}
			if($rewardpoint_discount > $max_points_discount){
				$quote->setMwRewardpointDiscount($max_points_discount);
				$quote->setMwRewardpoint(Mage::helper('rewardpoints')->roundPoints($spend_point,$store_id))->save();
				
				if($max_points_discount > $baseGrandTotal_after_rewardpoint){
					
					$quote->setMwRewardpointDiscount($baseGrandTotal_after_rewardpoint);
					$points = Mage::helper('rewardpoints')->exchangeMoneysToPoints($baseGrandTotal_after_rewardpoint,$store_id);
					$quote->setMwRewardpoint(Mage::helper('rewardpoints')->roundPoints($points,$store_id))->save();
				}
			}

			if($customer_id = $quote->getCustomerId()){
				
				$customer_rewarpoint = Mage::getModel('rewardpoints/customer')->load($customer_id)->getMwRewardPoint();
				$product_sell_point = 0;
				foreach ($quote->getAllItems() as $item) {
						$qty = $item->getQty();
						$mw_reward_point = Mage::getModel('catalog/product')->load($item->getProductId())->getData('mw_reward_point_sell_product');
						if($mw_reward_point > 0)$product_sell_point = $product_sell_point + $qty * $mw_reward_point;
					if($product_sell_point > 0 && $customer_rewarpoint < $product_sell_point + $quote->getMwRewardpoint()){
						//$quote->removeItem($item->getId())->save();
						$quote->setMwRewardpointDiscount(0)->setMwRewardpoint(0)->save();
						Mage::getSingleton('checkout/session')->getMessages(true);
						Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('Your balance has not enough point to spend!'));
//                        $url = "http://127.0.0.1/magento1.8rwp/index.php/checkout/cart/";
//                        return Mage::app()->getFrontController()->getResponse()->setRedirect($url);
					} 
				}
				
			}else{
				foreach ($quote->getAllItems() as $item) {
						$mw_reward_point = Mage::getModel('catalog/product')->load($item->getProductId())->getData('mw_reward_point_sell_product');
						if($mw_reward_point >0){
							$quote->removeItem($item->getId())->save();
							Mage::getSingleton('checkout/session')->getMessages(true);
							Mage::getSingleton('checkout/session')->addError(Mage::helper('rewardpoints')->__('For using points to checkout order, please login!'));

						} 
				}
				
			}

		}else{
			$quote = $argv->getQuote();
			$quote->setMwRewardpointDiscount(0);
			$quote->setMwRewardpoint(0)->save();
		}
    }
}