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
class Growdevelopment_Storelocations_Model_System_Config_PageDisplay
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'search', 'label'=>Mage::helper('adminhtml')->__('Locations Map Search')),
            array('value'=>'list', 'label'=>Mage::helper('adminhtml')->__('Locations Listing')),
        );
    }
}
