<?php
/**
 * Grid column renderer
 *
 * @category    Halo
 * @package     Halo_Autopacks
 * @author      Haloweb team
 */
class AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_Winner extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
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
		$param = Mage::helper("adminhtml")->getUrl("customerpictures/adminhtml_customerpictures/winner/", array('id'=>$id));
		$html = '<input type="checkbox" id="winner-'.$id.'" onclick="setWinner('.$id.', \''.$param.'\')"';
		if($row->getWinnerTime()!=''){
			$html.=' checked="checked"';
		}
		$html.='/>';
    	return $html;
    }
}

?>