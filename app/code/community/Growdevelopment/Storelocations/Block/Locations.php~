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
class Growdevelopment_Storelocations_Block_Locations extends Mage_Core_Block_Template
{
	private $mode; 

   /*
 	* getLocations()
 	*
 	*/
	public function getLocations() {
	
		$model = Mage::getModel('storelocation/storelocation');

		// get only enabled locations
		$collection = $model->getCollection()->addFieldToFilter('status','1');

		// store type
		if ($type = $this->getRequest()->getParam('type'))
		{
			if ($type=="online"){
				$collection->addFieldToFilter('store_type', '1');
			}elseif ($type=="physical"){
				$collection->addFieldToFilter('store_type', '2');
			
			}elseif ($type=="both"){
				$collection->addFieldToFilter('store_type', '3');
			}
			
		}		
		
		// if not requesting online store type, then check country & region
		if ($type != '1') {
			if ( $country = $this->getRequest()->getParam('country_id'))
				$collection->addFieldToFilter('location_country_id', $country );
				
			if ( $region_id = $this->getRequest()->getParam('region_id'))
				$collection->addFieldToFilter('location_region_id', $region_id);
				
			if ( $region = $this->getRequest()->getParam('region'))
				$collection->addFieldToFilter('location_region_id', $region);
		}				

		// limit by selected products
		$products = $this->getRequest()->getParam('products') ?  $this->getRequest()->getParam('products') : array() ;
		
		$product_ids = array();
		foreach ( $products as $key => $val ) {
			array_push( $product_ids, $val ); 
		}
		
		if ($product_ids){
			$collection->getSelect()->joinLeft(array('products' => 'growdevelopment_storelocation_products'),
											'`main_table`.id = `products`.store_id'
											);
			$collection->addFieldToFilter('product_id', $product_ids);
			$collection->getSelect()->group('main_table.id');

		} else {
		
			// set sort 
			$sortorder = Mage::getStoreConfig('growdevstorelocations/locationsconfig/grow_locations_sort');		
			if ( $sortorder == 'id') {
				$collection->setOrder('id','ASC');
			}		
			if ( $sortorder == 'name') {
				$collection->setOrder('store_name','ASC');
			}		
			if ( $sortorder == 'state') {
				$collection->setOrder('state','ASC');
			}		
		}

		// setup paging

		$page = $this->getRequest()->getParam('p');
		if ( $page == null) $page = 1; 
		$perpage = Mage::getStoreConfig('growdevstorelocations/locationsconfig/grow_per_page');
		
		if ( $perpage > 0 ) {
			$collection->setCurPage($page);
			$collection->setPageSize($perpage);
		}
		
		return $collection;
	}
   /* 
	* getProductIds()
	*
	* Returns a collection of the product ID's that are associated with locations
	*/
	public function getProductIds(){
		$ids = Mage::getModel('storeproduct/storeproduct')->getCollection();
		$ids->getSelect()->group('product_id');
		return $ids;
	}	

   /*
 	* getLocationById()
 	*
 	*/
	public function getLocationById( $location_id) {

		$location = Mage::getModel('storelocation/storelocation')->load($location_id);
		return $location; 
	
	}

   /*
 	* getMode()
 	*
 	*/
	public function getMode() {
		return (Mage::getStoreConfig('growdevstorelocations/locationsconfig/grow_locations_display') == 'grid') ? 'grid' : 'list';
	}
	
   /*
 	* displayLimiter()
 	*
 	*/
	public function displayLimiter() {
		return (Mage::getStoreConfig('growdevstorelocations/locationsconfig/grow_display_limiter') == 1) ? true : false;	 	
	}
	
   /*
 	* displayMap()
 	*
 	*/
	public function displayMap() {
		return (Mage::getStoreConfig('growdevstorelocations/locationsdetailsconfig/grow_show_map') == 1) ? true : false;	 	
	}

   /*
 	* displayGetDirections()
 	*
 	*/
	public function displayGetDirections() {
		return (Mage::getStoreConfig('growdevstorelocations/locationsdetailsconfig/grow_show_directions') == 1) ? true : false;	 	
	}
}