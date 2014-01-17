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

class AW_Ajaxcatalog_Helper_Data extends Mage_Core_Helper_Abstract
{
    const NATIVE_TEMPLATE_KEY = 'native_catalog_product_list_template';
    
    const IS_AJAX_KEY = '_is_ajax';
    
    /**
     * Retrives Ext Enabled Flag
     * @return boolean
     */
    public function isEnabled()
    {
        return  (!Mage::getStoreConfig('advanced/modules_disable_output/AW_Ajaxcatalog') && Mage::getStoreConfig('awajaxcatalog/general/enabled') && $this->isRightRoute());
    }
    
    public function getActionType()
    {
        return Mage::getStoreConfig('awajaxcatalog/general/action_type');
    }
    
    public function isRightRoute()
    {
        return in_array(Mage::app()->getRequest()->getRouteName(), array(
                            'catalog',
                            'catalogsearch',
                            'tag',
                            'awadvancedsearch',
                            # etc
                        ));
    }
    
    /**
     * Store native template in registry
     * @param string $template
     * @return AW_Ajaxcatalog_Helper_Data 
     */
    public function setNativeTemplate($template)
    {
        Mage::register(self::NATIVE_TEMPLATE_KEY, $template);
        return $this;
    }
    
    /**
     * Retrives native template from registry
     * @return string
     */
    public function getNativeTemplate()
    {
       return Mage::registry(self::NATIVE_TEMPLATE_KEY); 
    }
    
    /**
     * 
     * @return AW_Ajaxcatalog_Helper_Tools_Simpledom
     */
    public function getSimpleDOM()
    {
        return Mage::helper('awajaxcatalog/tools_simpledom');
    }
    
	/**
     * Compare param $version with magento version
     * 
     * @param string $version
     * @return boolean
     */
	public function checkVersion($version)
	{
		return version_compare(Mage::getVersion(), $version, '>=');
	}	    
}