<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Groups Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @author     Webtex Dev Team <dev@webtex.com>
 */

$installer = $this;

$installer->startSetup();
$installer->run("

ALTER TABLE {$this->getTable('customergroupsprice/prices')} ADD `website_id` smallint(5) NULL;
ALTER TABLE {$this->getTable('customergroupsprice/special_prices')} ADD `website_id` smallint(5) NULL;
ALTER TABLE {$this->getTable('customergroupsprice/globalprices')} ADD `website_id` smallint(5) NULL;
ALTER TABLE {$this->getTable('customergroupsprice/attribute_prices')} ADD `website_id` smallint(5) NULL;

");

$installer->endSetup();;