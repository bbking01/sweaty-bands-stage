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

class Webtex_CustomerGroupsPrice_Block_Catalog_Product_View extends Mage_Catalog_Block_Product_View
{
    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function getJsonConfig()
    {
        $config = Mage::helper('core')->jsonDecode(parent::getJsonConfig());
        $_finalPrice = $this->getProduct()->getFinalPrice();

        $config['productPrice']  = Mage::helper('core')->currency($_finalPrice, false, false);

        return Mage::helper('core')->jsonEncode($config);
    }


    public function hasOptions()
    {
        if($this->helper('customergroupsprice')->isEnabled() && (($this->helper('customergroupsprice')->isHidePrice() && !$this->helper('customer')->isLoggedIn()))) {
            return false;
        }
        if ($this->getProduct()->getTypeInstance(true)->hasOptions($this->getProduct())) {
            return true;
        }
        return false;
    }

    protected function _toHtml()
    {
        if(!(!$this->helper('customergroupsprice')->isEnabled() || (!$this->helper('customergroupsprice')->isHidePrice() || $this->helper('customer')->isLoggedIn())) && ($this->getTemplate() == 'catalog/product/view/addtocart.phtml')) {
            return '';
        }
        return parent::_toHtml();
    }

}
