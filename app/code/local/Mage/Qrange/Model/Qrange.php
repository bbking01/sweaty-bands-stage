<?php

class Mage_Qrange_Model_Qrange extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('qrange/qrange');
    }
	
	public function checkQuantityStart($data)
	{
		if ($data) {
			if($data['quantity_range_from'] >= $data['quantity_range_to'])
				throw new Exception('Quantity range is invalid');
			$collection = Mage::getModel('qrange/qrange')->getCollection()
						->addFieldToFilter('qrange_id', array('neq' => array($id)))
						->addFieldToFilter('quantity_range_from',array(
																		array('lt'=>array($data['quantity_range_from'])),
																		array('eq'=>array($data['quantity_range_from']))
																))
						->addFieldToFilter('quantity_range_to',array(
																		array('gt'=>array($data['quantity_range_from'])),
																		array('eq'=>array($data['quantity_range_from']))
																))
																
						/*->addFieldToFilter('quantity_range_to',array(
																		array('gt'=>array($data['quantity_range_to'])),
																		array('eq'=>array($data['quantity_range_to']))
																))*/
						;
			
			
			if ($collection->count() > 0) {
				
				$qcFrom = array();
				foreach($collection as $qcData)
				{
					$qcFrom['qty_range_from'] = $qcData['quantity_range_from'];
					$qcFrom['qty_range_to'] = $qcData['quantity_range_to'];
				}
				throw new Exception('Quantity range is belongs to '.$qcFrom['qty_range_from'] .' to '.$qcFrom['qty_range_to'].' range');
			}
			return true;
		}
	}
}