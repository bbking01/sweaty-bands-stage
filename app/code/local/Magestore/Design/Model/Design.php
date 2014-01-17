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

class Magestore_Design_Model_Design extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('design/design');
    }   
	public  function  getFinalPrice($qty=null,$product,$colorid,$sizeid)
        {
			if  (is_null($qty)  &&  !is_null($product->getCalculatedFinalPrice()))  {
					return  $product->getCalculatedFinalPrice();
			}
			//$finalPrice  =  $product->getFinalPrice($qty,  $product);
			$finalPrice  = Mage::getModel('catalog/product_type_price')->getFinalPrice($qty,$product);
			
			/*get discounted price if catalog product rule applied*/
			$store_id = Mage::app()->getStore()->getStoreId();			
			$discounted_price = Mage::getResourceModel('catalogrule/rule')->getRulePrice( 
									Mage::app()->getLocale()->storeTimeStamp($store_id), 
									Mage::app()->getStore($store_id)->getWebsiteId(), 
									Mage::getSingleton('customer/session')->getCustomerGroupId(), 
									$product->getId());

			if ($discounted_price!='') {
				$finalPrice=$discounted_price;
			}
			
			$baseprice = $finalPrice;
			$product->getTypeInstance()->setStoreFilter($product->getStore());
			$_attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
			foreach($_attributes as $_attribute)
			{
				foreach($_attribute->getprices() as $attributeoption)
				{
					if($attributeoption['value_index'] == $sizeid || $attributeoption['value_index'] == $colorid )
					{
						$finalPrice += $this->getPriceToAdd($attributeoption,$baseprice);
					}
				}
			}			
				return  $finalPrice ;
        }
	public function getCustomTierPrice($qutoeqty,$product)
		{
			$cnt = count($product->getTierPrice());
			if($cnt > 0)
			{
				$tierprices = $product->getTierPrice();
				
				foreach($tierprices as $tierprice)				
				{
					$price_qty = $tierprice['price_qty'];
					if( $qutoeqty >= ceil($price_qty)  )
					{
						$newtierprice = $tierprice['price'];
					}
				}
			}
			if(!isset($newtierprice))
			{
				$finalprice =$product->getPrice();
			}
			else
			{
				$finalprice =$newtierprice;
			}
		return $finalprice;
			
		}	
/* 	public function  getValueByIndex($values, $index) {
        foreach ($values as $value) {
            if($value['value_index'] == $index) {
                return $value;
            }
        }
        return false;
    } */
	public function getPriceToAdd($priceInfo, $productPrice)
    {
        if($priceInfo['is_percent']) {
            $ratio = $priceInfo['pricing_value']/100;
            $price = $productPrice * $ratio;
        } else {
            $price = $priceInfo['pricing_value'];
        }
        return $price;
    }	
	
	public function getProductsFromCategory($categoryId)
	{
		$designtoolAttrSetName = "Designtool";
		//Get Attribute set Id from Attribute set name
		$designtoolAttributeSetId = Mage::getModel('eav/entity_attribute_set')
                            ->load($designtoolAttrSetName, 'attribute_set_name')
                            ->getAttributeSetId();
		// Load Category by category id.
		$catagory = Mage::getModel('catalog/category')->load($categoryId);

		// Get product collection by category id.
		$productCollection = Mage::getResourceModel('catalog/product_collection')
							->addCategoryFilter($catagory) //category filter
							->AddFieldToFilter('is_customizable', 1)
							->AddFieldToFilter('status', 1)
							->addAttributeToFilter('type_id', 'configurable')
							->addAttributeToFilter('attribute_set_id',$designtoolAttributeSetId)
							->addAttributeToSelect('*');
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection);
		
		$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><allProducts>';
		foreach($productCollection as $product)
		{
			
			if($product->getis_salable() && $product->getStockItem()->getis_in_stock())
			{
				$configProductId = $product->getEntityId();				
				$configProduct = Mage::getModel('catalog/product')->load($configProductId);
				$isMulticolor = $configProduct->getMulticolor();
				$xmlString .= '<product>';
				$xmlString .= '<name>'.$configProduct->getName().'</name>';
				$xmlString .= '<productID>'.$configProduct->getEntityId().'</productID>';
				$xmlString .= '<code>'.$configProduct->getSku().'</code>';
				$xmlString .= '<shortDesc>'.htmlspecialchars($configProduct->getShortDescription()).'</shortDesc>';
				$xmlString .= '<longDesc>'.htmlspecialchars($configProduct->getDescription()).'</longDesc>';	

				if($configProduct->getThumbnail() == 'no_selection' or $configProduct->getThumbnail()== '')
					$thumbnailImage = '';
				else
					$thumbnailImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($configProduct->getThumbnail());
				$xmlString .= '<thumbImage>'.$thumbnailImage.'</thumbImage>';		
				$xmlString .= '</product>';				
			}
			
		}
		$xmlString .= '</allProducts>';
		return $xmlString;
	}
	public function getProductFromId($productId,$user)
	{
		$storeId = Mage::app()->getStore()->getStoreId();
		
		/*If product id not exist in case of design idea then get the first product id from collection*/
		$designtoolAttrSetName = "Designtool";
		//Get Attribute set Id from Attribute set name
		$designtoolAttributeSetId = Mage::getModel('eav/entity_attribute_set')
                            ->load($designtoolAttrSetName, 'attribute_set_name')
                            ->getAttributeSetId();	

		$productCollection = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToFilter('entity_id', $productId)
					->AddFieldToFilter('is_customizable', 1)					
					->addAttributeToFilter('type_id', 'configurable')
					->addAttributeToFilter('attribute_set_id',$designtoolAttributeSetId)					
					->addAttributeToSelect('*');
		$productCount = count($productCollection->getData());
		
		if($productCount==0 && $user!='')		
		{
			$productCollection = Mage::getModel('catalog/product')
							->getCollection()	
							->AddFieldToFilter('is_customizable', 1)
							->AddFieldToFilter('status', 1)
							->addAttributeToFilter('type_id', 'configurable')
							->addAttributeToFilter('attribute_set_id',$designtoolAttributeSetId)					
							->addAttributeToSelect('*');
						
			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection);		
			$firstProduct = $productCollection->getFirstItem();	
			$productId = $firstProduct->getEntityId();
		}
		
		$configProductId = $productId;
		$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><allProducts>';
		$xmlString .= '	<product>';
		$configProduct = Mage::getModel('catalog/product')->load($configProductId);
		$isMulticolor = $configProduct->getMulticolor();
		$xmlString .= '	<name>'.$configProduct->getName().'</name>';
		$xmlString .= '	<productID>'.$configProduct->getEntityId().'</productID>';
		$xmlString .= '	<code>'.$configProduct->getSku().'</code>';
		/*get Attribute Id of the color*/					
		$colorAttribute = Mage::getSingleton("eav/config")->getAttribute('catalog_product', 'color');
		$colorId = $colorAttribute->getAttributeId();
		/*get Attribute Id of the size*/					
		$sizeAttribute = Mage::getSingleton("eav/config")->getAttribute('catalog_product', 'size');
		$sizeId = $sizeAttribute->getAttributeId();
		$xmlString .= '	<colorId>'.$colorId.'</colorId>';
		$xmlString .= '	<sizeId>'.$sizeId.'</sizeId>';
		$xmlString .= '	<shortDesc>'.htmlspecialchars($configProduct->getShortDescription()).'</shortDesc>';
		$xmlString .= '	<longDesc>'.htmlspecialchars($configProduct->getDescription()).'</longDesc>';	

		if($configProduct->getThumbnail() == 'no_selection' or $configProduct->getThumbnail()== '')
			$thumbnailImage = '';
		else
			$thumbnailImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($configProduct->getThumbnail());

		if($configProduct->getStatus() == '1')
			$status = 'yes';
		else
			$status = 'no';

		if($isMulticolor == '1')
			$multiColor = 'yes';
		else
			$multiColor = 'no';

		$configFrontImage = $configProduct->getFrontImage();
		if($configFrontImage == 'no_selection' or $configFrontImage== '')
			$configFrontImage = '';
		else
			$configFrontImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($configFrontImage);

		$configBackImage = $configProduct->getBackImage();
		if($configBackImage == 'no_selection' or $configBackImage== '')
			$configBackImage = '';
		else
			$configBackImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($configBackImage);	

		$configLeftImage = $configProduct->getLeftImage();
		if($configLeftImage == 'no_selection' or $configLeftImage== '')
			$configLeftImage = '';
		else
			$configLeftImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($configLeftImage);	

		$configRightImage = $configProduct->getRightImage();
		if($configRightImage == 'no_selection' or $configRightImage== '')
			$configRightImage = '';
		else
			$configRightImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($configRightImage);	

		$xmlString .= '	<defaultThumb>'.$thumbnailImage.'</defaultThumb>';
		$xmlString .= '	<multiColor>'.$multiColor.'</multiColor>';
		$xmlString .= '	<allImages><image>';
		$xmlString .= '	<thumbImage>'.$thumbnailImage.'</thumbImage>';
		$xmlString .= '	</image></allImages>';
		$noOfSides = $configProduct->getAttributeText('no_of_sides');
		$xmlString .= '	<noofSides>'.$noOfSides.'</noofSides>';
		$xmlString .= '	<productImages><image>';
		switch($noOfSides)
		{
			case 4:
				$xmlString .= '	<frontImage>'.$configFrontImage.'</frontImage>';
				$xmlString .= '	<backImage>'.$configBackImage.'</backImage>';
				$xmlString .= '	<lefImage>'.$configLeftImage.'</lefImage>';
				$xmlString .= '	<rightImage>'.$configRightImage.'</rightImage>';
			break;
			case 3:
				$xmlString .= '	<frontImage>'.$configFrontImage.'</frontImage>';
				$xmlString .= '	<backImage>'.$configBackImage.'</backImage>';
				if($configLeftImage != '')
				{
					$xmlString .= '	<lefImage>'.$configLeftImage.'</lefImage>';		
				}   
				else
				{
					$xmlString .= '	<rightImage>'.$configRightImage.'</rightImage>';
				}    
			break;
			case 2:
				default: 
				$xmlString .= '	<frontImage>'.$configFrontImage.'</frontImage>';
				$xmlString .= '	<backImage>'.$configBackImage.'</backImage>';
			break;			
		}	
		$xmlString .= '	</image>';
		$xmlString .= '<maskImage><frontMaskImage/><backMaskImage/><lefMaskImage/><rightMaskImage/></maskImage>';
		
		$xmlString .= '</productImages>';

		/*get configure area from designtool_configarea table*/
		$configAreaData = Mage::getModel('design/configarea')->load($configProductId,'product_id');
		$xmlString .= '	<Area>';	
		switch($noOfSides)
		{
			case 4:
				$xmlString .= '	<frontArea>'.$configAreaData->getFaX().','.$configAreaData->getFaY().','.$configAreaData->getFaWidth().','.$configAreaData->getFaHeight().'</frontArea>';		
				$xmlString .= '	<backArea>'.$configAreaData->getBaX().','.$configAreaData->getBaY().','.$configAreaData->getBaWidth().','.$configAreaData->getBaHeight().'</backArea>';
				$xmlString .= '	<leftArea>'.$configAreaData->getLeX().','.$configAreaData->getLeY().','.$configAreaData->getLeWidth().','.$configAreaData->getLeHeight().'</leftArea>';		
				$xmlString .= '	<rightArea>'.$configAreaData->getRiX().','.$configAreaData->getRiY().','.$configAreaData->getRiWidth().','.$configAreaData->getRiHeight().'</rightArea>';			
			break;
			case 3:
				$xmlString .= '	<frontArea>'.$configAreaData->getFaX().','.$configAreaData->getFaY().','.$configAreaData->getFaWidth().','.$configAreaData->getFaHeight().'</frontArea>';		
				$xmlString .= '	<backArea>'.$configAreaData->getBaX().','.$configAreaData->getBaY().','.$configAreaData->getBaWidth().','.$configAreaData->getBaHeight().'</backArea>';
				if($configLeftImage != '')
					$xmlString .= '	<leftArea>'.$configAreaData->getLeX().','.$configAreaData->getLeY().','.$configAreaData->getLeWidth().','.$configAreaData->getLeHeight().'</leftArea>';	
				else
					$xmlString .= '	<rightArea>'.$configAreaData->getRiX().','.$configAreaData->getRiY().','.$configAreaData->getRiWidth().','.$configAreaData->getRiHeight().'</rightArea>';		
			break;
			case 2:
				default: 
				$xmlString .= '	<frontArea>'.$configAreaData->getFaX().','.$configAreaData->getFaY().','.$configAreaData->getFaWidth().','.$configAreaData->getFaHeight().'</frontArea>';		
				$xmlString .= '	<backArea>'.$configAreaData->getBaX().','.$configAreaData->getBaY().','.$configAreaData->getBaWidth().','.$configAreaData->getBaHeight().'</backArea>';
			break;			
		}	
		$xmlString .= '	</Area>';
		/* echo "<pre>";
		print_r($configProduct->getData()); */
		$configurableProduct = Mage::getModel('catalog/product_type_configurable')->setProduct($configProduct);
		$configProduct->getTypeInstance()->setStoreFilter($configProduct->getStore());
		$productAttributeOptions = $configProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($configProduct);

		$attributeOptions = array();
		$isColorAttribute = false;
		$isSizeAttribute = false;
		foreach ($productAttributeOptions as $productAttribute) {
			if($productAttribute['attribute_code'] == 'color'):
				$isColorAttribute = true;
			endif;

			if($productAttribute['attribute_code'] == 'size'):
				$isSizeAttribute = true;
			endif;
			
			foreach ($productAttribute['values'] as $attribute) {			
				//$attributeOptions[] = $attribute;
				$attributeOptions[$attribute['value_index']] = $attribute;
			}
		}

		/*get associate product collection with status enabled products*/
		$childProductCollection = $configurableProduct->getUsedProductCollection()
							->addAttributeToSelect('*')							
							->addFilterByRequiredOptions();	
		if($user=='')
		{
			$childProductCollection->addAttributeToFilter('status', array('eq' => 1));
		}
		//Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($childProductCollection);						
		/*filter associate products collection by "in stock" product*/		
		if($user=='')
		{
			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($childProductCollection);
		}
		/*get All product ids of associate products*/
		$childProdIds = $childProductCollection->getAllIds();	

		//if($isColorAttribute == 1) {
		/*Get product collection by color attribute*/
		$associateProductCollection = Mage::getModel('catalog/product')->getCollection()
				->AddAttributeToSelect('*')
				->addAttributeToFilter('type_id','simple')				
				->AddFieldToFilter('entity_id',$childProdIds)
				->addOrder('entity_id','ASC');
				if($user=='')
				{
					$associateProductCollection->AddFieldToFilter('status', 1);
				}
				if($isMulticolor == '1')
				{
					$associateProductCollection->addAttributeToFilter('front_image',array('notnull'=>'','neq'=>'no_selection'));
				}
		$associateProductCollection->groupbyAttribute('color');				
		/* echo "<pre>";		
		print_r($childProdIds);		
		print_r($associateProductCollection->getData());
		die; */
		//Color XML start
		$xmlString .= '	<allColors>';
		foreach($associateProductCollection as $associateProduct){		
		$xmlString .= '	<color>';
		//echo "<br />";
		$productModel = Mage::getModel('catalog/product');
		$colorAttribute = $productModel->getResource()->getAttribute("color");
		$colorId = $associateProduct->getColor();
			if ($colorAttribute->usesSource()) {
				$colorLabel = $colorAttribute->getSource()->getOptionText($colorId);
				$colorName = explode('(', $colorLabel);
				$colorText = $colorName[0];					
				$colorTemp = array_reverse($colorName);
				$colorName = explode(')', $colorTemp[0]);
			}
			$xmlString .= '	<optionID>'.$colorId.'</optionID>';
			if($isMulticolor==1)
			{
				$colorImage = $associateProduct->getColorImage();
				if($colorImage == 'no_selection' or $colorImage== '')
					$colorImage = '';
				else
					$colorImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($colorImage);
					
				$frontColorImage = $associateProduct->getFrontImage();
				if($frontColorImage == 'no_selection' or $frontColorImage== '')
					$frontColorImage = '';
				else
					$frontColorImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($frontColorImage);
					
				$backColorImage = $associateProduct->getBackImage();
				if($backColorImage == 'no_selection' or $backColorImage== '')
					$backColorImage = '';
				else
					$backColorImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($backColorImage);
				
				$leftColorImage = $associateProduct->getLeftImage();
				if($leftColorImage == 'no_selection' or $leftColorImage== '')
					$leftColorImage = '';
				else
					$leftColorImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($leftColorImage);
					
				$rightColorImage = $associateProduct->getRightImage();
				if($rightColorImage == 'no_selection' or $rightColorImage== '')
					$rightColorImage = '';
				else
					$rightColorImage = Mage::getModel('catalog/product_media_config')->getMediaUrl($rightColorImage);
					
				$xmlString .= '	<colorimage>'.$colorImage.'</colorimage>';
				$xmlString .= '	<image>';
				$xmlString .= '	<frontImage>'.$frontColorImage.'</frontImage>';			
				$xmlString .= '	<backImage>'.$backColorImage.'</backImage>';
				$xmlString .= '	<lefImage>'.$leftColorImage.'</lefImage>';
				$xmlString .= '	<rightImage>'.$rightColorImage.'</rightImage>';			
				$xmlString .= '	</image>';			
			}
			else
			{		
				// $xmlString .= ' <optionName>'.$colorName[0].'</optionName>'; 
				$_collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
				->setStoreFilter(0)
				->load();

				foreach( $_collection->toOptionArray() as $_cur_option ) {     
					if ($_cur_option['value'] == $colorId)
					{ 
						$colorName = explode('(', $_cur_option['label']);
						$colorText = $colorName[0];     
						$colorTemp = array_reverse($colorName);
						$colorName = explode(')', $colorTemp[0]);
						$xmlString .= ' <optionName>'.$colorName[0].'</optionName>'; 
					}      
				}			
			}
		$xmlString .= '	<priceModifier>0</priceModifier>';
		$xmlString .= '	<colorName>'.$colorText.'</colorName>';
		/* 
		echo $attributeOptions[$colorId]['is_percent'];
		echo "<br />";
		echo $attributeOptions[$colorId]['pricing_value'];
		echo "<br />"; */		
			
		//if($isSizeAttribute == 1) {
			$sizeProductCollection = Mage::getModel('catalog/product')->getCollection()
						->AddAttributeToSelect('*')
						->addAttributeToFilter('type_id','simple')						
						->addAttributeToFilter('color',$colorId)
						->AddFieldToFilter('entity_id',$childProdIds)
						->groupbyAttribute('size');	
			if($user=='')
				{
					$sizeProductCollection->AddFieldToFilter('status', 1);
				}
			$xmlString .= '	<sizes>';
			foreach($sizeProductCollection as $sizeProduct){
				$child = Mage::getModel('catalog/product')->load($sizeProduct->getId());
				$availableQty = $child->getStockItem()->getQty();	
				$minSaleQty = $child->getStockItem()->getMinSaleQty();				
				$isConfigSetting = $child->getStockItem()->getUseConfigMaxSaleQty();
				$maxSaleQty = $child->getStockItem()->getMaxSaleQty();
				$minQty = min($availableQty,$maxSaleQty);
				$sizeAttribute = $productModel->getResource()->getAttribute("size");
				$sizeId = $sizeProduct->getSize();
				$child = Mage::getModel('catalog/product')->load($sizeProduct->getId());    
				$isManageStock = $child->getStockItem()->getManageStock();
				if ($sizeAttribute->usesSource()) {
					$sizeLabel = $sizeAttribute->getSource()->getOptionText($sizeId);
				}
				
				$xmlString .= '	<size>';
				$xmlString .= '	<productID>'.$sizeProduct->getId().'</productID>';
				$xmlString .= ' <isManageStock>'.$isManageStock.'</isManageStock>';
				if($isManageStock == 0)
				{
					$xmlString .= '	<minQty></minQty>';
					$xmlString .= '	<maxQty></maxQty>';
				}
				else
				{
					$xmlString .= '	<minQty>'.$minSaleQty.'</minQty>';
					$xmlString .= '	<maxQty>'.$minQty.'</maxQty>';
				}
				$xmlString .= '	<optionID>'.$sizeId.'</optionID>';
				$xmlString .= '	<optionName>'.$sizeLabel.'</optionName>';
				$xmlString .= '	<priceModifier>0</priceModifier>';
				$xmlString .= '	</size>';			
				//echo $attributeOptions[$sizeId]['pricing_value'];
				//echo "<br />";
						
			}	
			$xmlString .= '	</sizes>';
		//}
		$xmlString .= '	</color>';
		}
		//}

		$xmlString .= '	</allColors>';
		$xmlString .= '	</product>';
		$xmlString .= '</allProducts>';
		return $xmlString;
	}	
	
	public function renderChildrenCategory($catId)
	{		
		$children = Mage::getModel('catalog/category')->load($catId)->getChildren();
		
		if(!empty($children)) {
			$categories = explode(',', $children);
		
			foreach($categories as $categoryId) {
				$category = Mage::getModel('catalog/category')->load($categoryId);
				//echo '<category label="'.$category->getName().'" catName="'.$category->getName().'" orderNo="'.$category->getPosition().'" type="subcategory" catID="'.$category->getId().'" >';
				$xmlString .= '<category>';
				$xmlString .= '<catName>'.$category->getName().'</catName>';
				$xmlString .=  '<catID>'.$category->getId().'</catID>';
				$xmlString .=  '<orderNo>'.$category->getPosition().'</orderNo>';
				$xmlString .=  '<catDesc>'.$category->getDescription().'</catDesc>';		
				$categoryImage = Mage::getModel("catalog/category")->load($category->getId())->getImage();
				if( $categoryImage!='')
					$xmlString .=  '<catThumb>'.$path.'/media/catalog/category/'. $categoryImage.'</catThumb>';
					
				$xmlString .=  '<type>'.'subcategory'.'</type>';
				$this->renderChildrenCategory($categoryId);
				$xmlString .= "</category>";
			}
		
		}		
		return $xmlString;
	}
} ?>