<?php
/**
 * Grow Development - Store Locations Pro
 *
 * @category   Growdevelopment
 * @package    Growdevelopment_StoreLocations
 * @copyright  Copyright (c) 2012 Grow Development (http://www.growdevelopment.com)
 * @license    http://www.growdevelopment.com/docs/eula.txt
 * @author     Daniel Espinoza <modules@growdevelopment.com>
 *
 */

$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('growdevelopment_storelocation')};
CREATE TABLE IF NOT EXISTS {$this->getTable('growdevelopment_storelocation')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `status` smallint(6) NOT NULL default '0',
  `store_name` varchar(255) NOT NULL default '',
  `owner_name` varchar(255) NOT NULL default '',
  `street` varchar(255) NOT NULL default '',
  `city` varchar(255) NOT NULL default '',
  `location_region_id` varchar(255) NOT NULL default '',
  `postal_code` varchar(255) NOT NULL default '',
  `location_country_id` varchar(255) NOT NULL default '',
  `phone` varchar(255) NOT NULL default '',
  `photo` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `store_type` varchar(255) NOT NULL default '',
  `google_latitude` decimal(15,10) NOT NULL,
  `google_longitude` decimal(15,10) NOT NULL,
  `google_zoom_level` int(5) NOT NULL,
  `description` text,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('growdevelopment_storelocation_products')};
CREATE TABLE IF NOT EXISTS {$this->getTable('growdevelopment_storelocation_products')} (
  `relation_id` int(10) unsigned NOT NULL auto_increment,
  `store_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  PRIMARY KEY  (`relation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=25;


");

$installer->endSetup();


