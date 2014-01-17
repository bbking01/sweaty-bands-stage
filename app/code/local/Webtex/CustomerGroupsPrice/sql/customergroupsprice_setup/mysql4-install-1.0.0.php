<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Groups Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @author     Webtex Dev Team <dev@webtex.com>
 */

/* @var $installer Webtex_CustomerGroupsPrice_Model_Mysql4_Setup */
$installer = $this;
$installer->installEntities();
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('customergroupsprice/prices')};
CREATE TABLE IF NOT EXISTS {$this->getTable('customergroupsprice/prices')} (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
  `group_id` SMALLINT(3) UNSIGNED NOT NULL,
  `product_id` INTEGER UNSIGNED NOT NULL,
  `price` DECIMAL(11,2) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_WEBTEX_CGP_PRICES_PRODUCTS` FOREIGN KEY (`product_id`) REFERENCES `{$installer->getTable('catalog/product')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_WEBTEX_CGP_PRICES_CUSTOMER_GROUPS` FOREIGN KEY (`group_id`) REFERENCES `{$installer->getTable('customer_group')}` (`customer_group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();