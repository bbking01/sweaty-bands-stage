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
class Webtex_CustomerGroupsPrice_Model_Specialprices extends Mage_Core_Model_Abstract {

    protected static $_url = null;
    protected $_product = null;
    protected $_pricesCollection = null;

    public function _construct() {
        parent::_construct();
        $this->_init('customergroupsprice/specialprices');
    }

    public function getPricesCollection($productId,$websiteId) {
        if (is_null($this->_pricesCollection)) {
            $this->_pricesCollection = Mage::getResourceModel('customergroupsprice/specialprices_collection')
                            ->addProductFilter($productId)
                            ->addWebsiteFilter($websiteId);
        }

        return $this->_pricesCollection;
    }

    public function deleteByProduct($productId, $websiteId) {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $connection->delete($tablePrefix . 'customergroupsprice_special_prices', 'product_id = ' . $productId. ' and website_id = ' . $websiteId );
    }

    public function getProductPrice($product) {

        if (!Mage::helper('customer')->isLoggedIn() || !($product->getId())) {
            return $product->getSpecialPrice();
        }

        $store = Mage::app()->getStore();
        $specialPriceFrom = $product->getSpecialFromDate();
        $specialPriceTo   = $product->getSpecialToDate();
        if (!Mage::app()->getLocale()->isStoreDateInInterval($store, $specialPriceFrom, $specialPriceTo)) {
            return ;
        }

        $websiteId  = Mage::app()->getStore()->getWebsiteId();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        $groupId = Mage::helper('customer')->getCustomer()->getGroupId();

        if (Mage::helper('customergroupsprice')->isGroupActive($groupId)) {
            $query = $connection->select()->from($tablePrefix . 'customergroupsprice_special_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = ' . $websiteId);
            $row = $connection->fetchRow($query);

            
            if(isset($row['price']) && $row['price'] != '' && $row['price'] * 1 > 0) {
                return $row['price'];
            } else {

                $query = $connection->select()->from($tablePrefix . 'customergroupsprice_special_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = 0');
                $row = $connection->fetchRow($query);

            
                if(isset($row['price']) && $row['price'] != '' && $row['price'] * 1 > 0) {
                    return $row['price'];
                } else {
                    if(Mage::helper('customergroupsprice')->isShowDefaultSpecialPrice()) {
                        return $product->getSpecialPrice();
                    } else {
                        $product->setSpecialPrice(null);
                        return null;
                    }
                }
            }
        } else {
            if(Mage::helper('customergroupsprice')->isShowDefaultSpecialPrice()) {
                return $product->getSpecialPrice();
            } else {
                $product->setSpecialPrice(null);
                return null;
            }
        }
    }

    public function getGroupProductPrice($product, $groupId) {

        $store = Mage::app()->getStore();
        $specialPriceFrom = $product->getSpecialFromDate();
        $specialPriceTo   = $product->getSpecialToDate();
        if (!Mage::app()->getLocale()->isStoreDateInInterval($store, $specialPriceFrom, $specialPriceTo)) {
            return ;
        }

        $websiteId  = Mage::app()->getStore()->getWebsiteId();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();

        if (Mage::helper('customergroupsprice')->isGroupActive($groupId)) {
            $query = $connection->select()->from($tablePrefix . 'customergroupsprice_special_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = ' . $websiteId);
            $row = $connection->fetchRow($query);

            if(isset($row['price']) && $row['price'] != '' && $row['price'] * 1 > 0) {
                return $row['price'];
            } else {

                $query = $connection->select()->from($tablePrefix . 'customergroupsprice_special_prices')
                            ->where('product_id = ' . $product->getId() . ' AND group_id = ' . $groupId . ' AND website_id = 0');
                $row = $connection->fetchRow($query);

                if(isset($row['price']) && $row['price'] != '' && $row['price'] * 1 > 0) {
                    return $row['price'];
                } else {
            
                    if(Mage::helper('customergroupsprice')->isShowDefaultSpecialPrice()) {
                        return $product->getSpecialPrice();
                    } else {
                        $product->setSpecialPrice(null);
                        return null;
                    }
                }
            }
        } else {
            if(Mage::helper('customergroupsprice')->isShowDefaultSpecialPrice()) {
                return $product->getSpecialPrice();
            } else {
                $product->setSpecialPrice(null);
                return null;
            }
        }
    }

	public function loadByGroup($productId, $groupId, $website = 0)
	{
        if(!$productId){
            return $this;
        }
        $store = Mage::app()->getStore();
        $product = Mage::getModel('catalog/product')->load($productId);
        $specialPriceFrom = $product->getSpecialFromDate();
        $specialPriceTo   = $product->getSpecialToDate();
        if (!Mage::app()->getLocale()->isStoreDateInInterval($store, $specialPriceFrom, $specialPriceTo) && Mage::app()->getStore()->getId() != 0) {
            return ;
        }
        if($website == 0){
	        $this->setData($this->getResource()->loadByGroup($productId, $groupId,Mage::app()->getStore()->getWebsiteId()));
	    } else {
	        $this->setData($this->getResource()->loadByGroup($productId, $groupId,$website));
	    }
        return $this;
	}
}