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

$table = $this->getTable('ugiftcert/cert');
$this->getConnection()->addColumn($table, 'template','INT(10) NULL');
$this->getConnection()->addColumn($table, 'template_self','INT(10) NULL');
$eav = new Mage_Eav_Model_Entity_Setup('catalog_setup');
// add email templates to gc settings group to be available for editing. Limit product type and change scope to store
$eav->addAttributeToGroup('catalog_product', $eav->getDefaultAttributeSetId('catalog_product'), 'GC Settings', 'ugiftcert_email_template');
$eav->addAttributeToGroup('catalog_product', $eav->getDefaultAttributeSetId('catalog_product'), 'GC Settings', 'ugiftcert_email_template_self');
$eav->updateAttribute('catalog_product', 'ugiftcert_email_template', array('apply_to' => 'ugiftcert', 'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE));
$eav->updateAttribute('catalog_product', 'ugiftcert_email_template_self', array('apply_to' => 'ugiftcert', 'is_global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE));

$this->endSetup();