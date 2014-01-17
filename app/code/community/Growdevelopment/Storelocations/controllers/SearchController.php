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
class Growdevelopment_Storelocations_SearchController extends Mage_Core_Controller_Front_Action 
{

    public function indexAction() {

    	if (Mage::getStoreConfig('growdevstorelocations/storelocationsconfig/grow_search_active')) {
	    	$this->loadLayout();
	    	
	    	// Set page layout based on admin setting
	    	$layout = "page_" . Mage::getStoreConfig('growdevstorelocations/storelocationsconfig/grow_page_layout');
			$node = Mage::getConfig()->getNode('global/cms/layouts') ? Mage::getConfig()->getNode('global/page/layouts') : Mage::getConfig()->getNode('global/page/layouts');
			$template = 'page_one_column';
	
			foreach ($node->children() as $layoutConfig) {
				if ($layoutConfig->layout_handle == $layout ) {
					$template = $layoutConfig->template; 
				}
			}
			
			$this->getLayout()->getBlock('root')->setTemplate( $template );
			
			$block = $this->getLayout()->createBlock(
			'growdevstorelocations/locations',
			'locations-search',
			array('template'=> 'storelocations/search.phtml')				
			);
			$this->getLayout()->getBlock('content')->append($block);
			
	    	$this->renderLayout();
    	} else { 
    		$this->_redirect('noroute'); 
    	}
    }

	/*
	 *  searchAction()
	 *
	 *  Ajax call that returns the results for the provided location.  
	 *  If no params supplied send to 404 page. 
	 *
	 */
	public function searchAction() 
	{
    	$params = $this->getRequest()->getParams();
		if(isset($params['lat'])){

			$lat = filter_var($params['lat'], FILTER_VALIDATE_FLOAT);
			$long = filter_var($params['lng'],FILTER_VALIDATE_FLOAT);
			$radius = filter_var($params['radius'], FILTER_SANITIZE_NUMBER_INT);
			$metric = filter_var($params['unit'], FILTER_SANITIZE_STRING);
			$miles = (filter_var($params['unit'], FILTER_SANITIZE_STRING)=='mi') ? true : false; 
			
			//response is an xml document;
			$dom = new DOMDocument("1.0");
	        $node = $dom->createElement("markers");
	        $locations = $dom->appendChild($node);
	        
			$model = Mage::getModel('storelocation/storelocation');
			$collection = $model->getCollection()
							->addFieldToFilter('status','1');
	
			// add all locations within radius to the response	
	        $i = 0;					
			foreach($collection as $location){
				$distance = $location->getDistance($lat, $long, $miles); 
				
				
				
				
				
				if ($distance < $radius ){
					//DCS Mage::log(__LINE__."searchAction ".print_r( $location,1));
	
					$node = $dom->createElement("marker");
	                $l_node = $locations->appendChild($node);
	                $node = $dom->createElement( 'id', ++$i);                
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'storename',$location->getStoreName());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'street',$location->getStreet());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'street2',$location->getStreet2());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'city',$location->getCity());
	                $l_node->appendChild($node);
					
					$region_txt = '';
			        $regionCollection = Mage::getModel('directory/region')->getCollection();
			        $regions = $regionCollection->toOptionArray();
					        
				    //DCS if (isset($regions[$location->getLocationRegionId()])){
					//DCS 	$region_txt = $regions[$location->getLocationRegionId()]['label'];
					//DCS 	Mage::log(__LINE__." searchAction ".$region_txt );
					//DCS 	Mage::log(__LINE__. "searchAction ". $location->getLocationRegionId());
					//DCS} else {
						$region_txt =  $location->getLocationRegionId(); 
						
					//DCS	Mage::log(__LINE__."searchAction ".$region_txt );
                    
                    $regionId =$region_txt; //DCS 
                    $region = Mage::getModel('directory/region')->load($regionId); //DCS 



						
					//DCS}
					
	              //DCS  $node = $dom->createElement( 'state',$region_txt);
	                $node = $dom->createElement( 'state',$region->getName());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'postalcode',$location->getPostalCode());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'country',$location->getLocationCountryId());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'photo',$location->getPhoto());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'phone',$location->getPhone());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'fax',$location->getFax());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'email',$location->getEmail());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'url',$location->getUrl());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'latitude',$location->getGoogleLatitude());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'longitude',$location->getGoogleLongitude());
	                $l_node->appendChild($node);
	                $node = $dom->createElement( 'units', $metric);
	                $l_node->appendChild($node);
	                
				}
				
			}
	        
			$this->getResponse()->setHeader('Content-Type', 'text/xml', true)->setBody($dom->saveXml());
		} else {
    		$this->_redirect('noroute'); 
		}
	}
	
}
