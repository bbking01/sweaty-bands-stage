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
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Autorelated_Helper_Data extends Mage_Core_Helper_Abstract
{
    const REGISTRY_ABSTRACT_BLOCK = 'awautorelated_product_abstract_block';
    const REGISTRYSTORAGE_FILES = 'awarp_blocks_storage_files';
    const FILE_ADDED = 'added';
    const FILE_USED = 'used';

    /**
     * Compare param $version with magento version
     * @param string $version Version to compare
     * @return boolean
     */
    public function checkVersion($version, $operator = '>=')
    {
        return version_compare(Mage::getVersion(), $version, $operator);
    }

    public function removeEmptyItems($var)
    {
        return !empty($var);
    }

    public function prepareArray($var)
    {
        if (is_string($var))
            $var = @explode(',', $var);
        if (is_array($var)) {
            $var = array_unique($var);
            $var = array_filter($var, array($this, 'removeEmptyItems'));
            $var = @implode(',', $var);
        }
        return $var;
    }

    public function convertFlatToRecursive(array $rule, $keys)
    {
        $arr = array();
        foreach ($rule as $key => $value) {
            if (in_array($key, $keys) && is_array($value)) {
                foreach ($value as $id => $data) {

                    $path = explode('--', $id);
                    $node = & $arr;
                    for ($i = 0, $l = sizeof($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = array();
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        if (isset($data['attribute']) && ($data['attribute'] == 'sku')) {
                            if ($k == 'value') {
                                $v = preg_replace("#,\s{1}#is", ",", $v);
                            }
                        }

                        $node[$k] = $v;
                    }
                }
            } else {
                /**
                 * convert dates into Zend_Date
                 */
                if (in_array($key, array('from_date', 'to_date')) && $value) {
                    $value = Mage::app()->getLocale()->date(
                        $value, Varien_Date::DATE_INTERNAL_FORMAT, null, false
                    );
                }
            }
        }

        return $arr;
    }

    public function isEditAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/awautorelated/new');
    }

    public function isViewAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/awautorelated/manage');
    }

    public function getCurrentUserGroup()
    {
        return Mage::getSingleton('customer/session')->getCustomerGroupId();
    }

    public function getAbstractProductBlock()
    {
        $_abstractBlock = Mage::registry(self::REGISTRY_ABSTRACT_BLOCK);
        if (!$_abstractBlock) {
            $_abstractBlock = Mage::getSingleton('core/layout')->createBlock('catalog/product_list_related');
            $_abstractBlock->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
            $_abstractBlock->addPriceBlockType('giftcard', 'enterprise_giftcard/catalog_product_price', 'giftcard/catalog/product/price.phtml');
            $_abstractBlock->addPriceBlockType('msrp', 'catalog/product_price', 'catalog/product/price_msrp.phtml');
            $_abstractBlock->addPriceBlockType('msrp_item', 'catalog/product_price', 'catalog/product/price_msrp_item.phtml');
            $_abstractBlock->addPriceBlockType('msrp_noform', 'catalog/product_price', 'catalog/product/price_msrp_noform.phtml');
            Mage::register(self::REGISTRY_ABSTRACT_BLOCK, $_abstractBlock);
        }
        return $_abstractBlock;
    }

    public function updateChild($array, $from, $to)
    {
        foreach ($array as $k => $rule) {
            foreach ($rule as $name => $param) {
                if ($name == 'type' && $param == $from)
                    $array[$k][$name] = $to;
            }
        }
        return $array;
    }

    public function getExtDisabled()
    {
        return Mage::getStoreConfig('advanced/modules_disable_output/AW_Autorelated');
    }

    /**
     * @return Array of all productIds in current customer's wishlist
     */
    public function getWishlistProductsIds()
    {
        $ids = array();
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            try {
                $resource = Mage::getSingleton('core/resource');
                $wishlistTable = $resource->getTableName('wishlist');
                $wishlistItemTable = $resource->getTableName('wishlist_item');

                $db = $resource->getConnection('core_read');
                $query = $db->select()
                    ->from(array('w' => $wishlistTable), array('wishlist_id'))
                    ->where('customer_id = ?', $session->getCustomer()->getId());
                $query2 = $db->select()
                    ->from(array('i' => $wishlistItemTable), array('product_id'))
                    ->where('i.store_id = ?', Mage::app()->getStore()->getId())
                    ->join(array('w' => $query), 'w.wishlist_id = i.wishlist_id', array());
                $ids = $db->fetchCol($query2);
            } catch (Exception $e) {

            }
        }
        return $ids;
    }
}
