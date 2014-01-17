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
class Growdevelopment_Storelocations_Helper_Data extends Mage_Core_Helper_Abstract
{


    /**
     * Retrieve store locations page url
     *
     * @return string
     */
    public function getStoreLocationsUrl()
    {
        return $this->_getUrl('store-locations');
    }

}
