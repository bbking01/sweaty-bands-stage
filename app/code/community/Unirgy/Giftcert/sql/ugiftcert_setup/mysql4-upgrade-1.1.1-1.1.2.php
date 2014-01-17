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

$eav = new Mage_Sales_Model_Mysql4_Setup('catalog_setup');

if (version_compare(Mage::getVersion(), '1.4', '>=')) {
    $this->run("UPDATE {$this->getTable('catalog_eav_attribute')} SET apply_to=IF(NOT FIND_IN_SET('ugiftcert','apply_to'),CONCAT(apply_to,',ugiftcert'),apply_to) WHERE attribute_id IN (SELECT attribute_id FROM {$this->getTable('eav_attribute')} WHERE entity_type_id={$eav->getEntityTypeId('catalog_product')} AND attribute_code='price')");
} else {
    $this->run("update {$this->getTable('eav_attribute')} SET apply_to=IF(NOT FIND_IN_SET('ugiftcert','apply_to'),CONCAT(apply_to,',ugiftcert'),apply_to) WHERE entity_type_id={$eav->getEntityTypeId('catalog_product')} AND attribute_code='price'");
}

$gcTable = $this->getTable('ugiftcert_cert');
$this->getConnection()->addColumn($gcTable,'pdf_settings','TEXT NULL');

$eav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
$eav->addAttribute('catalog_product', 'ugiftcert_pdf_settings',
                   array(
                       'type' => 'text',
                       'label' => 'GC PDF Configuration (leave empty for default configuration)',
                       'backend_model' => 'adminhtml/system_config_backend_serialized_array',
                       'group' => 'GC Settings',
                       'user_defined' => 1,
                       'apply_to' => 'ugiftcert',
                       'required' => 0,
                       'global' => 0,
                   )
);

$this->endSetup();