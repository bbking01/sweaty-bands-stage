<?php
require '../app/Mage.php';		
Mage::app();
$storeId = Mage::app()->getStore()->getStoreId();

/* echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
echo "<br />";
echo Mage::getBaseUrl();
echo "<br />";
echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
echo "<br />";
echo Mage::getUrl('',array('_secure'=>true));
die; */
//Attibute set Id
$designtoolAttributeSetId = 9;
$categoryId = 3;
$admin = 0;

// Load Category by category id.
$catagory = Mage::getModel('catalog/category')->load($categoryId);

$productCollection = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToFilter('entity_id', 125)
					->AddFieldToFilter('is_customizable', 1)
					->AddFieldToFilter('status', 1)
					->addAttributeToFilter('type_id', 'configurable')
					->addAttributeToFilter('attribute_set_id',$designtoolAttributeSetId)					
					->addAttributeToSelect('*');
echo "<pre>";
print_r($productCollection->getData());
die;

// Get product collection by category id.
$productCollection = Mage::getModel('catalog/product')
					->getCollection()
					->addCategoryFilter($catagory) //category filter
					->AddFieldToFilter('is_customizable', 1)
					->AddFieldToFilter('status', 1)
					->addAttributeToFilter('type_id', 'configurable')
					->addAttributeToFilter('attribute_set_id',$designtoolAttributeSetId)					
					->addAttributeToSelect('*');
Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection);		
echo "<pre>";
print_r($productCollection->getData());
echo "ajay";
$productCollection->getFirstItem();			
foreach($productCollection as $product)
{
	if($product->getis_salable() && $product->getStockItem()->getis_in_stock())
	{
		echo "<pre>";
		print_r($productCollection->getData());
	}
}					
die; 
$configProductId = 75;

$xml_data = Mage::getModel('catalog/product')->load(configProductId);
echo Mage::helper('checkout/cart')->getAddUrl($xml_data);
die;
$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><allProducts>';
$xmlString .= '	<product>';
$configProduct = Mage::getModel('catalog/product')->load($configProductId);

$isMulticolor = $configProduct->getMulticolor();
$xmlString .= '	<name>'.$configProduct->getName().'</name>';
$xmlString .= '	<productID>'.$configProduct->getEntityId().'</productID>';
$xmlString .= '	<code>'.$configProduct->getSku().'</code>';
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
$xmlString .= '	</image></productImages>';

$xmlString .= '	<Area>';	
switch($noOfSides)
{
	case 4:
		$xmlString .= '	<frontArea>'.$configProduct->getFaX().','.$configProduct->getFaY().','.$configProduct->getFaWidth().','.$configProduct->getFaHeight().'</frontArea>';		
		$xmlString .= '	<backArea>'.$configProduct->getBaX().','.$configProduct->getBaY().','.$configProduct->getBaWidth().','.$configProduct->getBaHeight().'</backArea>';
		$xmlString .= '	<leftArea>'.$configProduct->getLeX().','.$configProduct->getLeY().','.$configProduct->getLeWidth().','.$configProduct->getLeHeight().'</leftArea>';		
		$xmlString .= '	<rightArea>'.$configProduct->getRiX().','.$configProduct->getRiY().','.$configProduct->getRiWidth().','.$configProduct->getRiHeight().'</rightArea>';			
		break;
	case 3:
		$xmlString .= '	<frontArea>'.$configProduct->getFaX().','.$configProduct->getFaY().','.$configProduct->getFaWidth().','.$configProduct->getFaHeight().'</frontArea>';		
		$xmlString .= '	<backArea>'.$configProduct->getBaX().','.$configProduct->getBaY().','.$configProduct->getBaWidth().','.$configProduct->getBaHeight().'</backArea>';
		if($left_image != '')
			$xmlString .= '	<leftArea>'.$configProduct->getLeX().','.$configProduct->getLeY().','.$configProduct->getLeWidth().','.$configProduct->getLeHeight().'</leftArea>';	
		else
			$xmlString .= '	<rightArea>'.$configProduct->getRiX().','.$configProduct->getRiY().','.$configProduct->getRiWidth().','.$configProduct->getRiHeight().'</rightArea>';		
		break;
	case 2:
	default: 
		$xmlString .= '	<frontArea>'.$configProduct->getFaX().','.$configProduct->getFaY().','.$configProduct->getFaWidth().','.$configProduct->getFaHeight().'</frontArea>';		
		$xmlString .= '	<backArea>'.$configProduct->getBaX().','.$configProduct->getBaY().','.$configProduct->getBaWidth().','.$configProduct->getBaHeight().'</backArea>';
		break;			
}	
	$xmlString .= '	</Area>';

$configurableProduct = Mage::getModel('catalog/product_type_configurable')->setProduct($configProduct);
$configProduct->getTypeInstance()->setStoreFilter($configProduct->getStore());
$productAttributeOptions = $configProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($configProduct);

$attributeOptions = array();
$isColorAttribute = false;
$isSizeAttribute = false;
foreach ($productAttributeOptions as $productAttribute) {	
	//echo "<pre>";
	//print_r($productAttribute);
	if($productAttribute['attribute_code'] == 'color'):
		$isColorAttribute = true;
	endif;
	
	if($productAttribute['attribute_code'] == 'size'):
		$isSizeAttribute = true;
	endif;
	
	//echo "ajay ".$isColorAttribute;
	foreach ($productAttribute['values'] as $attribute) {			
		//$attributeOptions[] = $attribute;
		$attributeOptions[$attribute['value_index']] = $attribute;
	}
}
	
/*get associate product collection with status enabled products*/
$childProductCollection = $configurableProduct->getUsedProductCollection()
							->addAttributeToSelect('*')
							->AddFieldToFilter('status', 1)
							->addFilterByRequiredOptions();	
								
/* echo "<pre>";
print_r($childProductCollection->getData());		
die;		 */				
/*filter associate products collection by "in stock" product*/						
Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($childProductCollection);

/*get All product ids of associate products*/
$childProdIds = $childProductCollection->getAllIds();	

//if($isColorAttribute == 1) {
/*Get product collection by color attribute*/
$associateProductCollection = Mage::getModel('catalog/product')->getCollection()
                ->AddAttributeToSelect('*')
                ->addAttributeToFilter('type_id','simple')
				->AddFieldToFilter('status', 1)
                ->AddFieldToFilter('entity_id',$childProdIds)
				//->addAttributeToFilter('front_image',array('notnull'=>'','neq'=>'no_selection'))
                ->groupbyAttribute('color');				
				
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
			$xmlString .= '	<optionName>'.$colorName[0].'</optionName>';			
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
						->AddFieldToFilter('status', 1)
						->addAttributeToFilter('color',$colorId)
						->AddFieldToFilter('entity_id',$childProdIds)
						->groupbyAttribute('size');	
			
			$xmlString .= '	<sizes>';
			foreach($sizeProductCollection as $sizeProduct){
				$child = Mage::getModel('catalog/product')->load($sizeProduct->getId());
				$availableQty = $child->getStockItem()->getQty();				
				$minSaleQty = $child->getStockItem()->getMinSaleQty();				
				$isConfigSetting = $child->getStockItem()->getUseConfigMaxSaleQty();
				$maxSaleQty = $child->getStockItem()->getMaxSaleQty();		
				echo "<br />";
				echo "availabel:-".$availableQty;
				echo "<br />";
				echo "max:-".$maxSaleQty;
				echo "<br />";
				$minQty = min($availableQty,$maxSaleQty);
				echo "min:-".$minQty;
				$sizeAttribute = $productModel->getResource()->getAttribute("size");
				$sizeId = $sizeProduct->getSize();
				if ($sizeAttribute->usesSource()) {
					$sizeLabel = $sizeAttribute->getSource()->getOptionText($sizeId);
				}
				$xmlString .= '	<size>';
				$xmlString .= '	<productId>'.$sizeProduct->getId().'</productId>';
				$xmlString .= '	<minQty>'.$minSaleQty.'</minQty>';
				$xmlString .= '	<maxQty>'.$minQty.'</maxQty>';
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
die;
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
//echo $xmlString;
?>