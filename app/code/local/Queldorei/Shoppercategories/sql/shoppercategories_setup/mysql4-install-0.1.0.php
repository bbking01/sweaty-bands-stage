<?php
/**
 * @version   1.0 06.08.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

$installer = $this;
$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('shoppercategories/scheme')}`;
CREATE TABLE `{$this->getTable('shoppercategories/scheme')}` (
  `scheme_id` int(11) unsigned NOT NULL auto_increment,
  `category_id` int(11) unsigned NOT NULL,
  `apply_child` tinyint(1) unsigned NOT NULL default '1',
  `status` tinyint(1) NOT NULL default '1',
  `enable_font` tinyint(1) NOT NULL default '1',
  `font` varchar(32) NOT NULL default '',
  `color` char(7) NOT NULL default '',
  `title_color` char(7) NOT NULL default '',
  `header_bg` char(7) NOT NULL default '',
  `slideshow_bg` char(7) NOT NULL default '',
  `content_bg` char(7) NOT NULL default '',
  `content_link` char(7) NOT NULL default '',
  `content_link_hover` char(7) NOT NULL default '',
  `page_title_bg` char(7) NOT NULL default '',
  `toolbar_bg` char(7) NOT NULL default '',
  `toolbar_color` char(7) NOT NULL default '',
  `toolbar_hover_color` char(7) NOT NULL default '',
  `footer_bg` char(7) NOT NULL default '',
  `footer_color` char(7) NOT NULL default '',
  `footer_hover_color` char(7) NOT NULL default '',
  `footer_banners_bg` char(7) NOT NULL default '',
  `footer_info_bg` char(7) NOT NULL default '',
  `footer_info_border` char(7) NOT NULL default '',
  `footer_info_title_color` char(7) NOT NULL default '',
  `footer_info_color` char(7) NOT NULL default '',
  `footer_info_link_color` char(7) NOT NULL default '',
  `footer_info_link_hover_color` char(7) NOT NULL default '',
  `price_font` varchar(32) NOT NULL default '',
  `price_color` char(7) NOT NULL default '',
  `price_circle_color` char(7) NOT NULL default '',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`scheme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

/**
 * Drop 'scheme_store' table
 */
$conn = $installer->getConnection();
$conn->dropTable($installer->getTable('shoppercategories/scheme_store'));

/**
 * Create table for stores
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('shoppercategories/scheme_store'))
    ->addColumn('scheme_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'nullable'  => false,
    'primary'   => true,
), 'Scheme ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Store ID')
    ->addIndex($installer->getIdxName('shoppercategories/scheme_store', array('store_id')),
    array('store_id'))
    ->addForeignKey($installer->getFkName('shoppercategories/scheme_store', 'scheme_id', 'shoppercategories/scheme', 'scheme_id'),
    'scheme_id', $installer->getTable('shoppercategories/scheme'), 'scheme_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('shoppercategories/scheme_store', 'store_id', 'core/store', 'store_id'),
    'store_id', $installer->getTable('core/store'), 'store_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Scheme To Store Linkage Table');
$installer->getConnection()->createTable($table);
$installer->endSetup();