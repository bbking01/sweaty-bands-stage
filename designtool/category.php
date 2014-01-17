<?php		
require '../app/Mage.php';	
Mage::app("default");
$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
$designtoolAttributeSetId = 9;
$_category  = Mage::app()->getStore()->getRootCategoryId();
$collection = Mage::getModel('catalog/category')->getCategories($_category);
$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><allCategory>';

foreach($collection as $res)
{
    $category = Mage::getModel("catalog/category")->load($res->getId());
    
    $productCollection = Mage::getModel('catalog/product')
					->getCollection()
					->addCategoryFilter($category) //category filter
					->AddFieldToFilter('is_customizable', 1)
					->AddFieldToFilter('status', 1)
					->addAttributeToFilter('type_id', 'configurable')
					->addAttributeToFilter('attribute_set_id',$designtoolAttributeSetId)					
					->addAttributeToSelect('*');
    Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection);	
    
    if(count($productCollection->getData())>0)
    {
        $categoryImage = Mage::getModel("catalog/category")->load($res->getId())->getImage();
        $xmlString .= '	<category>';
        $xmlString .= '	<catName>'.$res->getName().'</catName>';
        $xmlString .= '	<catID>'.$res->getId().'</catID>';
        $xmlString .= '	<orderNo>'.$res->getPosition().'</orderNo>';
        $xmlString .= '	<catDesc>'.$res->getDescription().'</catDesc>';		
        if( $categoryImage!='')
                $xmlString .= '	<catThumb>'.$path.'/media/catalog/category/'. $categoryImage.'</catThumb>';			

        $xmlString .= '	<type>'.'subcategory'.'</type>';	
        $xmlString .= '	</category>';	
    }
}

$xmlString .= '</allCategory>';
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );

echo $xmlString;



?>