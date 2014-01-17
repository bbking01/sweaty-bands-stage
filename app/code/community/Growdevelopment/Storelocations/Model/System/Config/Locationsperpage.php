<?php
/**
 * Grow Development - Store Locations Pro
 *
 * @category   Growdevelopment
 * @package    Growdevelopment_StoreLocations
 * @copyright  Copyright (c) 2012 Grow Development (http://www.growdevelopment.com)
 * @license    http://www.growdevelopment.com/docs/eula.txt
 * @author     Daniel Espinoza <modules@growdevelopment.com>
 *
 */
class Growdevelopment_Storelocations_Model_System_Config_Locationsperpage
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'0', 'label'=>Mage::helper('adminhtml')->__('All Locations')),
            array('value'=>'3', 'label'=>Mage::helper('adminhtml')->__('3')),
            array('value'=>'6', 'label'=>Mage::helper('adminhtml')->__('6')),
            array('value'=>'9', 'label'=>Mage::helper('adminhtml')->__('9')),
            array('value'=>'12', 'label'=>Mage::helper('adminhtml')->__('12')),
            array('value'=>'18', 'label'=>Mage::helper('adminhtml')->__('18')),
            array('value'=>'24', 'label'=>Mage::helper('adminhtml')->__('24')),
        );
    }
}
