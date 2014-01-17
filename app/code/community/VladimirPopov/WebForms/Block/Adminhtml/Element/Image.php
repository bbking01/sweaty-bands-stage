<?php
class VladimirPopov_WebForms_Block_Adminhtml_Element_Image extends VladimirPopov_WebForms_Block_Adminhtml_Element_File{
	
	protected function _getPreviewHtml()
	{
		$html = '';
		if($this->getData('result_id')){
			$result = Mage::getModel('webforms/results')->load($this->getData('result_id'));
			$field_id = $this->getData('field_id');
			$value = $this->getData('value');
			$thumbnail = $result->getThumbnail($field_id,$value,100);
			if($thumbnail){
				$html.= '<div><img src="'.$thumbnail.'"/></div>';
			}
			if($value){
				$html.='<nobr><a href="'.$result->getDownloadLink($field_id,$value).'">'.$value.'</a> <small>['.$result->getFileSizeText($field_id,$value).']</small></nobr><br/>';
			}
		}
		return $html;
		
	}	
}
?>
