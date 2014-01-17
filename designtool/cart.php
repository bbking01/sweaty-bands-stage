<?php	
require '../app/Mage.php';
Mage::app();
$resource = Mage::getSingleton('core/resource');
$read = $resource->getConnection('core_read');

$cartid = $_POST['cart_id'];

if(isset($_POST['cart_id']))
{
	$cartid = $_POST['cart_id'];
	if ($cartid) {
		try {		
			Mage::getSingleton('checkout/cart')->removeItem($cartid)->save();
		} catch (Exception $e) {
			
			Mage::getSingleton('checkout/session')->addError($this->__('Cannot remove the item.'));
		}
	}	
}

if(isset($_POST['flashvars'])){

	$flashvars = $_POST['flashvars'];	
	
	$xml = simplexml_load_string($flashvars); 
	
	$configProductId = $xml->prodid;
	$configProduct = Mage::getModel('catalog/product')->load($configProductId);
	
	$colorOptionId = $xml->clroptionid;
	
	$productModel = Mage::getModel('catalog/product');
	
	$colorAttribute = $productModel->getResource()->getAttribute("color");
	$colorId = $colorAttribute->getAttributeId();
	
	$sizeAttribute = $productModel->getResource()->getAttribute("size");
	$sizeId = $sizeAttribute->getAttributeId();
	
	
	$carturl = array();
	
	$i = 0;
	foreach($xml->sizes->size as $size)
	{
		$sizeOptionId = $size->optionid;		

		$configurableProduct = Mage::getModel('catalog/product_type_configurable')->setProduct($configProduct);

		/*get associate product collection with status enabled products*/
		$childProduct = $configurableProduct->getUsedProductCollection()
									->addAttributeToSelect('*')
									->AddFieldToFilter('status', 1)
									->addAttributeToFilter('color',$colorOptionId)
									->addAttributeToFilter('size',$sizeOptionId)
									->addFilterByRequiredOptions();			
		
		if($childProduct->count()>=1)
		{
					 			
			$xml_data = Mage::getModel('catalog/product')->load($xml->prodid);								 			
			if($_POST['cart_id'])
			{
				$carturl[$i] = str_replace('cart.php', 'index.php', Mage::helper('checkout/cart')->getAddUrl($xml_data)).'#**#'.$size->quantity.'#**#'.$colorId.'#**#'.$xml->clroptionid.'#**#'.$sizeId.'#**#'.$size->optionid.'#**#'.$_POST['cart_id'];
			}
			else
			{							 			
					$carturl[$i] = str_replace('cart.php', 'index.php', Mage::helper('checkout/cart')->getAddUrl($xml_data)).'#**#'.$size->quantity.'#**#'.$colorId.'#**#'.$xml->clroptionid.'#**#'.$sizeId.'#**#'.$size->optionid;								
			}						
			$i++;
		}
	}
	
	echo '&carturl='.implode('|',$carturl); 
} ?>
