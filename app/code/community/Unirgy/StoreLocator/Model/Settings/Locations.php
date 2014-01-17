<?php

class Unirgy_StoreLocator_Model_Settings_Locations
{
    public function toOptionArray()
    {
        /* @var $collection Unirgy_StoreLocator_Model_Mysql4_Location_Collection*/
        $collection = Mage::getModel('ustorelocator/location')->getCollection();
        $options = $collection->getData();
        $opArray[] = array('value' => '', 'label' => 'Choose address');
        foreach ($options as $row) {
            $array = array(
                'value' => $row['location_id'],
                'label' => $row['address']
            );
            $opArray[] = $array;
        }

        return $opArray;
    }
}
