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
class Webtex_CustomerGroupsPrice_Helper_Data extends Mage_Core_Helper_Abstract {
    const XML_CUSTOMERGROUPSPRICE_ENABLED = 'webtex_catalog/customergroupsprice/enabled';
    const XML_CUSTOMERGROUPSPRICE_HIDE_PRICE = 'webtex_catalog/customergroupsprice/hide_price';
    const XML_CUSTOMERGROUPSPRICE_SHOW_SPECPRICE = 'webtex_catalog/customergroupsprice/special_price';
    const XML_CUSTOMERGROUPSPRICE_SCP = 'webtex_catalog/customergroupsprice/scp_price';
    const XML_CUSTOMERGROUPSPRICE_GROUPS = 'webtex_catalog/customergroupsprice/groups';

    public function getCustomerGroups() {
        $groupIds = explode(',', trim(Mage::getStoreConfig(self::XML_CUSTOMERGROUPSPRICE_GROUPS)));

        $groups = array();
        $groupCollection = Mage::getModel('customer/group')->getCollection();
        foreach ($groupCollection as $item) {
            if (array_search($item->getId(), $groupIds) !== false) {
                $groups[$item->getId()] = $item->getCustomerGroupCode();
            }
        }

        return $groups;
    }
    
    public function isEnabled() {
        return Mage::getStoreConfigFlag(self::XML_CUSTOMERGROUPSPRICE_ENABLED);
    }

    public function isUseScp() {
        return false;
        //return Mage::getStoreConfigFlag(self::XML_CUSTOMERGROUPSPRICE_SCP);
    }

    public function isHidePrice() {
        return Mage::getStoreConfigFlag(self::XML_CUSTOMERGROUPSPRICE_HIDE_PRICE);
    }

    public function isShowDefaultSpecialPrice() {
        return Mage::getStoreConfigFlag(self::XML_CUSTOMERGROUPSPRICE_SHOW_SPECPRICE);
    }

    public function isGroupActive($groupId) {
        if(!$this->isEnabled()) {
           return false;
        }
        $groups = $this->getCustomerGroups();
        return isset($groups[$groupId]);
    }

    public function getCustomPrice($product) {
        $prices = Mage::getModel('customergroupsprice/prices');
        $price = $prices->getProductPrice($product);

        return $price;
    }

}