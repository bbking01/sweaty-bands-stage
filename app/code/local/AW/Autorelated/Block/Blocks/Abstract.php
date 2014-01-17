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


abstract class AW_Autorelated_Block_Blocks_Abstract extends Mage_Core_Block_Template
{
    /** @var $_collection AW_Autorelated_Model_Product_Collection */
    protected $_collection = null;

    protected function _getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    protected function _initCollection()
    {
        if ($this->_collection === null) {
            $this->_collection = Mage::getModel('awautorelated/product_collection');
            $this->_collection->addAttributeToSelect('*');

            $_visibility = array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
            );

            $this->_collection->addAttributeToFilter('visibility', $_visibility)->addAttributeToFilter('status', array("in" => Mage::getSingleton("catalog/product_status")->getVisibleStatusIds()));

            if (!$this->_getShowOutOfStock()) {
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_collection);
            }

            $this->_collection->addStoreFilter($this->_getStoreId())->joinCategoriesByProduct()->groupByAttribute('entity_id');
        }
        return $this->_collection;
    }

    protected function _initCollectionForIds(array $ids)
    {
        unset($this->_collection);
        $this->_collection = Mage::getModel('awautorelated/product_collection');
        $this->_collection->addAttributeToSelect('*')
            ->addFilterByIds($ids)
            ->setStoreId($this->_getStoreId());
        return $this->_collection;
    }

    protected function _getShowOutOfStock()
    {
        return $this->getData('related_products') instanceof Varien_Object
            && $this->getData('related_products')->getData('show_out_of_stock');
    }

    /**
     * @return AW_Autorelated_Model_Product_Collection
     */
    public function getCollection()
    {
        if ($this->canShow()) {
            if ($this->_collection === null) {
                $this->_initCollection();
                $this->_renderRelatedProductsFilters();
                $this->_postProcessCollection();
            }
            return $this->_collection;
        }
        return null;
    }

    public function isLocationLink($product)
    {
        $types = array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
            Mage_Catalog_Model_Product_Type::TYPE_GROUPED);

        if (in_array($product->getTypeId(), $types)) {
            return false;
        }

        return true;
    }

    protected function _postProcessCollection()
    {
        if ($this->_collection instanceof AW_Autorelated_Model_Product_Collection) {
            $this->_collection->setStoreId($this->_getStoreId())
                ->addUrlRewrites()
                ->addMinimalPrice()
                ->groupByAttribute('entity_id');
            if ($this->_getShowOutOfStock() && !Mage::helper('cataloginventory')->isShowOutOfStock()) {
                $fromPart = $this->_collection->getSelect()->getPart(Zend_Db_Select::FROM);
                if (isset($fromPart['price_index'])
                    && is_array($fromPart['price_index'])
                    && isset($fromPart['price_index']['joinType'])
                    && $fromPart['price_index']['joinType'] === Zend_Db_Select::INNER_JOIN
                ) {
                    $fromPart['price_index']['joinType'] = Zend_Db_Select::LEFT_JOIN;
                    $this->_collection->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
                }
            }
        }
        return $this;
    }

    public function getBlockPosition()
    {
        return $this->getParent() && $this->getParent()->getBlockPosition() ? $this->getParent()->getBlockPosition() : null;
    }

    protected function _getCurrentlyViewed()
    {
        return $this->getData('currently_viewed') ? $this->getData('currently_viewed') : null;
    }

    protected function _getRelatedProducts()
    {
        return $this->getData('related_products') ? $this->getData('related_products') : null;
    }

    protected function _getRelatedProductsOrder()
    {
        if (!$this->_getData('_rp_order')) {
            $rpOrder = array(
                'type' => AW_Autorelated_Model_Source_Block_Common_Order::NONE
            );
            if (($relatedProducts = $this->_getRelatedProducts())
                && is_array($order = $relatedProducts->getData('order'))
            ) {
                $rpOrder = $order;
            }
            $this->setData('_rp_order', new Varien_Object($rpOrder));
        }
        return $this->_getData('_rp_order');
    }

    protected function _preorderIds(array $ids)
    {
        if ($this->_getRelatedProductsOrder()->getData('type') == AW_Autorelated_Model_Source_Block_Common_Order::RANDOM) {
            shuffle($ids);
            $ids = array_values($ids);
        }
        return $ids;
    }

    protected function _orderRelatedProductsCollection($collection)
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $orderSettings = $this->_getRelatedProductsOrder();
        switch ($orderSettings->getData('type')) {
            case AW_Autorelated_Model_Source_Block_Common_Order::RANDOM:
                $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
                break;
            case AW_Autorelated_Model_Source_Block_Common_Order::BY_ATTRIBUTE:
                $collection->addAttributeToSort(
                    $orderSettings->getData('attribute'),
                    $orderSettings->getData('direction')
                );
                break;
        }
        return $this;
    }

    protected function _beforeToHtml()
    {
        $this->_setTemplate();
        return parent::_beforeToHtml();
    }

    abstract protected function _setTemplate();

    abstract protected function _renderRelatedProductsFilters();

    abstract public function canShow();
}
