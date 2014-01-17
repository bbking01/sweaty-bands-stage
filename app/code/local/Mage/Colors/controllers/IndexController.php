<?php
class Mage_Colors_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/colors?id=15 
    	 *  or
    	 * http://site.com/colors/id/15 	
    	 */
    	/* 
		$colors_id = $this->getRequest()->getParam('id');

  		if($colors_id != null && $colors_id != '')	{
			$colors = Mage::getModel('colors/colors')->load($colors_id)->getData();
		} else {
			$colors = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($colors == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$colorsTable = $resource->getTableName('colors');
			
			$select = $read->select()
			   ->from($colorsTable,array('colors_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$colors = $read->fetchRow($select);
		}
		Mage::register('colors', $colors);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}