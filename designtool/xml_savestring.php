<?php
require '../app/Mage.php';
Mage::app();

if(isset($_REQUEST['design_id']) && $_REQUEST['design_id'] != '')
{	
	$design_id = $_REQUEST['design_id'];
	$saveDesign  = Mage::getModel('design/savedesign')->load($design_id);	
	$designstring = $saveDesign->getSaveString();	
	echo '&savestring='.$designstring.'&';
	
}else if(isset($_REQUEST['order_id']) && $_REQUEST['order_id'] != '')
{
	$order_id = $_REQUEST['order_id'];
	$orderItem = Mage::getModel('sales/order_item')->load($order_id);		
	$order = $orderItem->getProductOptions();

	$dataxml = simplexml_load_string($order['info_buyRequest']['dataxml']);	
	$data = objectsIntoArray($dataxml);
	echo '&savestring='.$data['savestr'].'&';
	
}else if(isset($_REQUEST['template_id']) && $_REQUEST['template_id'] != '')
{
	$template_id = $_REQUEST['template_id'];
	$designIdea  = Mage::getModel('gallery/gallery')->load($template_id);   
	$templatestring = $designIdea->getDesigndata();	 
	echo '&savestring='.$templatestring.'&';
	
}else if(isset($_REQUEST['cart_id']) && $_REQUEST['cart_id'] != '')
{
	$cart_id = $_REQUEST['cart_id'];
	$item = Mage::getModel('sales/quote_item')->load($cart_id);	
	$cartstring = $item->getSavestr();		
	echo '&savestring='.$cartstring.'&';
}

function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
	$arrData = array();
	// if input is object, convert into array
	if (is_object($arrObjData)) {
		$arrObjData = get_object_vars($arrObjData);
	}
	
	if (is_array($arrObjData)) {
		foreach ($arrObjData as $index => $value) {
			if (is_object($value) || is_array($value)) {
				$value = objectsIntoArray($value, $arrSkipIndices); // recursive call
			}
			if (in_array($index, $arrSkipIndices)) {
				continue;
			}
			$arrData[$index] = $value;
		}
	}
	return $arrData;
}

?>
