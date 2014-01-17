<?php

$installer = $this;

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();


//adding Designtool attribute set
$sNewSetName = 'Designtool';
$iCatalogProductEntityTypeId = (int) $setup->getEntityTypeId('catalog_product');

$oAttributeset = Mage::getModel('eav/entity_attribute_set')
    ->setEntityTypeId($iCatalogProductEntityTypeId)
    ->setAttributeSetName($sNewSetName);
//adding designtool attribute set based on default attribute set
if ($oAttributeset->validate()) {
    $oAttributeset
        ->save()
        ->initFromSkeleton(9)
        ->save();
}
else {
    die('Attribute set with name ' . $sNewSetName . ' already exists.');
}
 	
// adding Designtool attribute group
// the attribute added will be displayed under the group/tab Designtool in product edit page
$setup->addAttributeGroup('catalog_product', 'Designtool', 'Designtool', 1);


//add color attribute
$setup->addAttribute('catalog_product', 'color', array(
	'group'     	=> 'Designtool',
	'input'         => 'select',
    'type'          => 'text',
    'label'         => 'Color',
	'backend'       => '',
	'visible'       => 1,
	'required'		=> 0,
	'user_defined' => 1,
	'is_configurable' => 1, 
	'searchable' => 1,
	'filterable' => 0,
	'comparable'	=> 1,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 0,
	'apply_to' => array('simple'),
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'option'            => array (
                                            'value' => array('optionone' => array('Black(#000000)'),
                                                             'optiontwo' => array('Blue(#0000FF)'),
                                                             'optionthree' => array('Brown(#551011)'),
															 'optionfour' => array('CadetBlue4(#4C787E)'),
															 'optionfive' => array('Gray(#736F6E)'),
															 'optionsix' => array('Lime(#00FF00)'),
															 'optionseven' => array('Pink-White(#FF00FF)'),
															 'optioneight' => array('White(#FFFFFF)'),
															 'optionnine' => array('Fuchsia(#FF00FF)'),
															 'optionten' => array('Green(#339933)'),
															 'optioneleven' => array('LawnGreen(#7CFC00)'),
															 'optiontwelve' => array('Orange(#FFA500)'),
															 'optionthirteen' => array('Pink(#FF33CC)'),
															 'optionfourteen' => array('RoyalBlue(#4169E1)'),
															 'optionfifteen' => array('Violet(#CC66FF)'),
															 'optionsixteen' => array('Yellow(#FFFF00)'),
															 'optionseventeen' => array('Hot Pink (#FF69B4)'),
															 'optioneighteen' => array('Darker Blue (#00008B)'),
															 'optionnineteen' => array('Silver(#C0C0C0)'),
															 'optiontwenty' => array('Red(#FF0000)'),
															 'optiontwentyone' => array('Olive(#808000)'),
															 'optiontwentytwo' => array('Maroon(#800000)'),
															 'optiontwentythree' => array('Crimson(#DC143C)'),	
															'optiontwentyfour' => array('Blue'),
															'optiontwentyfive' => array('Brown'),
															'optiontwentysix' => array('Crimson'),
															'optiontwentyseven' => array('Darkgreen'),
															'optiontwentyeight' => array('Green'),
															'optiontwentynine' => array('Maroon'),
															'thirtyone' => array('Olive'),
															'thirtytwo' => array('Orange'),
															'thirtythree' => array('Pink'),
															'thirtyfour' => array('Red'),
															'thirtyfive' => array('Multicolor1'),
															'thirtysix' => array('Multicolor2'),
															'thirtyseven' => array('Multicolor3'),
															'thirtyeight' => array('Multicolor4'),
															'thirtynine' => array('Multicolor5'),
                                                        )
                                        ),
)); 


//add size attribute
$setup->addAttribute('catalog_product', 'size', array(
	'group'     	=> 'Designtool',
	'input'         => 'select',
    'type'          => 'text',
    'label'         => 'Size',
	'backend'       => '',
	'visible'       => 1,
	'required'		=> 0,
	'user_defined' => 1,
	'is_configurable' => 1, 
	'searchable' => 1,
	'filterable' => 0,
	'comparable'	=> 1,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 0,
	'apply_to' => array('simple'),
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'option'            => array (
                                            'value' => array('optionone' => array('11.6 inch'),
                                                             'optiontwo' => array('12 inch'),
                                                             'optionthree' => array('13 inch'),
															 'optionfour' => array('14 inch'),
															 'optionfive' => array('15 inch'),
															 'optionsix' => array('17 inch'),
															 'optionseven' => array('1GB'),
															 'optioneight' => array('2GB'),
															 'optionnine' => array('4.5 inches'),
															 'optionten' => array('4GB'),
															 'optioneleven' => array('8GB'),
															 'optiontwelve' => array('L'),
															 'optionthirteen' => array('Large'),
															 'optionfourteen' => array('M'),
															 'optionfifteen' => array('Medium)'),
															 'optionsixteen' => array('medium'),
															 'optionseventeen' => array('normal'),
															 'optioneighteen' => array('Round'),
															 'optionnineteen' => array('S'),
															 'optiontwenty' => array('Small'),
															 'optiontwentyone' => array('standard'),
															 'optiontwentytwo' => array('XL'),
															 'optiontwentythree' => array('XXL'),	
															'optiontwentyfour' => array('XXXL'),
                                                        )
                                        ),
)); 



//add is_customizable attribute
$setup->addAttribute('catalog_product', 'is_customizable', array(
	'group'     	=> 'Designtool',
	'input'         => 'boolean',
    'type'          => 'text',
    'label'         => 'Customizable Product',
	'backend'       => '',
	'visible'       => 1,
	'required'		=> 0,	
	'user_defined' => 1,
	'is_configurable' => 0, 
	'searchable' => 1,
	'filterable' => 0,
	'comparable'	=> 1,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 0,	
	'used_in_product_listing'   => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->updateAttribute('catalog_product', 'is_customizable', 'apply_to', 'configurable');
$setup->updateAttribute('catalog_product', 'is_customizable', 'used_in_product_listing', 1);
$setup->addAttributeToSet('catalog_product', 'Designtool', 'Designtool', 'is_customizable', 10);

//add multicolor attribute
$setup->addAttribute('catalog_product', 'multicolor', array(
	'group'     	=> 'Designtool',
	'input'         => 'boolean',
    'type'          => 'text',
    'label'         => 'Add Multicolor functionality',
	'backend'       => '',
	'visible'       => 1,
	'required'		=> 0,	
	'apply_to'      => 'configurable',
	'user_defined' => 1,
	'is_configurable' => 0, 
	'searchable' => 1,
	'filterable' => 0,
	'comparable'	=> 1,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 0,	
	'used_in_product_listing'   => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$setup->updateAttribute('catalog_product', 'multicolor', 'apply_to', 'configurable');
$setup->addAttributeToSet('catalog_product', 'Designtool', 'Designtool', 'multicolor', 20);

//add no_of_sides attribute
$setup->addAttribute('catalog_product', 'no_of_sides', array(
	'group'     	=> 'Designtool',
	'input'         => 'select',
    'type'          => 'text',
    'label'         => 'No Of Sides',
	'backend'       => '',
	'visible'       => 1,
	'required'		=> 0,	
	'apply_to'      => 'configurable',
	'user_defined' => 1,
	'is_configurable' => 0, 
	'searchable' => 1,
	'filterable' => 0,
	'option'            => array (
                                            'value' => array('optionone' => array('1'),
                                                             'optiontwo' => array('2'),
                                                             'optionthree' => array('3'),
															 'optionfour' => array('4'),
                                                        )
                                        ),
	'comparable'	=> 1,
	'visible_on_front' => 1,
	'visible_in_advanced_search'  => 0,
	'is_html_allowed_on_front' => 0,	
	'used_in_product_listing'   => 0,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));
$setup->updateAttribute('catalog_product', 'no_of_sides', 'is_configurable', 0);
$setup->updateAttribute('catalog_product', 'no_of_sides', 'apply_to', 'configurable');
$setup->addAttributeToSet('catalog_product', 'Designtool', 'Designtool', 'no_of_sides', 30);

//add color_image attribute
$setup->addAttribute('catalog_product', 'color_image', array(	
	'input'         => 'media_image',
    'type'          => 'varchar',
    'label'         => 'Color Image',
	'backend'       => '',
	'visible'       => 1,
	'required'		=> 0,		
	'user_defined' => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,	
));
$setup->addAttributeToSet('catalog_product', 'Designtool', 'Images', 'color_image', 6);
$setup->updateAttribute('catalog_product', 'color_image', 'apply_to', 'simple');

//add front_image attribute
$setup->addAttribute('catalog_product', 'front_image', array(	
	'input'         => 'media_image',
    'type'          => 'varchar',
    'label'         => 'Front Image',
	'backend'       => '',
	'visible'       => 1,
	'required'		=> 0,		
	'user_defined' => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,	
));
$setup->addAttributeToSet('catalog_product', 'Designtool', 'Images', 'front_image', 7);

//add back_image attribute
$setup->addAttribute('catalog_product', 'back_image', array(	
	'input'         => 'media_image',
    'type'          => 'varchar',
    'label'         => 'Back Image',
	'backend'       => '',
	'visible'       => 1,
	'required'		=> 0,		
	'user_defined' => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,	
));
$setup->addAttributeToSet('catalog_product', 'Designtool', 'Images', 'back_image', 8);

//add left_image attribute
$setup->addAttribute('catalog_product', 'left_image', array(	
	'input'         => 'media_image',
    'type'          => 'varchar',
    'label'         => 'Left Image',
	'backend'       => '',
	'visible'       => 1,
	'required'		=> 0,		
	'user_defined' => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,	
));
$setup->addAttributeToSet('catalog_product', 'Designtool', 'Images', 'left_image', 9);

//add right_image attribute
$setup->addAttribute('catalog_product', 'right_image', array(	
	'input'         => 'media_image',
    'type'          => 'varchar',
    'label'         => 'Right Image',
	'backend'       => '',
	'visible'       => 1,
	'required'		=> 0,		
	'user_defined' => 1,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,	
));
$setup->addAttributeToSet('catalog_product', 'Designtool', 'Images', 'right_image', 10);

$installer->endSetup();