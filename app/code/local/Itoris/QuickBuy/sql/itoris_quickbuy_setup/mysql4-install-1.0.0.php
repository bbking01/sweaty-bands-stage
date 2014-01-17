<?php 
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_QUICKBUY
 * @copyright  Copyright (c) 2012 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

$this->startSetup();
$enabled = Itoris_QuickBuy_Model_Settings::ENABLED;
$this->run("
	DROP TABLE IF EXISTS {$this->getTable('itoris_quickbuy_settings')};

	CREATE TABLE {$this->getTable('itoris_quickbuy_settings')} (
		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`scope` ENUM('default', 'website', 'store') NOT NULL ,
		`scope_id` INT UNSIGNED NOT NULL ,
		`key` VARCHAR( 255 ) NOT NULL ,
		`value` INT UNSIGNED NOT NULL ,
		`type` ENUM('text', 'default') NULL,
		UNIQUE(`scope`, `scope_id`, `key`)
	) ENGINE = InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

	INSERT INTO {$this->getTable('itoris_quickbuy_settings')} (`scope`, `scope_id`, `key`, `value`, `type`) VALUES
		('default', 0, 'enable', {$enabled}, 'default');
");

$this->endSetup();
?>