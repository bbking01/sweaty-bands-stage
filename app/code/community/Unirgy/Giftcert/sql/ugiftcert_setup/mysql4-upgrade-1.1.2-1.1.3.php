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

$eav = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
$eav->updateAttribute('catalog_product', 'ugiftcert_pdf_settings', 'frontend_input', 'ugiftcert_pdf');
$eav->updateAttribute('catalog_product', 'ugiftcert_pdf_settings', 'backend_type', 'text');
$eav->addAttribute('catalog_product', 'ugiftcert_conditions',
                   array(
                        'type' => 'text',
                        'input' => 'ugiftcert_cond',
                        'label' => 'GC conditions',
                        'group' => 'GC Settings',
                        'user_defined' => 1,
                        'apply_to' => 'ugiftcert',
                        'required' => 0,
                        'global' => 0,
                   )
);
$this->endSetup();