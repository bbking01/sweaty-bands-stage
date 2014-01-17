<?php
class Magestore_Printcolormanagement_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/bannerslider?id=15 
    	 *  or
    	 * http://site.com/bannerslider/id/15 	
    	 */
    	/* 
		$bannerslider_id = $this->getRequest()->getParam('id');

  		if($bannerslider_id != null && $bannerslider_id != '')	{
			$bannerslider = Mage::getModel('bannerslider/bannerslider')->load($bannerslider_id)->getData();
		} else {
			$bannerslider = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($bannerslider == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$bannersliderTable = $resource->getTableName('bannerslider');
			
			$select = $read->select()
			   ->from($bannersliderTable,array('bannerslider_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$bannerslider = $read->fetchRow($select);
		}
		Mage::register('bannerslider', $bannerslider);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}