<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2012 Vladimir Popov
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$webforms_table = 'webforms';

$edition = 'CE';
$version = explode('.', Mage::getVersion());
if ($version[1] >= 9)
    $edition = 'EE';

if((float)substr(Mage::getVersion(),0,3)>1.1 || $edition == 'EE')
    $webforms_table = $this->getTable('webforms/webforms');

$installer->run("
ALTER TABLE  `{$webforms_table}` ADD  `crf_account`  tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE  `{$webforms_table}` ADD  `crf_account_position`  int(11) NOT NULL DEFAULT '10';
ALTER TABLE  `{$webforms_table}` ADD  `crf_account_frontend`  tinyint(1) NOT NULL DEFAULT '1';
ALTER TABLE  `{$webforms_table}` ADD  `crf_account_group_serialized`  text NOT NULL;
");

$installer->endSetup();