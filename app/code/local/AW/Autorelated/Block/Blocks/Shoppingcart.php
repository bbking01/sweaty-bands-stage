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


class AW_Autorelated_Block_Blocks_Shoppingcart extends AW_Autorelated_Block_Blocks_Abstract
{
    protected $_canShow = null;
    protected $_checkoutCart = null;
    protected $_storeId = null;

    /**
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCheckoutCart()
    {
        if ($this->_checkoutCart === null) {
            /** @var $checkoutCart Mage_Checkout_Model_Cart */
            $checkoutCart = Mage::getSingleton('checkout/cart');
            if ($quoteId = $this->getData('_quote_id')) {
                /** @var $quote Mage_Sales_Model_Quote */
                $quote = Mage::getModel('sales/quote');
                if (method_exists($quote, 'loadByIdWithoutStore')) {
                    $quote->loadByIdWithoutStore($quoteId);
                } else {
                    $quote->setStoreId(Mage::app()->getDefaultStoreView())->load($quoteId);
                }
                if ($quote->getId()) {
                    $checkoutCart = Mage::getModel('checkout/cart')->setQuote($quote);
                }
            }
            foreach ($checkoutCart->getQuote()->getAllItems() as $item) {
                /** @var $item Mage_Sales_Model_Quote_Item */
                $item->getProduct()->load($item->getProductId());
            }
            $this->_checkoutCart = $checkoutCart;
        }
        return $this->_checkoutCart;
    }

    protected function _getCheckoutCartProductIds()
    {
        $productIds = array();
        if ($this->_getCheckoutCart()) {
            foreach ($this->_getCheckoutCart()->getQuote()->getItemsCollection() as $quoteItem) {
                /** @var $quoteItem Mage_Sales_Model_Quote_Item */
                $productIds[] = $quoteItem->getProductId();
            }
        }
        return $productIds;
    }

    protected function _getStoreId()
    {
        if ($this->_storeId === null) {
            $this->_storeId = $this->_getCheckoutCart() ? $this->_getCheckoutCart()->getQuote()->getStore()->getId() : parent::_getStoreId();
        }
        return $this->_storeId;
    }

    protected function _getConditions($key)
    {
        return ($conditions = $this->getData($key . '/conditions')) ? $conditions : array();
    }

    protected function _getWebsiteId()
    {
        try {
            $store = Mage::app()->getStore($this->_getStoreId());
            $websiteId = $store->getWebsiteId();
        } catch (Exception $ex) {
            $websiteId = Mage::app()->getStore()->getWebsiteId();
        }
        return $websiteId;
    }

    public function canShow()
    {
        if ($this->_canShow === null) {
            $shoppingCart = $this->_getCheckoutCart();
            if ($shoppingCart->getItemsCount()) {
                /** @var $model AW_Autorelated_Model_Blocks_Shoppingcart_Ruleviewed */
                $model = Mage::getModel('awautorelated/blocks_shoppingcart_ruleviewed');
                $model->setWebsiteIds($this->_getWebsiteId());
                $model->getConditions()->loadArray($this->_getConditions('currently_viewed'), 'viewed');
                $quote = $shoppingCart->getQuote();
                if ($quote instanceof Mage_Sales_Model_Quote_Address_Item) {
                    $address = $quote->getAddress();
                } elseif ($quote instanceof Mage_Sales_Model_Quote) {
                    if ($quote->isVirtual()) {
                        $address = $quote->getBillingAddress();
                    } else {
                        $address = $quote->getShippingAddress();
                    }
                } elseif ($quote->getQuote()->isVirtual()) {
                    $address = $quote->getQuote()->getBillingAddress();
                } else {
                    $address = $quote->getQuote()->getShippingAddress();
                }
                $this->_canShow = $model->validate($address);
            } else {
                $this->_canShow = false;
            }
        }
        return $this->_canShow;
    }

    protected function _setTemplate()
    {
        if (!$this->getTemplate()) {
            switch ($this->getBlockPosition()) {
                case AW_Autorelated_Model_Source_Position::REPLACE_CROSSSELS_BLOCK:
                    $this->setTemplate('aw_autorelated/blocks/shoppingcart/crosssells.phtml');
                    break;
                default:
                    $this->setTemplate('aw_autorelated/blocks/shoppingcart/block.phtml');
                    break;
            }
        }
        return $this;
    }

    protected function _getShoppingCartProductsAttributeConditions($attrName, $attrCondition)
    {
        $attributeConditions = array();
        foreach ($this->_getCheckoutCart()->getQuote()->getAllItems() as $quoteItem) {
            /** @var $quoteItem Mage_Sales_Model_Quote_Item */
            $product = $quoteItem->getProduct();
            if ($product->getId() && $attrValue = $product->getData($attrName)) {
                if (in_array($attrCondition, array('like', 'nlike'))) {
                    $attrValue = '%' . $attrValue . '%';
                }
                $attributeConditions[] = array($attrCondition => $attrValue);
            }
        }
        return $attributeConditions;
    }

    protected function _getFilteredByOptionsIds()
    {
        $options = $this->_getRelatedProducts()->getData('options');
        if ($options) {
            /** @var $productCollection AW_Autorelated_Model_Product_Collection */
            $productCollection = Mage::getModel('awautorelated/product_collection');
            $productCollection->setStoreId($this->_getStoreId());
            $isFiltered = false;
            foreach ($options as $option) {
                $attributeConditions = $this->_getShoppingCartProductsAttributeConditions($option['ATTR'], $option['CONDITION']);
                if ($attributeConditions) {
                    $isFiltered = true;
                    switch ($option['ATTR']) {
                        case 'price':
                            $productCollection->addPriceAttributeToFilter('final_price', $attributeConditions);
                            break;
                        default:
                            $productCollection->addAttributeToFilter($option['ATTR'], $attributeConditions);
                    }
                }
            }
            if (!$isFiltered) {
                return array();
            }
            $filteredIds = array_intersect($this->_collection->getAllIds(), $productCollection->getAllIds());
        } else {
            $filteredIds = $this->_collection->getAllIds();
        }
        return $filteredIds;
    }

    protected function _getFilteredIdsByConditions()
    {
        /** @var $rule AW_Autorelated_Model_Blocks_Shoppingcart_Rulerelated */
        $rule = Mage::getModel('awautorelated/blocks_shoppingcart_rulerelated');
        $rule->setReturnMode(AW_Autorelated_Model_Blocks_Rule::ALL_IDS_ON_NO_CONDITIONS);
        $rule->getConditions()->loadArray($this->_getRelatedProducts()->getData('conditions'), 'related');
        $rule->setWebsiteIds(Mage::app()->getWebsite()->getId());
        return $rule->getMatchingProductIds();
    }

    protected function _getRelatedIds()
    {
        $relatedIds = array();
        $filteredByOptionsIds = $this->_getFilteredByOptionsIds();
        if ($filteredByOptionsIds) {
            $relatedIds = array_intersect($filteredByOptionsIds, $this->_getFilteredIdsByConditions());
        }
        if ($relatedIds) {
            $relatedIds = array_diff($relatedIds, $this->_getCheckoutCartProductIds());
        }
        return $relatedIds;
    }

    protected function _renderRelatedProductsFilters()
    {
        $limit = $this->_getRelatedProducts()->getData('count');
        $relatedIds = $this->_getRelatedIds();
        if ($relatedIds) {
            $relatedIds = $this->_preorderIds($relatedIds);
            $this->_initCollectionForIds($relatedIds);
            $this->_collection->setPageSize($limit);
            $this->_collection->setCurPage(1);
            $this->_orderRelatedProductsCollection($this->_collection);
        } else {
            $this->_collection = null;
        }
        return $this;
    }
}
