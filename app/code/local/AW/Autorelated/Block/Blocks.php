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


class AW_Autorelated_Block_Blocks extends Mage_Core_Block_Template
{
    protected $_blockPosition = null;
    protected $_blocks = null;
    protected $_blockType = null;

    public function setBlockPosition($position)
    {
        $this->_blockPosition = $position;
        return $this;
    }

    public function getBlockPosition()
    {
        if ($this->_blockPosition === null) {
            switch ($this->getNameInLayout()) {
                case 'awarp.content.top.category':
                    $this->_blockPosition = AW_Autorelated_Model_Source_Position::BEFORE_CONTENT;
                    $this->_blockType = AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK;
                    break;
                case 'awarp.content.top.product':
                    $this->_blockPosition = AW_Autorelated_Model_Source_Position::BEFORE_CONTENT;
                    $this->_blockType = AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK;
                    break;
                case 'awarp.content.instead.native':
                    $this->_blockPosition = AW_Autorelated_Model_Source_Position::INSTEAD_NATIVE_RELATED_BLOCK;
                    break;
                case 'awarp.content.under.native':
                    $this->_blockPosition = AW_Autorelated_Model_Source_Position::UNDER_NATIVE_RELATED_BLOCK;
                    break;
                case 'awarp.content.inside.product':
                    $this->_blockPosition = AW_Autorelated_Model_Source_Position::INSIDE_PRODUCT_PAGE;
                    break;
                case 'awarp.shc.before.content':
                    $this->_blockPosition = AW_Autorelated_Model_Source_Position::BEFORE_CONTENT;
                    $this->_blockType = AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK;
                    break;
                case 'aw.arp2.shc.crosssells':
                    $this->_blockPosition = AW_Autorelated_Model_Source_Position::REPLACE_CROSSSELS_BLOCK;
                    $this->_blockType = AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK;
                    break;
                default:
                    $this->_blockPosition = AW_Autorelated_Model_Source_Position::CUSTOM;
                    break;
            }
        }
        return $this->_blockPosition;
    }

    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('aw_autorelated/blocks.phtml');
        }
        return parent::_beforeToHtml();
    }

    protected function getBlocks()
    {
        $helper = Mage::helper('awautorelated');
        if ($this->_blocks === null) {
            if ($this->getBlockPosition() != AW_Autorelated_Model_Source_Position::CUSTOM) {
                $collection = Mage::getModel('awautorelated/blocks')->getCollection();
                $collection->addStoreFilter()
                    ->addPositionFilter($this->getBlockPosition())
                    ->addStatusFilter()
                    ->addCustomerGroupFilter($helper->getCurrentUserGroup())
                    ->addDateFilter()
                    ->setPriorityOrder();
                if ($this->_blockType) {
                    $collection->addTypeFilter($this->_blockType);
                }
                $this->_blocks = $collection;
            } else if ($this->getData('block_id')) {
                $collection = Mage::getModel('awautorelated/blocks')->getCollection();
                $collection->addStoreFilter()
                    ->addStatusFilter()
                    ->addCustomerGroupFilter($helper->getCurrentUserGroup())
                    ->addDateFilter()
                    ->addIdFilter($this->getData('block_id'));
                $this->_blocks = $collection;
            }
        }
        return $this->_blocks;
    }

    public function getBlocksHtml()
    {
        $out = '';
        foreach ($this->getBlocks() as $block) {
            $blockInstance = null;
            switch ($block->getData('type')) {
                case AW_Autorelated_Model_Source_Type::PRODUCT_PAGE_BLOCK:
                    $blockInstance = $this->getLayout()->createBlock('awautorelated/blocks_product');
                    break;
                case AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK:
                    $blockInstance = $this->getLayout()->createBlock('awautorelated/blocks_category');
                    break;
                case AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK:
                    $blockInstance = $this->getLayout()->createBlock('awautorelated/blocks_shoppingcart');
                    break;
            }
            if ($blockInstance) {
                $block->callAfterLoad();
                $blockInstance->setData($block->getData())
                    ->setParent($this);
                $out .= $blockInstance->toHtml();
            }
        }
        return $out;
    }
}
