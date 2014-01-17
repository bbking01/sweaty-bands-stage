<?php
class Mage_Qcprice_Block_Adminhtml_Qcprice_Pricesymbol extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row) {
		return ($this->_getLayout($row));
	}
	
	protected function _getLayout(Varien_Object $row) {
		return Mage::helper('core')->currency($row->getPrice());
	}
}
?>