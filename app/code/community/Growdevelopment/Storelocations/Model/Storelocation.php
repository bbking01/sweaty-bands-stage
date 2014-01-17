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
class Growdevelopment_Storelocations_Model_Storelocation extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('storelocation/storelocation');
    }
    
	public function getDistance($latitude, $longitude, $miles=true)
	{
		// code inspired by http://snipplr.com/view/2531/
		$pi = M_PI / 180;
		$storelat =  $this->getGoogleLatitude() * $pi;
		$storelong = $this->getGoogleLongitude() * $pi;
		$locationlat = $latitude * $pi;
		$locationlong = $longitude * $pi; 
		
		$r = 6372.797;
		$dlat = $locationlat - $storelat;
		$dlng = $locationlong - $storelong; 
		$a = sin($dlat / 2) * sin($dlat / 2) + cos($storelat) * cos($locationlat) * sin($dlng / 2) * sin($dlng / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$km = $r * $c;

 
 		$distance = $miles ? ($km * 0.621371192) : $km;
 		$mi_or_km = $miles ? 'mi' : 'km';
 		
		return ($miles ? ($km * 0.621371192) : $km);
	}
}