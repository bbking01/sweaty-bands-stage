<?php
$installer = $this;
$installer->startSetup();

$installer->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('save_design')}` (
  `design_id` int(11) NOT NULL auto_increment,
  `customer_id` int(11) NOT NULL,
  `products_id` int(11) NOT NULL,
  `design_name` varchar(255) NOT NULL,
  `front_image` varchar(255) NOT NULL,
  `back_image` varchar(255) NOT NULL,
  `left_image` varchar(255) NOT NULL,
  `right_image` varchar(255) NOT NULL,
  `save_string` longtext NOT NULL,
  `action` varchar(255) NOT NULL,
  PRIMARY KEY  (`design_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1");

$installer->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('user_cliparts')}` (
  `id` int(11) NOT NULL auto_increment,
  `customer_id` int(11) NOT NULL,
  `imgname` varchar(200) NOT NULL,
  `vectorname` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1
");



$installer->run("CREATE TABLE IF NOT EXISTS `{$this->getTable('designtool_configarea')}` (
  `id` int(11) NOT NULL auto_increment,
  `product_id` int(11) NOT NULL,
  `fa_height` varchar(200) NULL,
  `fa_width` varchar(200) NULL,
  `fa_x` varchar(200) NULL,
  `fa_y` varchar(200) NULL,
  `ba_height` varchar(200) NULL,  
  `ba_width` varchar(200) NULL,
  `ba_x` varchar(200) NULL,
  `ba_y` varchar(200) NULL,
  `le_height` varchar(200) NULL,
  `le_width` varchar(200) NULL,
  `le_x` varchar(200) NULL,
  `le_y` varchar(200) NULL,
  `ri_height` varchar(200) NULL,
  `ri_width` varchar(200) NULL,
  `ri_x` varchar(200) NULL,
  `ri_y` varchar(200) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1
");

$installer->endSetup();
?>