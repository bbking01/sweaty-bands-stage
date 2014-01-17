<?php
class Mage_Qcprice_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/qcprice?id=15 
    	 *  or
    	 * http://site.com/qcprice/id/15 	
    	 */
    	/* 
		$qcprice_id = $this->getRequest()->getParam('id');

  		if($qcprice_id != null && $qcprice_id != '')	{
			$qcprice = Mage::getModel('qcprice/qcprice')->load($qcprice_id)->getData();
		} else {
			$qcprice = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($qcprice == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$qcpriceTable = $resource->getTableName('qcprice');
			
			$select = $read->select()
			   ->from($qcpriceTable,array('qcprice_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$qcprice = $read->fetchRow($select);
		}
		Mage::register('qcprice', $qcprice);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}