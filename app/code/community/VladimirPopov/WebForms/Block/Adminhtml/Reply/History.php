<?php
class VladimirPopov_WebForms_Block_Adminhtml_Reply_History
	extends Mage_Adminhtml_Block_Template
{
	
	protected $_result;
	
	public function getResult(){
		return $this->_result;
	}
	
	protected function _construct()
	{
		parent::_construct();
		
		$id = $this->getRequest()->getParam('id');
		if(!is_array($id)){
			$this->_result = Mage::getModel('webforms/results')->load($id);
		} else {
			$this->_result = false;
		}
		
		$this->setTemplate('webforms/reply/history.phtml');
	}
	
	public function getMessages()
	{
		$id = $this->getRequest()->getParam('id');
		if(!is_array($id)){
			$collection = Mage::getModel('webforms/message')->getCollection()
				->addFilter('result_id',$id);
			$collection->getSelect()->order('created_time desc');
			return $collection;
		}
		return false;
	}
	
}
?>
