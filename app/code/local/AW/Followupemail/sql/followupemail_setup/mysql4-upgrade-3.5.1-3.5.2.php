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
 * @package    AW_Followupemail
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

$installer = $this;
$installer->startSetup();
try {
    $installer->run("
        CREATE TABLE IF NOT EXISTS {$this->getTable('followupemail/unsubscribe')} (
          `id` int(11) unsigned NOT NULL auto_increment,
          `customer_id` int(11) NOT NULL,
          `customer_email` varchar(255) NOT NULL,
          `store_id` varchar(255) NOT NULL,
          `rule_id` varchar(128) NOT NULL default '',
          `is_unsubscribed` tinyint(1) NOT NULL default '0',
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

        ALTER TABLE {$this->getTable('followupemail/rule')} MODIFY `sku` TEXT;
        ALTER TABLE {$this->getTable('followupemail/queue')} ADD `template_styles` TEXT NULL AFTER `content`;
    ");
} catch (Exception $e) {
    Mage::logException($e);
}
$installer->endSetup();

// template loader
require 'installtemplates.php';