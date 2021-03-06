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
 * @package    AW_Popup
 * @version    1.2.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

$this->startSetup();
$query="ALTER TABLE {$this->getTable('popup/popup')}
    ADD `show_count` smallint NOT NULL DEFAULT '0',
    ADD `use_count` smallint NOT NULL DEFAULT '0',
    ADD `show_count_per_customer` smallint NOT NULL DEFAULT '0',
    ADD `mss_rule_id` INT NOT NULL COMMENT  'Market Segmentation Suite Rule Id';

    CREATE TABLE {$this->getTable('popup/stat')} (
    `stat_id` int(11) unsigned NOT NULL auto_increment,
    `popup_id` int(11) UNSIGNED  NULL,
    `customer_id` int(11) UNSIGNED NULL,
    `session_id` varchar(64) NULL,
    PRIMARY KEY (`stat_id`),
    FOREIGN KEY (`popup_id`) REFERENCES {$this->getTable('popup/popup')} (`popup_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
$this->run($query);
$this->endSetup();