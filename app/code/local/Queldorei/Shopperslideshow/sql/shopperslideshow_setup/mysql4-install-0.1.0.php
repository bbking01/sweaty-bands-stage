<?php
/**
 * @version   1.0 06.08.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

$installer = $this;
$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('shopperslideshow/slides')}`;
CREATE TABLE `{$this->getTable('shopperslideshow/slides')}` (
  `slide_id` int(11) unsigned NOT NULL auto_increment,
  `slide_align` ENUM('left', 'right', 'center'),
  `slide_title` text NOT NULL default '',
  `slide_text` text NOT NULL default '',
  `slide_button` text NOT NULL default '',
  `slide_width` varchar(8) NOT NULL default '',
  `slide_link` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `small_image` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `sort_order` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`slide_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{$this->getTable('shopperslideshow/slides')}` (`slide_id`, `slide_align`, `slide_title`, `slide_text`, `slide_button`, `slide_link`, `image`, `status`, `sort_order`, `created_time`, `update_time`) VALUES (1, 'left', 'Lorem Ipsum<br/> Dolor sit Amen', '\'60s-inspired bangles, pendants, and head pieces<br/>from Nicole Richie\'s boho-chic line', 'learn more', '//queldorei.com', 'queldorei/shopper/slideshow/slide1.jpg', 1, 10, NOW(), NOW() );
INSERT INTO `{$this->getTable('shopperslideshow/slides')}` (`slide_id`, `slide_align`, `slide_title`, `slide_text`, `slide_button`, `slide_link`, `image`, `status`, `sort_order`, `created_time`, `update_time`) VALUES (2, 'center', 'Lorem Ipsum Dolor sit Amen', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec viverra enim sed nibh aliquam nec feugiat orci ultricies. Pellentesque dictum, metus ac faucibus ornare, mauris sem condimentum tortor, vel vestibulum nisi felis ut tortor. Mauris venenatis fermentum turpis', 'READY', '//queldorei.com', '', 1, 20, NOW(), NOW() );
INSERT INTO `{$this->getTable('shopperslideshow/slides')}` (`slide_id`, `slide_align`, `slide_title`, `slide_text`, `slide_button`, `slide_link`, `image`, `status`, `sort_order`, `created_time`, `update_time`) VALUES (3, 'left', '', '', '', '', 'queldorei/shopper/slideshow/slide3.jpg', 1, 30, NOW(), NOW() );

");

/**
 * Drop 'slides_store' table
 */
$conn = $installer->getConnection();
$conn->dropTable($installer->getTable('shopperslideshow/slides_store'));

/**
 * Create table for stores
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('shopperslideshow/slides_store'))
    ->addColumn('slide_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'nullable'  => false,
    'primary'   => true,
), 'Slide ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Store ID')
    ->addIndex($installer->getIdxName('shopperslideshow/slides_store', array('store_id')),
    array('store_id'))
    ->addForeignKey($installer->getFkName('shopperslideshow/slides_store', 'slide_id', 'shopperslideshow/slides', 'slide_id'),
    'slide_id', $installer->getTable('shopperslideshow/slides'), 'slide_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('shopperslideshow/slides_store', 'store_id', 'core/store', 'store_id'),
    'store_id', $installer->getTable('core/store'), 'store_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Slide To Store Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Assign 'all store views' to existing slides
 */
$installer->run("INSERT INTO {$this->getTable('shopperslideshow/slides_store')} (`slide_id`, `store_id`) SELECT `slide_id`, 0 FROM {$this->getTable('shopperslideshow/slides')};");
$installer->endSetup();