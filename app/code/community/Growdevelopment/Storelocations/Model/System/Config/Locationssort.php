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
class Growdevelopment_Storelocations_Model_System_Config_Locationssort
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'id', 'label'=>Mage::helper('adminhtml')->__('ID')),
            array('value'=>'name', 'label'=>Mage::helper('adminhtml')->__('Store Name')),
            array('value'=>'state', 'label'=>Mage::helper('adminhtml')->__('State - Ascending')),
        );
    }
}
