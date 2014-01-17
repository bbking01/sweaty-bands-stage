<?php
class VladimirPopov_WebFormsCRF_Model_Webforms
	extends Mage_Core_Model_Abstract
{
	public function toOptionArray(){
		$option_array = array();
		if(Mage::getStoreConfig('webformscrf/registration/enable') && Mage::getStoreConfig('webformscrf/registration/form')){
			$webform = Mage::getModel('webforms/webforms')->load(Mage::getStoreConfig('webformscrf/registration/form'));
			if($webform->getId())
				$option_array[]= array('value'=> $webform->getId(), 'label' => $webform->getName());
		}
		
		$read = Mage::getModel('webforms/webforms')->getResource()->getReadConnection();
		
		$select = $read->select()
			->from(array('g'=> Mage::getModel('customer/group')->getResource()->getMainTable()),array('webform_id'))
			->joinLeft(array('w'=> Mage::getModel('webforms/webforms')->getResource()->getMainTable()),'g.webform_id = w.id',array('id','name'))
			->where('w.id > 0')
			->distinct(true);
		
		$result = $read->fetchAll($select);
		
		foreach($result as $data){
			if($data['name']){
				$option = array('value'=>$data['id'], 'label' => $data['name']);
				if(!in_array($option,$option_array))	$option_array[]= $option;
			}
		}

		return $option_array;
	}	
}
?>
