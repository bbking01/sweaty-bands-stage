<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('points/summary')} (
  `id` int(11) NOT NULL auto_increment,
  `customer_id` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `balance_update_notification` TINYINT DEFAULT NULL,
  `points_expiration_notification` TINYINT DEFAULT NULL,
  `points_for_subscription_granted` TINYINT DEFAULT NULL,
  `points_for_registration_granted` TINYINT DEFAULT NULL,
  `points_for_tags_granted` TEXT,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `AW_POINTS_SUMMARY_CUSTOMER_ID` (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('points/transaction')} (
  `id` int(11) NOT NULL auto_increment,
  `store_id` smallint(5) unsigned NOT NULL,
  `summary_id` int(11) NOT NULL,
  `balance_change` int(11) NOT NULL,
  `balance_change_spent` int(11) NOT NULL,
  `action` varchar(30) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `notice` varchar(255) NOT NULL,
  `change_date` datetime NOT NULL,
  `expiration_date` datetime NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  CONSTRAINT `AW_POINTS_TRANSACTION_FK` FOREIGN KEY (`summary_id`) REFERENCES {$this->getTable('points/summary')} (`id`) ON DELETE CASCADE,
  KEY `AW_POINTS_TRANSACTION_ACTION` (`action`),
  KEY `AW_POINTS_TRANSACTION_BALANCE_CHANGE_SPENT` (`balance_change_spent`),
  KEY `AW_POINTS_TRANSACTION_BALANCE_CHANGE` (`balance_change`),
  KEY `AW_POINTS_TRANSACTION_CHANGE_DATE` (`change_date`),
  KEY `AW_POINTS_TRANSACTION_EXPIRATION_DATE` (`expiration_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

  CREATE TABLE IF NOT EXISTS {$this->getTable('points/rule')} (
  `rule_id` int(10) unsigned NOT NULL auto_increment,
  `sort_order` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `from_date` date default '0000-00-00',
  `to_date` date default '0000-00-00',
  `customer_group_ids` text,
  `is_active` tinyint(1) NOT NULL default '0',
  `conditions_serialized` mediumtext NOT NULL,
  `website_ids` text,
  `points_change` int(11) NOT NULL,
  `static_blocks_ids` text,
  PRIMARY KEY  (`rule_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

   CREATE TABLE IF NOT EXISTS {$this->getTable('points/invitation')} (
    `invitation_id` INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `customer_id` INT( 10 ) UNSIGNED DEFAULT NULL ,
    `date` DATETIME NOT NULL ,
    `email` VARCHAR( 255 ) NOT NULL ,
    `referral_id` INT( 10 ) UNSIGNED DEFAULT NULL ,
    `protection_code` CHAR(32) NOT NULL,
    `signup_date` DATETIME DEFAULT NULL,
    `store_id` SMALLINT(5) UNSIGNED NOT NULL,
    `message` TEXT DEFAULT NULL,
    `status` SMALLINT(5) UNSIGNED NOT NULL,
  UNIQUE KEY `email` (`email`),
  PRIMARY KEY  (`invitation_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

   CREATE TABLE IF NOT EXISTS {$this->getTable('points/rate')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `website_ids` text,
  `customer_group_ids` text,
  `direction` smallint(3) NOT NULL,
  `points` int(11) NOT NULL,
  `money` float NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

   CREATE TABLE IF NOT EXISTS {$this->getTable('points/transaction_orderspend')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `transaction_id` int(11) NOT NULL,
  `order_increment_id` varchar(50) NOT NULL,
  `points_to_money` decimal(12,4) NOT NULL,
  `base_points_to_money` decimal(12,4) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `points_order_increment_id` (`order_increment_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

$installer->endSetup();
