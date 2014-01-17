<?php
/**
 * Grid column renderer
 *
 * @category    Halo
 * @package     Halo_Autopacks
 * @author      Haloweb team
 */
class AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_Coupon extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render column
     *
     * @param Varien_Object $row
     * @return string html
     */
    public function render(Varien_Object $row)
    {
    	$id = $row->getId();
		$action = Mage::helper('adminhtml')->getUrl('customerpictures/adminhtml_customerpictures/coupon');
		$html = '<div id="coupon-form-'.$id.'"';
		if($row->getWinnerTime()==''){
			$html.=' style="display:none"';
		}
		$html.='>';
		$html.='<form action="'.$action.'" method="POST" id="coupon-'.$id.'">';
		$html.='<input type="hidden" name="coupon[id]" value="'.$id.'" />';
		$html.='<input type="hidden" name="form_key" value="'.Mage::getSingleton('core/session')->getFormKey().'"/>';
		$html.='<input type="text" class="input-text required-entry" name="coupon[code]" style="width:100px; margin-right:5px"/>';
		$html.='<button type="submit" class="button"><span><span>'.$this->__('Save').'</span></span></button>';
		$html.='</form>';
		$html.='</div>';
    	return $html;
    }
}

?>