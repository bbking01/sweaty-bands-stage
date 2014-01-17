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
class Growdevelopment_Storelocations_Model_Mysql4_Storeproduct extends Mage_Core_Model_Mysql4_Abstract 
{

	public function _construct()
	{
		$this->_init('storeproduct/storeproduct', 'relation_id');
	}
}