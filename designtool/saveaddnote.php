<?php	
require '../app/Mage.php';		
Mage::app();

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$table = $resource->getTableName('catalog_product_entity_text');

 $add_note_attr = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product','addnote');

$productId = $_POST['prodid'];
$addnote = $_POST['addnote'];

$addnote = "WELCOME TO RWS";
$productId = '1';
/*echo '<pre>';
print_r($coordinate );
echo '</pre>';
exit;*/
$xml1 = simplexml_load_string($coordinate); 
$xml = simplexml_load_string($coordinate);
		
	echo $query = "UPDATE {$table} SET value = '{$addnote}' WHERE entity_id = ".$productId." and attribute_id = ".$add_note_attr;
	echo "<br>";
	    $writeConnection->query($query);	
exit;
?>
