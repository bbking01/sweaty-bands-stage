<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

$this->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('ugiftcert_cert')} (
`cert_id` int(10) unsigned NOT NULL auto_increment,
`cert_number` varchar(40) NOT NULL,
`balance` decimal(12,4) NOT NULL,
`pin` varchar(20) NOT NULL,
`pin_hash` varchar(40) NOT NULL,
`status` char(1) NOT NULL default 'P',
PRIMARY KEY  (`cert_id`),
KEY `KEY_cert_number` (`cert_number`),
KEY `KEY_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('ugiftcert_history')} (
 `history_id` int(10) unsigned NOT NULL auto_increment,
 `cert_id` int(10) unsigned NOT NULL,
 `action_code` varchar(20) NOT NULL,
 `ts` datetime NOT NULL,
 `amount` decimal(12,4) NOT NULL,
 `status` char(1) NOT NULL,
 `comments` text,
 `customer_id` int(10) unsigned default NULL,
 `customer_email` varchar(255) default NULL,
 `order_id` int(10) unsigned default NULL,
 `order_increment_id` varchar(50) default NULL,
 `user_id` mediumint(9) unsigned default NULL,
 `username` varchar(40) default NULL,
 PRIMARY KEY  (`history_id`),
 KEY `FK_ugiftcert_history` (`cert_id`),
 KEY `FK_ugiftcert_history_customer` (`customer_id`),
 KEY `FK_ugiftcert_history_order` (`order_id`),
 KEY `FK_ugiftcert_history_user` (`user_id`),
 CONSTRAINT `FK_ugiftcert_history` FOREIGN KEY (`cert_id`) REFERENCES `{$this->getTable('ugiftcert_cert')}` (`cert_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `FK_ugiftcert_history_customer` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE SET NULL ON UPDATE SET NULL,
 CONSTRAINT `FK_ugiftcert_history_order` FOREIGN KEY (`order_id`) REFERENCES `sales_order` (`entity_id`) ON DELETE SET NULL ON UPDATE SET NULL,
 CONSTRAINT `FK_ugiftcert_history_user` FOREIGN KEY (`user_id`) REFERENCES `{$this->getTable('admin_user')}` (`user_id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$gcTable = $this->getTable('ugiftcert_cert');
$this->getConnection()->addColumn($gcTable,'currency_code','char(3) NOT NULL');
$this->getConnection()->addColumn($gcTable,'expire_at','date default NULL');
$this->getConnection()->addColumn($gcTable,'recipient_name','varchar(127) default NULL');
$this->getConnection()->addColumn($gcTable,'recipient_email','varchar(127) default NULL');
$this->getConnection()->addColumn($gcTable,'recipient_address','text');
$this->getConnection()->addColumn($gcTable,'recipient_message','text');
$this->getConnection()->addColumn($gcTable,'toself_printed','tinyint not null');
$this->getConnection()->addColumn($gcTable,'store_id','smallint unsigned not null');
$this->getConnection()->addColumn($gcTable,'sender_name','varchar(100) not null');
$this->getConnection()->addColumn($gcTable,'conditions_serialized','mediumtext null');

$gchTable = $this->getTable('ugiftcert_history');
$this->getConnection()->addColumn($gchTable,'currency_code','char(3) NOT NULL');
$this->getConnection()->addColumn($gchTable,'order_item_id','int(10) unsigned default NULL');

$this->getConnection()->dropForeignKey($gchTable, 'FK_ugiftcert_history_order');

$this->getConnection()->addColumn($this->getTable('sales_flat_quote'), 'giftcert_code', 'varchar(100)');
    $this->getConnection()->addColumn($this->getTable('sales_flat_quote_address'), 'giftcert_amount', 'decimal(12,4)');
$this->getConnection()->addColumn($this->getTable('sales_flat_quote_address'), 'base_giftcert_amount', 'decimal(12,4)');


$eav = new Mage_Eav_Model_Entity_Setup('catalog_setup');
if ($eav->getAttributeId('catalog_product', 'ugiftcert_amount_config') === false) {
    $eav->addAttribute('catalog_product', 'ugiftcert_amount_config', array(
                                                                          'type' => 'text',
                                                                          'input' => 'textarea',
                                                                          'label' => 'GC Amount Configuration (leave empty for default configuration)',
                                                                          'global' => 2,
                                                                          'group' => 'Prices',
                                                                          'user_defined' => 1,
                                                                          'apply_to' => 'ugiftcert',
                                                                          'required' => 0,
                                                                     ));
}
if ($eav->getAttributeId('catalog_product', 'ugiftcert_email_template') === false) {
    $eav->addAttribute('catalog_product', 'ugiftcert_email_template', array(
                                                                           'type' => 'int',
                                                                           'input' => 'select',
                                                                           'label' => 'GC Email Template',
                                                                           'source' => 'ugiftcert/source_template',
                                                                           'global' => 0,
                                                                           'user_defined' => 1,
                                                                           'apply_to' => 'ugiftcert',
                                                                           'required' => 0,
                                                                      ));
}
if ($eav->getAttributeId('catalog_product', 'ugiftcert_email_template_self') === false) {
    $eav->addAttribute('catalog_product', 'ugiftcert_email_template_self', array(
                                                                                'type' => 'int',
                                                                                'input' => 'select',
                                                                                'label' => 'GC Email Template (Self)',
                                                                                'source' => 'ugiftcert/source_template',
                                                                                'global' => 0,
                                                                                'user_defined' => 1,
                                                                                'apply_to' => 'ugiftcert',
                                                                                'required' => 0,
                                                                           ));
}
if (version_compare(Mage::getVersion(), '1.3.0', '>=')) {
    $eav->updateAttribute('catalog_product', 'ugiftcert_amount_config', 'used_in_product_listing', 1);
}


$eav = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

$this->run("update {$this->getTable('eav_attribute')} set is_required=0 where entity_type_id={$eav->getEntityTypeId('catalog_product')} and attribute_code='ugiftcert_amount_config'");

$table = $this->getTable(version_compare(Mage::getVersion(), '1.4', '<') ? 'eav_attribute' : 'catalog_eav_attribute');
$attrId = $this->getConnection()->fetchOne("select attribute_id from {$this->getTable('eav_attribute')} where attribute_code='tax_class_id' and entity_type_id={$eav->getEntityTypeId('catalog_product')}");
$this->run("update {$table} set apply_to=if(not find_in_set('ugiftcert','apply_to'),concat(apply_to,',ugiftcert'),'') where attribute_id={$attrId}");

if (version_compare(Mage::getVersion(), '1.4', '>=')) {
    $this->run("update {$this->getTable('catalog_eav_attribute')} set apply_to=if(not find_in_set('ugiftcert','apply_to'),concat(apply_to,',ugiftcert'),'') where attribute_id in (select attribute_id from {$this->getTable('eav_attribute')} where entity_type_id={$eav->getEntityTypeId('catalog_product')} and attribute_code='weight')");
} else {
    $this->run("update {$this->getTable('eav_attribute')} set apply_to=if(not find_in_set('ugiftcert','apply_to'),concat(apply_to,',ugiftcert'),'') where entity_type_id={$eav->getEntityTypeId('catalog_product')} and attribute_code='weight'");
}

if ($eav->getAttributeId('order', 'giftcert_code') === false) {
    $eav->addAttribute('order', 'giftcert_code', array('type' => 'varchar'));
}
if ($eav->getAttributeId('order', 'giftcert_amount') === false) {
    $eav->addAttribute('order', 'giftcert_amount', array('type' => 'decimal'));
}
if ($eav->getAttributeId('order', 'base_giftcert_amount') === false) {
    $eav->addAttribute('order', 'base_giftcert_amount', array('type' => 'decimal'));
}

if ($eav->getAttributeId('order', 'giftcert_amount_invoiced') === false) {
    $eav->addAttribute('order', 'giftcert_amount_invoiced', array('type' => 'decimal'));
}
if ($eav->getAttributeId('order', 'base_giftcert_amount_invoiced') === false) {
    $eav->addAttribute('order', 'base_giftcert_amount_invoiced', array('type' => 'decimal'));
}
if ($eav->getAttributeId('order', 'giftcert_amount_credited') === false) {
    $eav->addAttribute('order', 'giftcert_amount_credited', array('type' => 'decimal'));
}

if ($eav->getAttributeId('order', 'base_giftcert_amount_credited') === false) {
    $eav->addAttribute('order', 'base_giftcert_amount_credited', array('type' => 'decimal'));
}

if ($eav->getAttributeId('invoice', 'giftcert_amount') === false) {
    $eav->addAttribute('invoice', 'giftcert_amount', array('type' => 'decimal'));
}
if ($eav->getAttributeId('invoice', 'base_giftcert_amount') === false) {
    $eav->addAttribute('invoice', 'base_giftcert_amount', array('type' => 'decimal'));
}
if ($eav->getAttributeId('creditmemo', 'giftcert_amount') === false) {
    $eav->addAttribute('creditmemo', 'giftcert_amount', array('type' => 'decimal'));
}
if ($eav->getAttributeId('creditmemo', 'base_giftcert_amount') === false) {
    $eav->addAttribute('creditmemo', 'base_giftcert_amount', array('type' => 'decimal'));
}


$table = $this->getTable('sales_flat_quote_address');
$this->getConnection()->addColumn($table, 'giftcert_code', 'varchar(255)');
$this->getConnection()->addColumn($table, 'base_giftcert_balances', 'varchar(255)');
$this->getConnection()->addColumn($table, 'giftcert_balances', 'varchar(255)');

$this->endSetup();
