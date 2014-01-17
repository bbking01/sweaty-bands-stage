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
$this->run("
-- DROP TABLE IF EXISTS {$this->getTable('popup/popup')};
CREATE TABLE {$this->getTable('popup/popup')} (
    `popup_id` int(11) unsigned NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `title` varchar(255) NULL,
    `popup_content` text NOT NULL default '',
    `status` smallint(6) NOT NULL default '0',
    `show_at` varchar(255) NOT NULL default '',
    `store_view` varchar(255) NOT NULL default '0',
    `date_from` date NULL,
    `date_to` date NULL,
    `align`  smallint(6) NOT NULL,
    `sort_order`  smallint(6) NOT NULL,
    `width`  smallint(6) NULL,
    `height` smallint(6) NULL,
    PRIMARY KEY (`popup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
$this->endSetup();