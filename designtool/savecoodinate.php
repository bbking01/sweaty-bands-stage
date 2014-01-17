<?php	
require '../app/Mage.php';		
Mage::app();

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$table = $resource->getTableName('catalog_product_entity');
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
$productId = $_POST['prodId'];
$coordinate = $_POST['coordinate'];
$noofside = $_POST['noofside'];
 

$xml1 = simplexml_load_string($coordinate); 
$xml = simplexml_load_string($coordinate);
$xml1 = objectsIntoArray($xml);	
$total = array();
$total = $xml1[IMAGES][IMAGE];

$cnt = count($total);

	if($noofside == 1)
	{
		$data['product_id'] = $productId;
		$data['fa_height'] = $total[COORDINATE][H];
		$data['fa_width'] = $total[COORDINATE][W];
		$data['fa_x'] = $total[COORDINATE][X];
		$data['fa_y'] = $total[COORDINATE][Y];
		
	}else if($noofside == 2 )
	{
	
		$data['product_id'] = $productId;
		$data['fa_height'] = $total[0][COORDINATE][H];
		$data['fa_width'] = $total[0][COORDINATE][W];
		$data['fa_x'] = $total[0][COORDINATE][X];
		$data['fa_y'] = $total[0][COORDINATE][Y];

		$data['ba_height'] = $total[1][COORDINATE][H];
		$data['ba_width'] = $total[1][COORDINATE][W];
		$data['ba_x'] = $total[1][COORDINATE][X];
		$data['ba_y'] = $total[1][COORDINATE][Y];
		
	}else if($noofside == 3 )
	{
		$data['product_id'] = $productId;
		$data['fa_height'] = $total[0][COORDINATE][H];
		$data['fa_width'] = $total[0][COORDINATE][W];
		$data['fa_x'] = $total[0][COORDINATE][X];
		$data['fa_y'] = $total[0][COORDINATE][Y];

		$data['ba_height'] = $total[1][COORDINATE][H];
		$data['ba_width'] = $total[1][COORDINATE][W];
		$data['ba_x'] = $total[1][COORDINATE][X];
		$data['ba_y'] = $total[1][COORDINATE][Y];

		$data['le_height'] = $total[2][COORDINATE][H];
		$data['le_width'] = $total[2][COORDINATE][W];
		$data['le_x'] = $total[2][COORDINATE][X];
		$data['le_y'] = $total[2][COORDINATE][Y];
		
	}else if($noofside == 4 )
	{

		$data['product_id'] = $productId;
		$data['fa_height'] = $total[0][COORDINATE][H];
		$data['fa_width'] = $total[0][COORDINATE][W];
		$data['fa_x'] = $total[0][COORDINATE][X];
		$data['fa_y'] = $total[0][COORDINATE][Y];

		$data['ba_height'] = $total[1][COORDINATE][H];
		$data['ba_width'] = $total[1][COORDINATE][W];
		$data['ba_x'] = $total[1][COORDINATE][X];
		$data['ba_y'] = $total[1][COORDINATE][Y];

		$data['le_height'] = $total[2][COORDINATE][H];
		$data['le_width'] = $total[2][COORDINATE][W];
		$data['le_x'] = $total[2][COORDINATE][X];
		$data['le_y'] = $total[2][COORDINATE][Y];

		$data['ri_height'] = $total[3][COORDINATE][H];
		$data['ri_width'] = $total[3][COORDINATE][W];
		$data['ri_x'] = $total[3][COORDINATE][X];
		$data['ri_y'] = $total[3][COORDINATE][Y];
	}
	
$configData = Mage::getModel('design/configarea')->load($productId,'product_id');

if(count($configData->getData())>0)
{
	$model = Mage::getModel('design/configarea');
	$model->setData($data);
	$model->setId($configData->getId());
	
	$model->save();
}
else
{
	$model = Mage::getModel('design/configarea');		
	$model->setData($data);
	$model->save();
}
exit;
?>
