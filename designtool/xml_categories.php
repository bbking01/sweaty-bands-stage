<?php
require '../app/Mage.php';
Mage::app();

function renderChildren($category) {

	$children = Mage::getModel('catalog/category')->load($category)->getChildren();
	if(!empty($children)) {
		$categories = explode(',', $children);
	
		foreach($categories as $categoryId) {
			$category = Mage::getModel('catalog/category')->load($categoryId);
			//echo '<category label="'.$category->getName().'" catName="'.$category->getName().'" orderNo="'.$category->getPosition().'" type="subcategory" catID="'.$category->getId().'" >';
			echo '<category>';
			echo '<catName>'.$category->getName().'</catName>';
			echo  '<catID>'.$category->getId().'</catID>';
			echo  '<orderNo>'.$category->getPosition().'</orderNo>';
			echo  '<catDesc>'.$category->getDescription().'</catDesc>';		
			$categoryImage = Mage::getModel("catalog/category")->load($category->getId())->getImage();
			if( $categoryImage!='')
				echo  '<catThumb>'.$path.'/media/catalog/category/'. $categoryImage.'</catThumb>';
				
			echo  '<type>'.'subcategory'.'</type>';
			renderChildren($categoryId);
			echo "</category>";
		}
	
	}

}

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo '<?xml version="1.0" encoding="iso-8859-1"?>';
echo '<allCategory>';
renderChildren(Mage::app()->getStore()->getRootCategoryId());
echo "</allCategory>";
?>