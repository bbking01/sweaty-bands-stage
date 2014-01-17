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
class Growdevelopment_Storelocations_Model_System_Config_Milesorkm
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'mi', 'label'=>Mage::helper('adminhtml')->__('Miles')),
            array('value'=>'km', 'label'=>Mage::helper('adminhtml')->__('Kilometers')),
        );
    }
}
