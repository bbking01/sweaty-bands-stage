<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-10-21
 * Time: 0:10
 */
 
class Unirgy_StoreLocator_Model_Settings_Sort
{
    const ALPHA = 'alpha';
    const DIST  = 'distance';
    public function toOptionArray()
    {
        return array(
            array('value'=>self::ALPHA, 'label'=>Mage::helper('ustorelocator')->__('Alphabetically')),
            array('value'=>self::DIST, 'label'=>Mage::helper('ustorelocator')->__('By Distance')),
        );
    }
}
