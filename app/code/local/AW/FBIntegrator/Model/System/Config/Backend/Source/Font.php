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
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_FBIntegrator
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */




class AW_FBIntegrator_Model_System_Config_Backend_Source_Font {

    public function toOptionArray() {
        return array(
            array('value' => 'arial', 'label' => Mage::helper('fbintegrator')->__('Arial')),
            array('value' => 'lucida grande', 'label' => Mage::helper('fbintegrator')->__('Lucida Grande')),
            array('value' => 'segoe ui', 'label' => Mage::helper('fbintegrator')->__('Segoe Ui')),
            array('value' => 'tahoma', 'label' => Mage::helper('fbintegrator')->__('Tahoma')),
            array('value' => 'trebuchet ms', 'label' => Mage::helper('fbintegrator')->__('Trebuchet Ms')),
            array('value' => 'verdana', 'label' => Mage::helper('fbintegrator')->__('Verdana')),
        );
    }

}