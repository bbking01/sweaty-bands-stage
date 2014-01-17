<?php

class Mage_Qcprice_Model_Qcprice extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('qcprice/qcprice');
    }
	public function checkCombination($data, $id = 0)
	{
		$collection = Mage::getModel('qcprice/qcprice')->getCollection();
		
		if ($id != 0) {
			$collection->addFieldToFilter('qcprice_id',array('neq'=>array($id)));
		}
		$collection->addFieldToFilter('quantity_range_id',array('eq'=>array($data['quantity_range_id'])));
		$collection->addFieldToFilter('colors_counter_id',array('eq'=>array($data['colors_counter_id'])));
		
		if ($collection->count() > 0) {
			throw new Exception('Quality & Color combination is already define.');
		}
		return true;
	}
	
	public function getQcPrice($totalColor,$sizeQuantity)
	{
		$collection = Mage::getModel('qcprice/qcprice')->getCollection()
														->addFieldToFilter('qr.quantity_range_from',array('lteq'=>array($sizeQuantity)))
														->addFieldToFilter('qr.quantity_range_to',array('gteq'=>array($sizeQuantity)))
														->addFieldToFilter('clrs.colors_counter',array('eq'=>array($totalColor)));
														
		$collection->getSelect()
				->joinRight(array('qr'=>'qrange'), 'quantity_range_id = qr.qrange_id', array('qr.*'))
				->joinRight(array('clrs'=>'colors'), 'colors_counter_id = clrs.colors_id', array('clrs.*'));
		
		if ($collection->count() > 0) {
			$collectionData = $collection->getData();
			$printingPrice = $collectionData[0]['price'];	
			//$printingPrice = $printingPrice * $sizeQuantity;
			return $printingPrice;
		}
	}
}