<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-10-21
 * Time: 0:47
 */
 
class Unirgy_StoreLocator_Model_Settings_Units
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'mi', 'label'=>Mage::helper('ustorelocator')->__('Miles')),
            array('value'=>'km', 'label'=>Mage::helper('ustorelocator')->__('Kilometers')),
        );
    }
}
