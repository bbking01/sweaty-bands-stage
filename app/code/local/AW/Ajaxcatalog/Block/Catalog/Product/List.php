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

class AW_Ajaxcatalog_Block_Catalog_Product_List extends AW_Ajaxcatalog_Block_Catalog_Product_Abstract
{
    const TEMPLATE_PATH_CONTAINER = "awajaxcatalog/list/container.phtml";
    
    public function setNativeTemplate()
    {          
        if ($this->_helper()->getNativeTemplate()){
            $this->setSession('native_template_path', $this->_helper()->getNativeTemplate());
        }
        
        $this->setTemplate(
                    $this->_helper()->getNativeTemplate() ? 
                    $this->_helper()->getNativeTemplate() : 
                    $this->getSession('native_template_path')
                );
        $this->_isNativeList = true;
        return $this;
    }    

    public function getToolbarBlock() 
    {
        # Fix #7044 [1330] Fatal error when try to sort by price
        if (!$this->_helper()->checkVersion('1.4.0.0')){
            return $this->getLayout()->createBlock('awajaxcatalog/catalog_product_list_toolbar');            
        }
        # Endfix
                
        if ($this->getParentBlock() &&
                ($this->getParentBlock() instanceof AW_Ajaxcatalog_Block_Catalog_Product_Container)
                ){
            return $this->getParentBlock()->getToolbarBlock();
        } else {
            return parent::getToolbarBlock();
        }
        
    }
    
    public function getLoadedProductCollection() 
    {
        if ($this->getParentBlock() && 
                ($this->getParentBlock() instanceof AW_Ajaxcatalog_Block_Catalog_Product_Container)
           ){
            return $this->getParentBlock()->getLoadedProductCollection();
        } else {
            return parent::getLoadedProductCollection();
        }                
    }    
        
    public function getColumnCount() 
    {
        if ($this->getParentBlock() && 
                ($this->getParentBlock() instanceof AW_Ajaxcatalog_Block_Catalog_Product_Container)
           ){
            $colCount = $this->getParentBlock()->getNativeResource()->getColumnCount();
            $this->setSession('column_count', $colCount);            
            return $colCount;
        } else {
            return $this->getSession('column_count');
        }        
    }
               
    public function getToolbarHtml() {}        
    
}
