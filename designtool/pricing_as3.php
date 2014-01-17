<?php	
require '../app/Mage.php';
Mage::app();
Mage::getSingleton("core/session", array("name" => "frontend"));
$session = Mage::getSingleton('customer/session');
$flashData =$_POST['flashvars'];
if(isset($flashData)){
	$flashvars = $flashData;
	$xml = simplexml_load_string($flashvars);
		
	$totalQty = $xml->total_qty;	
	$colorId = $xml->colorid;
	$usedColorCounter = 0;
	$usedColorCounter = $xml->noofcolor->front + $xml->noofcolor->back + $xml->noofcolor->left + $xml->noofcolor->right;
	$productModel = Mage::getModel('catalog/product');
	$colorAttribute = $productModel->getResource()->getAttribute("color");
	if ($colorAttribute->usesSource()) {
			$colorLabel = $colorAttribute->getSource()->getOptionText($colorId);
			$colorName = explode('(', $colorLabel);			
			$colorName = $colorName[0];
	}
	
	$product = Mage::getModel('catalog/product')->load($xml->prodid);
	$configurableProduct = Mage::getModel('catalog/product_type_configurable')->setProduct($product);	
	//$product->setCustomerGroupId(0);
	
	$size = $product->getResource()->getAttribute('size');
	$size_id = $size->getAttributeId();
	$finalPrice = 0;
	$total_cost = 0.0;
	$qcPrice = 0;
	$totalQcPrice = 0;
	$namePrice = 0;
	$numberPrice = 0;
	$i = 0;	
	
	foreach($xml->sizes->size as $size)
	{			
		$qty = $size->quantity;
		$productId = $size->productid;		
		
		$sizeId = $size->optionid;
		$sizeAttribute = $productModel->getResource()->getAttribute("size");
		
		if ($sizeAttribute->usesSource()) {
			$sizeName = $sizeAttribute->getSource()->getOptionText($sizeId);
		}
		$child = Mage::getModel('catalog/product')->load($productId);
		
		$availableQty = $child->getStockItem()->getQty();
		//echo "available qty".$availableQty."<br />";
		$minSaleQty = $child->getStockItem()->getMinSaleQty();
		//echo "min qty".$minSaleQty."<br />";
		$maxSaleQty = $child->getStockItem()->getMaxSaleQty();
		$isConfigSetting = $child->getStockItem()->getUseConfigMaxSaleQty();
		//echo $isConfigSetting."<br />";		
		if($isConfigSetting==0)
		{
			if($qty > $maxSaleQty)
			{
				$message = 'The maximum quantity allowed for Color '.$colorName.' and Size '.$sizeName.' for purchase is '.$maxSaleQty;				
			}
		}
		if($qty > $availableQty)
		{
			$message = 'Quantity '.$qty.' is not available for Color '.$colorName.' and Size '.$sizeName;			
		}
		
		if($qty < $minSaleQty)
		{
			$message = "Minimum sale quantity for Color ".$colorName." and Size ".$sizeName." is ".$minSaleQty;
		}	
		
		$finalPrice = Mage::getModel('design/design')->getFinalPrice($qty,$product,$colorId,$sizeId);

		$finalPrice = $finalPrice * $qty;		
		$total_cost = $total_cost + $finalPrice;	
		
		$qcPrice = Mage::getModel('qcprice/qcprice')->getQcPrice($usedColorCounter,$qty);				
		$totalQcPrice = $totalQcPrice + ($qcPrice*$qty);

		
		// if($namesCount>0)
		// {
			// $namePrice = $namePrice + $namesCount;
		// }
		// if($numbersCount>0)
		// {
			// $numberPrice = $numberPrice + $numbersCount;
		// }	
	}	
	/*For Name Number Price*/
		$nameFixPrice =(float) Mage::getStoreConfig('designtool_options/name_number_price/name_price');	
		$numberFixPrice = (float)Mage::getStoreConfig('designtool_options/name_number_price/number_price');
		$namesCount = $xml->names;	
		$numbersCount = $xml->numbers;	
	$total_cost = $total_cost + $totalQcPrice + $namesCount*$nameFixPrice + $numbersCount*$numberFixPrice;
	echo "ttotal=".Mage::helper('core')->currency($total_cost,true,false);
	echo "&printingprice=".Mage::helper('core')->currency($totalQcPrice,true,false);
	echo "&totalquantity=".$xml->total_qty;
	echo "&totalusedcolors=".$usedColorCounter;
	echo "&namePrice=".$namePrice;
	echo "&numberPrice=".$numberPrice;
	if($xml->total_qty == 0)
		echo "&eachshirt=0";
	else	
		echo "&eachshirt=".Mage::helper('core')->currency($total_cost/$xml->total_qty,true,false);
	echo "&msg=".$message;
	
}
?>