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

class AW_Ajaxcatalog_Block_Catalog_Product_Container extends AW_Ajaxcatalog_Block_Catalog_Product_Abstract
{
    
    
    /**
     * Container Template
     */
    const TEMPLATE_PATH_CONTAINER = "awajaxcatalog/list/container.phtml";
       
    /**
     * Button Template
     */
    const TEMPLATE_PATH_BUTTON = "awajaxcatalog/list/container/button.phtml";
    
    /**
     * Loader Template
     */
    const TEMPLATE_PATH_LOADER = "awajaxcatalog/list/container/loader.phtml";
    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH_CONTAINER);
    }
        
    public function setNativeTemplate()
    {
        $this->setTemplate($this->_helper()->getNativeTemplate());
        return $this;
    }
    
    public function getNativeListHtml()
    {              
        return $this->getChildHtml('native_list');            
    }   
    
    /**
     * Retrives native list
     * @return AW_Ajaxcatalog_Block_Catalog_Product_List
     */
    public function getNativeList()
    {              
        return $this->getChild('native_list');            
    }   
    
    public function getLoaderHtml()
    {
        $block = $this->getLayout()->createBlock('core/template')->setTemplate(self::TEMPLATE_PATH_LOADER);
        if ($block){
            $block->setParentBlock($this);
            return $block->toHtml();
        }          
    }
           
    
    
    public function getButtonHtml()
    {
        $block = $this->getLayout()->createBlock('core/template')->setTemplate(self::TEMPLATE_PATH_BUTTON);
        if ($block){
            $block->setParentBlock($this);
            return $block->toHtml();
        }                
    }
     
    /**
     * Action Type
     * @return string
     */
    public function getActionType()
    {
        return $this->_helper()->getActionType();
    }

    /**
     * Native list block
     * @return Mage_Catalog_Block_Product_List
     */
    public function getNativeResource()
    {
        return $this->getChild('native_resource');
    }
    
    /**
     * Cut div block with class="pager"
     * @param string $html
     * @return string
     */
    protected function _cutPager($html)
    {
        $dom = $this->_helper()->getSimpleDOM()->str_get_html($html);        
        foreach ($dom->find("div[class=pager]") as $element){            
            $element->outertext = '';            
        }        
        foreach ($dom->find("table[class=pager]") as $element){            
            $element->outertext = '';            
        }        
        foreach ($dom->find("ul[class=pager]") as $element){            
            $element->outertext = '';            
        }        
        return $dom->__toString();
    }
    
    public function getToolbarHtml() 
    {
        return $this->_cutPager(parent::getToolbarHtml());
    }
    
    /**
     * Retrives route name
     * @return string
     */
    public function getRoute()
    {
        return $this->getRequest()->getRouteName();
    }
    
}
