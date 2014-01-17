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
class Webtex_CustomerGroupsPrice_Block_Catalog_Product_View_Options extends Mage_Catalog_Block_Product_View_Options
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Get option html block
     *
     * @param Mage_Catalog_Model_Product_Option $option
     */
    public function getOptionHtml(Mage_Catalog_Model_Product_Option $option)
    {
        if(!(!$this->helper('customergroupsprice')->isEnabled() || (!$this->helper('customergroupsprice')->isHidePrice() || $this->helper('customer')->isLoggedIn()))) {
            return '';
        }
        parent::getOptionHtml($option);
    }
}