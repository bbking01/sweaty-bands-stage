<?php			
$disigntoolattributeset = 9;  ////////// Attribute set id //////////////////////////////////////////

require '../app/Mage.php';		
Mage::app();

$current_store = Mage::app()->getStore()->getCode();
$current_storeid =  Mage::app()->getStore()->getStoreId();

 //For Default English Store
 if($current_store == "default")
 {
 	Mage::app("default");
	
 }else if($current_store == "dutch") //For German Store
 {
 	Mage::app("dutch");
 }


$resource = Mage::getSingleton('core/resource');
$read = $resource->getConnection('core_read');

$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

if(isset($_GET['cid']))
{
	$cid = $_GET['cid'];			
	$filters = array(		
		'type' => 'configurable'
		);
}
else
{	
	$filters = array(
		'type' => 'configurable'
		);
}

$catids[] = $cid;
$parentId =$cid;
$tree = Mage::getResourceSingleton('catalog/category_tree')->load();
$root = $tree->getNodeById($parentId);

$collection = Mage::getModel('catalog/category')->getCollection()
->addAttributeToSelect('name')
->addAttributeToSelect('is_active');

$tree->addCollectionData($collection, true); 

foreach($root->getChildren() as $child)
{
	 
	if($child->getIs_active())
		$catids[] = $child->getId();
}

// Load Category by category id.
$catagory_model = Mage::getModel('catalog/category')->load($cid);

// Get product collection by category id.
$products = Mage::getResourceModel('catalog/product_collection')
				->addCategoryFilter($catagory_model) //category filter
				->AddFieldToFilter('is_customizable', 1)
				->AddFieldToFilter('status', 1)
				->addAttributeToFilter('type_id', 'configurable')
				->addAttributeToFilter('attribute_set_id',$disigntoolattributeset)
				->addAttributeToSelect('*');

/*$products = Mage::getModel('catalog/product')->getcollection()->addAttributeToFilter('type_id','configurable')
										     ->addAttributeToFilter('attribute_set_id',$disigntoolattributeset)
											 ->AddFieldToFilter('is_customizable', 1)
											 ->AddFieldToFilter('status', 1)
											 ->addAttributeToFilter('category_ids',3);*/

/*New added by bhagyashri starts*/	
//$products->addCategoryFilter($category); 
/*New added by bhagyashri ends*/	
foreach($products as $ret)
{	
	if(array_intersect($catids,$ret->getCategoryIds()))
	{
		//Check stock item is in stock updated dt 
		if($ret->getStockItem()->getIsInStock() == "1")
		{
			$result_f[] = $ret->getData();
		}
	}
}

$result = $result_f;
$result_final = array();

foreach($result as $ret)
{
	/*echo '<pre>';
	print_r($ret);
	echo '</pre>';
	exit;*/

	$color = array();
	$size = array();
	$newsim = array();
	
	/*$filters = array(
		'sku' => array('like' => $ret['sku'].'-%')
	);*/
	$filters = '';
	$q = "SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product'";
	$data = $read->fetchAll($q);	
	$entity_type_id = $data[0]['entity_type_id'];				
	    $sql = "SELECT DISTINCT(eaov.value), cpei.`value` AS value_index
					FROM `eav_attribute` AS ea, 
					`eav_attribute_option` AS eao, 
					`eav_attribute_option_value` AS eaov, 
					`catalog_product_super_link` AS cpsl, 
					`catalog_product_entity_int` AS cpei
					WHERE ea.`attribute_id` = eao.`attribute_id`
					AND eao.`option_id` = eaov.`option_id`
					AND eaov.`option_id` = cpei.`value`
					AND ea.`attribute_id` = cpei.`attribute_id`
					AND cpsl.product_id = cpei.entity_id					
					AND ea.`entity_type_id` = ".$entity_type_id." 
					AND `attribute_code` = 'color'
					AND eaov.`store_id` = ".$current_storeid."  
					AND cpsl.parent_id = ".$ret['entity_id']."
					Order by  eao.sort_order" ; //Code updated for sort order of color value
					
	$data = $read->fetchAll($sql);	
	
	foreach( $data as $row )
	{	
		 $sql = "SELECT `pricing_value` 
					FROM `catalog_product_super_attribute_pricing` as cpsap, 
						`catalog_product_super_attribute` as cpsa 
					WHERE cpsap.`product_super_attribute_id` = cpsa.`product_super_attribute_id` 
						AND `value_index` = ".$row['value_index']." AND product_id = ".$ret['entity_id'];
						
		$data = $read->fetchAll($sql);			
		if($data[0]['pricing_value'] != '')					
			$row['pricing_value'] = $data[0]['pricing_value'];
		else
			$row['pricing_value'] = 0;
		$color[] = $row;
	}
	  $sql = "SELECT DISTINCT(eaov.value), cpei.`value` AS value_index
				FROM `eav_attribute` AS ea, 
					`eav_attribute_option` AS eao, 
					`eav_attribute_option_value` AS eaov, 
					`catalog_product_super_link` AS cpsl, 
					`catalog_product_entity_int` AS cpei
				WHERE ea.`attribute_id` = eao.`attribute_id`
					AND eao.`option_id` = eaov.`option_id`
					AND eaov.`option_id` = cpei.`value`
					AND ea.`attribute_id` = cpei.`attribute_id`
					AND cpsl.product_id = cpei.entity_id					
					AND ea.`entity_type_id` = ".$entity_type_id."
					AND `attribute_code` = 'size'
					AND cpsl.parent_id = ".$ret['entity_id']."
					Order by  eao.sort_order" ;
	$data = $read->fetchAll($sql);	
	 
/*echo '<pre>';
print_r($data);
echo '</pre>';
exit;*/
		
	foreach( $data as $row )
	{	
		 $sql = "SELECT `pricing_value` 
					FROM `catalog_product_super_attribute_pricing` as cpsap, 
						`catalog_product_super_attribute` as cpsa 
					WHERE cpsap.`product_super_attribute_id` = cpsa.`product_super_attribute_id` 
						AND `value_index` = ".$row['value_index']." AND product_id = ".$ret['entity_id'];
		
		$data = $read->fetchAll($sql);			
		if($data[0]['pricing_value'] != '')					
			$row['pricing_value'] = $data[0]['pricing_value'];
		else
			$row['pricing_value'] = 0;
		$size[] = $row;
	}
	 

	$configsize = array();
	$configsize = $size;

	/*Code added to check  X color available size in the simple product starts bhagyashri*/
		$sql_simple = "SELECT child_id FROM `catalog_product_relation` WHERE `parent_id` = 1";
		$res_simple = $read->fetchAll($sql_simple);
		$sim = array();
		foreach($res_simple as $simple)
		{
		 $sim[] = $simple['child_id'];
		 }
		$sim_array = implode(",", $sim);
		// This is to get the color attribute id 
		$sql = 'SELECT `sku` FROM '.Mage::getSingleton('core/resource')->getTableName('catalog_product_entity').' WHERE `entity_id` = '.$entity_type_id;
						  
	$prd_sku = $read->fetchAll($sql);
	$sku = $prd_sku[0]['sku'];
		
		$sql = 'SELECT `attribute_id` FROM '.Mage::getSingleton('core/resource')->getTableName('eav_attribute').' WHERE `entity_type_id` = '.$entity_type_id.' AND `attribute_code` = "color"';
		
		$color_id = $read->fetchAll($sql);
		$color_id = $color_id[0]['attribute_id'];
			
			// This is to get the size attribute id 
		  $sql = 'SELECT `attribute_id` FROM '.Mage::getSingleton('core/resource')->getTableName('eav_attribute').' WHERE `entity_type_id` = '.$entity_type_id.' AND `attribute_code` = "size"';
		
		$size_id = $read->fetchAll($sql);
		$size_id = $size_id[0]['attribute_id'];
		
			
		
		
	$ret['color'] = $color;	
	$ret['size'] = $size;		
	$result_final[] = $ret;	
	 

}
$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><allProducts>';
foreach($result_final as $res)		
{
	$product = Mage::getModel('catalog/product')->load($res['entity_id']);

	
	$res['status'] = $product->getStatus();
	
	
	$res['thumbnail'] =  $product->getThumbnail();
	$res['front_image'] = $product->getFront_image();
	$res['back_image'] = $product->getBack_image();
	$res['left_image'] = $product->getLeft_image();
	$res['right_image'] = $product->getRight_image();
	$multicolor = $product->getMulticolor();
		
	if($multicolor == '1')
		$multicolor = 'yes';
	else
		$multicolor = 'no';
	
	if($res['status'] == '1')
		$status = 'yes';
	else
		$status = 'no';
		
	if($res['thumbnail'] == 'no_selection')
		$thumbnail = '';
	else
		$thumbnail = $path.'media/catalog/product'.$res['thumbnail'];
		
	if($res['front_image'] == 'no_selection' or $res['front_image'] == '')
		$front_image = '';
	else
		$front_image = $path.'media/catalog/product'.$res['front_image'];
	
	if($res['back_image'] == 'no_selection' or $res['back_image'] == '')
		$back_image = '';
	else
		$back_image = $path.'media/catalog/product'.$res['back_image'];
		
	if($res['left_image'] == 'no_selection' or $res['left_image'] == '')
		$left_image = '';
	else
		$left_image = $path.'media/catalog/product'.$res['left_image'];
		
	if($res['right_image'] == 'no_selection' or $res['right_image'] == '')
		$right_image = '';
	else
		$right_image = $path.'media/catalog/product'.$res['right_image'];
	



		
	$no_of_sides = $product->getAttributeText('no_of_sides');
	
	//Code added to check template id if set then take one side starts
	if($_GET['templateId'] != "")
		$no_of_sides = 1;
	//Code added to check template id if set then take one side ends
	
	$xmlString .= '	<product>';
	$xmlString .= '	<name>'.$product->getName().'</name>';
	
	$xmlString .= '	<type>'.'product'.'</type>';
	$xmlString .= '	<productID>'.$product->getId().'</productID>';
	$xmlString .= '	<code>'.$res['sku'].'</code>';		
	$xmlString .= '	<weight>'.$product->getWeight().'</weight>';
	$xmlString .= '	<categoryID>'.$product->getCategory_id().'</categoryID>';
	$xmlString .= '	<provider>'.$res['Manufacturer'].'</provider>';
	$xmlString .= '	<shortDesc>'.htmlspecialchars($product->getShort_description()).'</shortDesc>';
	$xmlString .= '	<longDesc>'.htmlspecialchars($product->getDescription()).'</longDesc>';		
	$xmlString .= '	<defaultThumb>'.$thumbnail.'</defaultThumb>';
	$xmlString .= '	<availability>'.$status.'</availability>';
	$xmlString .= '	<visibleSide>'.'both'.'</visibleSide>';
	
	$xmlString .= '	<multiColor>'.$multicolor.'</multiColor>';
	
	$xmlString .= '	<allImages><image>';
	$xmlString .= '	<frontImage>'.$front_image.'</frontImage>';
	$xmlString .= '	<backImage>'.$back_image.'</backImage>';
	$xmlString .= '	<thumbImage>'.$thumbnail.'</thumbImage>';
	$xmlString .= '	<combinationName>'.'white and black'.'</combinationName>';
	$xmlString .= '	</image></allImages>';
	
	$xmlString .= '	<noofSides>'.$no_of_sides.'</noofSides>';
	$xmlString .= '	<productImages><image>';
	switch($no_of_sides)
	{
		case 4:
			$xmlString .= '	<frontImage>'.$front_image.'</frontImage>';
			$xmlString .= '	<backImage>'.$back_image.'</backImage>';
			$xmlString .= '	<lefImage>'.$left_image.'</lefImage>';
			$xmlString .= '	<rightImage>'.$right_image.'</rightImage>';
            
          
			break;
		case 3:
			$xmlString .= '	<frontImage>'.$front_image.'</frontImage>';
			$xmlString .= '	<backImage>'.$back_image.'</backImage>';
            
            
            
            
			if($left_image != '')
            {
				$xmlString .= '	<lefImage>'.$left_image.'</lefImage>';
                
             }   
			else
            {
				$xmlString .= '	<rightImage>'.$right_image.'</rightImage>';
                
            }    
			break;
		case 2:
		default: 
			$xmlString .= '	<frontImage>'.$front_image.'</frontImage>';
			$xmlString .= '	<backImage>'.$back_image.'</backImage>';
            
			break;
			
	}
	$xmlString .= '	</image></productImages>';
    
    
    
	$xmlString .= '	<Area>';	
	switch($no_of_sides)
	{
		case 4:
			$xmlString .= '	<frontArea>'.$product->getFa_x().','.$product->getFa_y().','.$product->getFa_width().','.$product->getFa_height().'</frontArea>';		
			$xmlString .= '	<backArea>'.$product->getBa_x().','.$product->getBa_y().','.$product->getBa_width().','.$product->getBa_height().'</backArea>';
			$xmlString .= '	<leftArea>'.$product->getLe_x().','.$product->getLe_y().','.$product->getLe_width().','.$product->getLe_height().'</leftArea>';		
			$xmlString .= '	<rightArea>'.$product->getRi_x().','.$product->getRi_y().','.$product->getRi_width().','.$product->getRi_height().'</rightArea>';			
			break;
		case 3:
			$xmlString .= '	<frontArea>'.$product->getFa_x().','.$product->getFa_y().','.$product->getFa_width().','.$product->getFa_height().'</frontArea>';		
			$xmlString .= '	<backArea>'.$product->getBa_x().','.$product->getBa_y().','.$product->getBa_width().','.$product->getBa_height().'</backArea>';
			if($left_image != '')
				$xmlString .= '	<leftArea>'.$product->getLe_x().','.$product->getLe_y().','.$product->getLe_width().','.$product->getLe_height().'</leftArea>';	
			else
				$xmlString .= '	<rightArea>'.$product->getRi_x().','.$product->getRi_y().','.$product->getRi_width().','.$product->getRi_height().'</rightArea>';		
			break;
		case 2:
		default: 
			$xmlString .= '	<frontArea>'.$product->getFa_x().','.$product->getFa_y().','.$product->getFa_width().','.$product->getFa_height().'</frontArea>';		
			$xmlString .= '	<backArea>'.$product->getBa_x().','.$product->getBa_y().','.$product->getBa_width().','.$product->getBa_height().'</backArea>';
			break;			
	}	
	$xmlString .= '	</Area>';
	
	$xmlString .= '	<frontTextLines>15</frontTextLines>';
	$xmlString .= '	<backTextLines>8</backTextLines>';
	$xmlString .= '	<freeShipping>Y</freeShipping>';
	$xmlString .= '	<discountType/>';
	$xmlString .= '	<minQuantity>'.$product->getMinqty().'</minQuantity>';
	$xmlString .= '	<price>'.$product->getPrice().'</price>';
	$xmlString .= '	<material>Cotton</material>';
	$xmlString .= '	<companionStyles>Pink Style</companionStyles>';	
	
		
		//if Add multicolor functionality is false starts
		if($multicolor=='no')
		{	
			/*Get Simple Product all sizes from related color starts*/
	$sql_relation = "SELECT cpr.`child_id` 
									FROM `catalog_product_entity` as cpe, 
										`catalog_product_relation` as cpr 
									WHERE cpe.`entity_id` = cpr.`parent_id`  AND cpe.`entity_id` = ".$res['entity_id'];
	$data_relation = $read->fetchAll($sql_relation);
	$sizearray = array();
	$colorarray = array();
    $test =array();
    $cnt = '0';
  	foreach($data_relation as $dt)
	{
		$simple_product = Mage::getModel('catalog/product')->load($dt['child_id']); 
		$simple_product->getCollection();
		
		//Enable simple product only with in stock 
		$user = $_GET['user'];
		
		if($simple_product->getStatus() == '1' && $simple_product->getData('is_in_stock')== '1' && $user != "admin")
		{ 
				$colorValue = Mage::getModel('catalog/product')
									->load($simple_product->getId())
									->getAttributeText('color');
				$sizeValue = Mage::getModel('catalog/product')
									->load($simple_product->getId())
									->getAttributeText('size');
				$test = $colorValue;
				 if(!in_array($test,$colorarray))
				  {
					 $colorarray[$cnt] .= $colorValue;
					 $sizearray[$cnt] = $sizeValue;
				  }
				  else
				  {
					 $key = array_search($test, $colorarray);
					 $val =  $key ;
					 $sizearray[$val] .= ','.$sizeValue;
				  } 
				  $cnt ++;
		 }
		 else if($simple_product->getStatus() == '1' && $user == "admin")
		 {
		 		$colorValue = Mage::getModel('catalog/product')
									->load($simple_product->getId())
									->getAttributeText('color');
				$sizeValue = Mage::getModel('catalog/product')
									->load($simple_product->getId())
									->getAttributeText('size');
				$test = $colorValue;
				 if(!in_array($test,$colorarray))
				  {
					 $colorarray[$cnt] .= $colorValue;
					 $sizearray[$cnt] = $sizeValue;
				  }
				  else
				  {
					 $key = array_search($test, $colorarray);
					 $val =  $key ;
					 $sizearray[$val] .= ','.$sizeValue;
				  } 
				  $cnt ++;
		 
		 }
		 		
		 
		 		  
	} 
	 
	$newarray = array_combine($colorarray, $sizearray);
	$xmlString .= '	<allColors>';
	foreach($res['color'] as $r1)
	{
		$newval = $r1['value'];
		
		if(in_array($newval,$colorarray))
		{
			$xmlString .= '	<color>';
			
			$xmlString .= '	<optionID>'.$r1['value_index'].'</optionID>';
			$color_name = explode('(', $r1['value']);
			$clr_name = array_reverse($color_name);
			$cl_name = explode(')', $clr_name[0]);
						
			$xmlString .= '	<optionName>'.$cl_name[0].'</optionName>';
			$xmlString .= '	<priceModifier>'.$r1['pricing_value'].'</priceModifier>';
			$xmlString .= '	<colorName>'.$color_name[0].'</colorName>';
		}
		
		if (array_key_exists($newval, $newarray))
		{
			$newsize = $newarray[$newval];
			$simplesizes = explode(",", $newsize); 
		}
		if(in_array($newval,$colorarray))
		{
			$xmlString .= '	<sizes>';
			 
			foreach($res['size'] as $r)
			{
				$sizeval =  $r['value'];
				if(in_array($sizeval,$simplesizes))
				{
					$xmlString .= '	<size>';
					$xmlString .= '	<optionID>'.$r['value_index'].'</optionID>';
					$xmlString .= '	<optionName>'.$r['value'].'</optionName>';
					$xmlString .= '	<priceModifier>'.$r['pricing_value'].'</priceModifier>';
					$xmlString .= '	</size>';
				}
			
			}
			$xmlString .= '	</sizes>';
			$xmlString .= '	</color>';
		}
		

	}
	$xmlString .= '	</allColors>';			
		}	
		//if Add multicolor functionality is false ends
		//if Add multicolor functionality is true starts
		else if($multicolor=='yes')
		{
				/*Code added by bhagyashri to get simple products images started*/
					 $sql_relation = "SELECT cpr.`child_id` 
									FROM `catalog_product_entity` as cpe, 
										`catalog_product_relation` as cpr 
									WHERE cpe.`entity_id` = cpr.`parent_id`  AND cpe.`entity_id` = ".$res['entity_id'];
							
					$data_relation = $read->fetchAll($sql_relation);
					$sizearray = array();
					$colorarray = array();
					$test =array();
					$cnt = '0';
					foreach($data_relation as $dt)
					{
						$simple_product = Mage::getModel('catalog/product')->load($dt['child_id']); 
						
						
						if($simple_product->getData('is_in_stock') == '1' && $simple_product->getData('status') == '1' )
						{
						
							$colorValue = Mage::getModel('catalog/product')
								->load($simple_product->getId())
								->getAttributeText('color');
							$sizeValue = Mage::getModel('catalog/product')
												->load($simple_product->getId())
												->getAttributeText('size');
							
							$test = $colorValue;
							 if(!in_array($test,$colorarray))
							  {
								 $colorarray[$cnt] .= $colorValue;
								 $sizearray[$cnt] = $sizeValue;
							  }
							  else
							  {
								 $key = array_search($test, $colorarray);
								 $val =  $key ;
								 $sizearray[$val] .= ','.$sizeValue;
							  } 
							  $cnt ++;
							  
								
							
							$simple_product_images[$res['entity_id']][] = array(
															'simple_product_name' =>$simple_product->getName() ,
															'front_image' =>$simple_product->getFront_image() ,
															'back_image' =>$simple_product->getBack_image() ,
															'left_image' =>$simple_product->getLeft_image() ,
															'right_image' =>$simple_product->getRight_image(),
															'color_image' =>$simple_product->getColor_image(),
															'simple_product_color' =>$simple_product->getAttributeText('color'),
															'simple_product_status' =>$simple_product->getStatus()   
														);
						 }								
					 }
							$newarray = array_combine($colorarray, $sizearray);
							
					//Color XML started
					$xmlString .= '	<allColors>';

					foreach($res['color'] as $r)
					{
						
						 $newval = $r['value'];
						
						$color_name = explode('(', $r['value']);
						$clr_name = array_reverse($color_name);
						$cl_name = explode(')', $clr_name[0]);
								 //Simple product array
									 $cnt = count($simple_product_images[$res['entity_id']]);
									 for($i=0;$i<$cnt;$i++)
									 {	
										$simple_product_name = $simple_product_images[$res['entity_id']][$i]['simple_product_name'];
										$front_image = $simple_product_images[$res['entity_id']][$i]['front_image'];
										$simple_product_color = $simple_product_images[$res['entity_id']][$i]['simple_product_color'];
										$simple_product_status = $simple_product_images[$res['entity_id']][$i]['simple_product_status'];
										//if( trim($simple_product_color) == trim($color_name[0]) && ($front_image != 'no_selection' && $front_image != '') && ($simple_product_status=='1'))
										if( trim($simple_product_color) == trim($r['value']) && ($front_image != 'no_selection' && $front_image != '') && ($simple_product_status=='1'))
										{
											
											$number_of_sides = 0;
											$xmlString .= '	<color>';
											$xmlString .= '	<optionID>'.$r['value_index'].'</optionID>';
											
											$back_image = $simple_product_images[$res['entity_id']][$i]['back_image'];
											$left_image = $simple_product_images[$res['entity_id']][$i]['left_image'];
											$right_image = $simple_product_images[$res['entity_id']][$i]['right_image'];
											$color_image = $simple_product_images[$res['entity_id']][$i]['color_image'];
											
											 
											if($front_image == 'no_selection' or $front_image == '')
												$front_image = $blank_image;
											else
												$front_image = $path.'media/catalog/product'.$front_image;
											
											if($back_image == 'no_selection' or $back_image == '')
												$back_image = $blank_image;
											else
												$back_image = $path.'media/catalog/product'.$back_image;
												
											if($left_image == 'no_selection' or $left_image == '')
												$left_image = $blank_image;
											else
												$left_image = $path.'media/catalog/product'.$left_image;
												
											if($right_image == 'no_selection' or $right_image== '')
												$right_image = $blank_image;
											else
												$right_image = $path.'media/catalog/product'.$right_image;
												
											if($color_image == 'no_selection' or $color_image== '')
												$color_image = '';
											else
												$color_image = $path.'media/catalog/product'.$color_image;
											
											
											$xmlString .= '	<colorimage>'.$color_image.'</colorimage>';
											$xmlString .= '	<image>';
											
											if( ($front_image!= "") )
											{
												$xmlString .= '	<frontImage>'.$front_image.'</frontImage>';
											}
											if( ($back_image!= "") )
											{
												$xmlString .= '	<backImage>'.$back_image.'</backImage>';
											}
											if(  ($left_image!= "") )
											{
												$xmlString .= '	<lefImage>'.$left_image.'</lefImage>';
												
											}
											if(  ($right_image!= "") )
											{
												$xmlString .= '	<rightImage>'.$right_image.'</rightImage>';
											}
											$xmlString .= '	</image>';
											$xmlString .= '	<priceModifier>'.$r['pricing_value'].'</priceModifier>';
											$xmlString .= '	<colorName>'.$color_name[0].'</colorName>';
											if (array_key_exists($newval, $newarray))
											{
												$newsize = $newarray[$newval];
												$simplesizes = explode(",", $newsize); 
											}
											$xmlString .= '	<sizes>';	
											foreach($res['size'] as $r1)
											{
												$sizeval =  $r1['value'];
												if(in_array($sizeval,$simplesizes))
												{
		
													$xmlString .= '	<size>';
													$xmlString .= '	<optionID>'.$r1['value_index'].'</optionID>';
													$xmlString .= '	<optionName>'.$r1['value'].'</optionName>';
													$xmlString .= '	<priceModifier>'.$r1['pricing_value'].'</priceModifier>';
													$xmlString .= '	</size>';
												}
											
											}
											$xmlString .= '	</sizes>';
											$xmlString .= '	</color>';
											break;
										}
										
									  }//End of for loop
					}//exit;
					$xmlString .= '	</allColors>';
		}
		//if Add multicolor functionality is true ends
		
		
		$xmlString .= '	</product>';
		
		
		
}

$xmlString .= '</allProducts>';

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;
?>