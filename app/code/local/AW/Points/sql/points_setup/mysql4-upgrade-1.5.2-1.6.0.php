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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


$this->startSetup();
$this->getConnection()->addColumn($this->getTable('points/summary'), 'last_birthday', 'DATETIME DEFAULT NULL AFTER `points_for_tags_granted`');
$this->getConnection()->addColumn($this->getTable('points/transaction'), 'order_id', 'INT UNSIGNED DEFAULT NULL AFTER `summary_id`');
$this->getConnection()->addColumn($this->getTable('points/transaction'), 'balance_change_type', 'INT UNSIGNED DEFAULT NULL AFTER `balance_change`');
$this->endSetup(); 