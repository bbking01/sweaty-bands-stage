<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Sendfriend
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Magestore_Design_Block_Design extends Mage_Core_Block_Template
{
	/**
     * Retrieve username for form field
     *
     * @return string
     */
    public function getProductId()
    {
        return $this->getRequest()->getParam('id');
    }

    public function getCategoryId()
    {
		//Code added by bhagyashri to get the  categoryid  from productid started
		
		
		$id = $this->getRequest()->getParam('id');
		$catid = $this->getRequest()->getParam('cat_id');
		
		 if($catid == "")
		{
				$product =  Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id'));
				$categoryIds = $product->getCategoryIds();
			
				$cnt = count($categoryIds);
				if( is_array($categoryIds) &&   ($cnt> 1) )
				{
					return $categoryIds[0];
				}else
				{
					return $categoryIds;
				}
				
		
		}//Code added by bhagyashri to get the   categoryid  from productid ended
		else
		{
			return $this->getRequest()->getParam('cat_id', null);
		}
		  
    }	
	
	public  function getDesignData($pid)
	{
		
		//$_product = Mage::getModel('catalog/product')->load($pid);	
		
		if($this->getRequest()->getParam('qty')>0)
		{
			$data['qty'] = $this->getRequest()->getParam('qty');
		}
		else
		{
			$data['qty'] = 1;
		}
		
		if($this->getRequest()->getParam('super_attribute'))
		{
			/* $data['color'] = $_POST['super_attribute']['92'];
			$data['size'] = $_POST['super_attribute']['142']; */
			$productData = $this->getRequest()->getParam('super_attribute');
			
			$productModel = Mage::getModel('catalog/product');
			/*get color attribute id from name Added by Ajay*/
			$colorAttribute = $productModel->getResource()->getAttribute("color");
			$colorId = $colorAttribute->getAttributeId();
			/*get size attribute id from name Added by Ajay*/
			$sizeAttribute = $productModel->getResource()->getAttribute("size");
			$sizeId = $sizeAttribute->getAttributeId();
			$data['color'] = $productData[$colorId];
			$data['size'] = $productData[$sizeId];				 
		}
		
		return $data;

	}
	
	public function getColorSizeAndQuantity()
	{
		$quoteId = $this->getRequest()->getParam('cart_id', null);
		
		$simpleQuoteData = Mage::getModel('sales/quote_item')->load($quoteId,'parent_item_id');		
		$productId = $simpleQuoteData->getProductId();
		$product = Mage::getModel('catalog/product')->load($productId);
		$colorId = $product->getColor();
		$sizeId = $product->getSize();
		
		$configQuoteData = Mage::getModel('sales/quote_item')->load($quoteId);
		$id = $configQuoteData->getProductId();		
		$cartQty = $configQuoteData->getQty();
		
		$data['id'] = $id;
		$data['colorId'] = $colorId;
		$data['sizeId'] = $sizeId;
		$data['qty'] = $cartQty;		
		return $data;
	}
	
	public function getDesignId()
    {
        return $this->getRequest()->getParam('design_id', null);
    }
	public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id', null);
    }
	public function getCartId()
    {
        return $this->getRequest()->getParam('cart_id', null);
    }
}