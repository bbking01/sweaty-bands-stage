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
 * Action Type
 */
class AW_Ajaxcatalog_Model_System_Config_Source_Actiontype
{
    /**
     * "Show more" button
     */
    const TYPE_BUTTON = 'button'; 
    
    /**
     * Show more by Scroll
     */
    const TYPE_SCROLL = 'scroll'; 
    
    /**
     * Retrives array with options
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::TYPE_BUTTON,
                'label' => Mage::helper('awajaxcatalog')->__('Show more button')
            ),            
            array(
                'value' => self::TYPE_SCROLL,
                'label' => Mage::helper('awajaxcatalog')->__('Auto-appearing on scrolling')
            ),            
        );
    }
    
}