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
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


$installer = $this;
$installer->startSetup();
$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/campaign')}` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` TINYTEXT NOT NULL ,
    `status` INT( 1 ) NOT NULL DEFAULT '0',
    `store_ids` TINYTEXT NOT NULL ,
    `active_from` DATE NULL DEFAULT NULL,
    `active_to` DATE NULL DEFAULT NULL,
    `allowed_groups` TINYTEXT NOT NULL ,
    `product_selection_rule` TEXT NOT NULL ,
    `type` TINYTEXT NOT NULL ,
    PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/profit')}` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` INT( 10 ) UNSIGNED NOT NULL ,
    `type` ENUM('fixed', 'tier','fixedcur','tiercur'),
    `rate_settings` TEXT NOT NULL,
    PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/profit_tier_rate')}` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `profit_id` INT( 10 ) UNSIGNED NOT NULL ,
    `profit_rate` FLOAT NOT NULL,
    `profit_amount` FLOAT NOT NULL,
    `affiliate_group_id` INT( 3 ) UNSIGNED NOT NULL,
    PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/affiliate')}` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` INT( 10 ) UNSIGNED NOT NULL ,
    `rate` FLOAT NOT NULL,
    `status` ENUM('active', 'inactive'),
    `current_balance` FLOAT NOT NULL,
    `active_balance` FLOAT NOT NULL,
    PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/client')}` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` INT( 10 ) UNSIGNED DEFAULT NULL  ,
    `campaign_id` INT( 10 ) UNSIGNED NOT NULL ,
    `affiliate_id` INT( 10 ) UNSIGNED NOT NULL,
    `traffic_id` INT( 10 ) UNSIGNED NOT NULL ,
    `created_at` TIMESTAMP NOT NULL,
    PRIMARY KEY ( `id` ),
    FOREIGN KEY (`affiliate_id`) REFERENCES {$this->getTable('awaffiliate/affiliate')} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/client_history')}` (
    `id` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id` INT( 10 ) UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL,
    `action` TINYTEXT NOT NULL,
    `linked_item_type` TINYTEXT NOT NULL,
    `linked_item_id` INT( 15 ) NOT NULL,
    `params` TEXT NOT NULL ,
    PRIMARY KEY ( `id` ),
    FOREIGN KEY (`client_id`) REFERENCES {$this->getTable('awaffiliate/client')} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/withdrawal_request')}` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `affiliate_id` INT( 10 ) UNSIGNED NOT NULL ,
    `transaction_id` INT( 15 ) UNSIGNED NOT NULL ,
    `amount` FLOAT NOT NULL,
    `created_at` TIMESTAMP NULL,
    `description` TEXT NOT NULL ,
    `notice` TEXT NOT NULL ,
    `status` ENUM('pending', 'paid', 'rejected', 'failed'),
    PRIMARY KEY ( `id` ),
    FOREIGN KEY (`affiliate_id`) REFERENCES {$this->getTable('awaffiliate/affiliate')} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/transaction_withdrawal')}` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `created_at` TIMESTAMP NULL,
    `description` TEXT NOT NULL ,
    `notice` TEXT NOT NULL ,
    `amount` FLOAT NOT NULL,
    `currency_code` TINYTEXT NOT NULL,
    PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/transaction_profit')}` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `created_at` TIMESTAMP NOT NULL,
    `description` TEXT NOT NULL ,
    `notice` TEXT NOT NULL ,
    `amount` FLOAT NOT NULL,
    `campaign_id` INT( 10 ) UNSIGNED NOT NULL,
    `type` ENUM('trx_customer_visit', 'trx_customer_purchase', 'trx_admin'),
    `linked_entity_type` TINYTEXT NOT NULL ,
    `linked_entity_id` INT( 10 ) NOT NULL,
    `affiliate_id` INT( 10 ) UNSIGNED NOT NULL,
    `traffic_id` INT( 10 ) UNSIGNED NOT NULL,
    `client_id` INT( 10 ) UNSIGNED NOT NULL,
    `rate` FLOAT NOT NULL,
    `currency_code` TINYTEXT NOT NULL,
    `attracted_amount` FLOAT NOT NULL DEFAULT 0,
    PRIMARY KEY ( `id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `{$this->getTable('awaffiliate/traffic_source')}` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `affiliate_id` INT( 10 ) UNSIGNED NOT NULL ,
    `traffic_name` TINYTEXT NOT NULL,
    PRIMARY KEY ( `id` ),
    FOREIGN KEY (`affiliate_id`) REFERENCES {$this->getTable('awaffiliate/affiliate')} (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
SQL;

try {
    $installer->run($sql);
} catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();
