<?php
class VladimirPopov_WebForms_Block_Adminhtml_Results_Renderer_Action 
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$edit_href = $this->getUrl('webforms/adminhtml_results/edit', array('_current'=>false,'id'=>$row->getId()));
		$reply_href = $this->getUrl('webforms/adminhtml_results/reply', array('_current'=>true,'id'=>$row->getId()));
		
		return <<<HTML
<select onchange="setLocation(this.options[this.selectedIndex].value)">
	<option></option>
	<option value="{$edit_href}">{$this->__('Edit')}</option>
	<option value="{$reply_href}">{$this->__('Reply')}</option>
</select>
HTML;
	}
}
  
?>
