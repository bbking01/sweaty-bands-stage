<?php
/**
 * Grow Development - Store Locations Pro
 *
 * @category   Growdevelopment
 * @package    Growdevelopment_StoreLocations
 * @copyright  Copyright (c) 2012 Grow Development (http://www.growdevelopment.com)
 * @license    http://www.growdevelopment.com/docs/eula.txt
 * @author     Daniel Espinoza <modules@growdevelopment.com>
 *
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// Add street2 column
$installer->getConnection()->addColumn($installer->getTable('growdevelopment_storelocation'), 'street2', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'default' => '',
    'comment' => 'Street (cont.)'
));

// Add fax column
$installer->getConnection()->addColumn($installer->getTable('growdevelopment_storelocation'), 'fax', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'default' => '',
    'comment' => 'Fax number'
));


// Add email column
$installer->getConnection()->addColumn($installer->getTable('growdevelopment_storelocation'), 'email', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 255,
    'default' => '',
    'comment' => 'Email address'
));

// Add opening hours column
$installer->getConnection()->addColumn($installer->getTable('growdevelopment_storelocation'), 'opening_hours', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'default' => '',
    'comment' => 'Opening hours'
));

$installer->endSetup();