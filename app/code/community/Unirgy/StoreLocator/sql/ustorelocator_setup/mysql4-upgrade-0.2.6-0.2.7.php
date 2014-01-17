<?php
/**
 * Unirgy_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_StoreLocator
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_StoreLocator
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
/* @var $this Unirgy_StoreLocator_Model_Resource_Setup */

$this->startSetup();
$table = $this->getTable('ustorelocator_location');
$conn = $this->getConnection();
$conn->addColumn($table, 'country', 'VARCHAR( 100 ) NULL');
$conn->addColumn($table, 'stores', 'VARCHAR( 100 ) NULL');
$conn->addColumn($table, 'icon', 'VARCHAR( 255 ) NULL');
$conn->addColumn($table, 'use_label', 'TINYINT ( 1 ) default 1 NOT NULL');
$conn->addColumn($table, 'is_featured', 'TINYINT ( 1 ) default 0 NOT NULL');
$conn->addColumn($table, 'zoom', 'TINYINT ( 2 ) default 10 NOT NULL');

$this->endSetup();
