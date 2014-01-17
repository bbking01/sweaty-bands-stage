<?php
class Mage_Qrange_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/qrange?id=15 
    	 *  or
    	 * http://site.com/qrange/id/15 	
    	 */
    	/* 
		$qrange_id = $this->getRequest()->getParam('id');

  		if($qrange_id != null && $qrange_id != '')	{
			$qrange = Mage::getModel('qrange/qrange')->load($qrange_id)->getData();
		} else {
			$qrange = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($qrange == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$qrangeTable = $resource->getTableName('qrange');
			
			$select = $read->select()
			   ->from($qrangeTable,array('qrange_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$qrange = $read->fetchRow($select);
		}
		Mage::register('qrange', $qrange);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}