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
 * @package    AW_Ajaxcatalog
 * @version    1.0.5
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

/**
 * Event Observer
 */
class AW_Ajaxcatalog_Model_Observer
{            
    /**
     * Ask Helper
     * @return AW_Ajaxcatalog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('awajaxcatalog');
    }
    
    
    protected function _getListBlock($blocks)
    {
        foreach ($blocks as $block){
            if ($block instanceof Mage_Catalog_Block_Product_List){
                return $block;
            }
        }
    }
    
    public function generateBlocksAfter($event)
    {      
        if (!$this->_helper()->isEnabled()){
            return false;
        }                        
        $layout = $event->getLayout();                        
        
        # Get list
        $list = $this->_getListBlock($layout->getAllBlocks());                
        
        if ($list){                  
            
            $children = $list->getSortedChildren();                       
            $parentBlock = $list->getParentBlock();                        
            
            $name = $list->getNameInLayout();
            $type = $list->getType();        
            $alias = $list->getBlockAlias();
            
            $newBlock = $list->getLayout()->createBlock('awajaxcatalog/catalog_product_container')->setBlockAlias($alias)->setLayout($layout);            
            
            $newBlock->setToolbarBlockName($list->getToolbarBlockName());                        
            
            $nativeList = $list->getLayout()->createBlock('awajaxcatalog/catalog_product_list')->setBlockAlias($alias)->setLayout($layout);                                    
            $this->_helper()->setNativeTemplate($list->getTemplate());                 
            $newBlock->setNameInLayout($name);                                           
            $newBlock->setChild('native_list', $nativeList->setNativeTemplate());                                    
            $newBlock->setChild('native_resource', $list);                                    
                        
            foreach ($children as $child){               
                $newBlock->setChild($layout->getBlock($child)->getBlockAlias(), $layout->getBlock($child) );                                                               
            }            

            $parentBlock->setChild($alias, $newBlock);                                   

            if (($parentBlock instanceof Mage_CatalogSearch_Block_Result) 
                    || ($parentBlock instanceof Mage_CatalogSearch_Block_Advanced_Result)
                    || ($parentBlock instanceof Mage_Tag_Block_Product_Result)  
               ){
                $parentBlock->setListOrders();
                $parentBlock->setListModes();
            }

            if ($parentBlock instanceof AW_Advancedsearch_Block_Result) {
                $parentBlock->setListBlock($newBlock);
                $parentBlock->setListOrders();
                $parentBlock->setListModes();
            }
            
            $parentBlock->setListCollection();
        }
    }
    
}