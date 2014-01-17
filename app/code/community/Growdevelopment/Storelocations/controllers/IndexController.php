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
class Growdevelopment_Storelocations_IndexController extends Mage_Core_Controller_Front_Action 
{

    public function indexAction() {

    	if (Mage::getStoreConfig('growdevstorelocations/locationsconfig/grow_listing_active')) {
	    	$this->loadLayout();
	    	
			// if 'location' parameter exists, then display the details page. Otherwise set search or location template.
			$location = $this->getRequest()->getParam('location') ? $this->getRequest()->getParam('location') : 0;

			if ( $location > 0 ) {

		    	// Set page layout based on admin setting
		    	$layout = "page_" . Mage::getStoreConfig('growdevstorelocations/locationsdetailsconfig/grow_details_page_layout');
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
				array('template'=> 'storelocations/details.phtml')				
				);
				
				$this->getLayout()->getBlock('content')->append($block);

			} else {

		    	// Set page layout based on admin setting
		    	$layout = "page_" . Mage::getStoreConfig('growdevstorelocations/locationsconfig/grow_listing_page_layout');	
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
				array('template'=> 'storelocations/locations.phtml')				
				);
				$this->getLayout()->getBlock('content')->append($block);
			} 
			
	    	$this->renderLayout();
    	} else { 
    		$this->_redirect('*/search'); 
    	}
    }

}