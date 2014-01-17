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
        CREATE TABLE IF NOT EXISTS {$this->getTable('points/coupon')} (
          `coupon_id` int(11) NOT NULL AUTO_INCREMENT,
          `coupon_code` varchar(255) CHARACTER SET utf8 NOT NULL,
          `coupon_name` varchar(255) CHARACTER SET utf8 NOT NULL,
          `description` text NOT NULL,
          `from_date` date default '0000-00-00',
          `to_date` date default '0000-00-00',
          `status` INT( 11 ) NOT NULL default '0',
          `points_amount` INT( 11 ) NOT NULL default '0',
          `uses_per_coupon` INT( 11 ) NOT NULL default '0',
          `website_ids` text CHARACTER SET utf8 NOT NULL,
          `customer_group_ids` text NOT NULL,
          `activation_cnt`INT( 11 ) NOT NULL default '0',
        
          PRIMARY KEY (`coupon_id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
        
        
      CREATE TABLE IF NOT EXISTS {$this->getTable('points/coupon_transaction')} (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `coupon_id` int(11) NOT NULL,
        `transaction_id` int(11) NOT NULL,
        `customer_id` int(11) NOT NULL,
        PRIMARY KEY (`id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;
        
");

$installer->endSetup();