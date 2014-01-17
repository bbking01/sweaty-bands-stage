<?php
/**
 * Grid column renderer
 *
 * @category    Halo
 * @package     Halo_Autopacks
 * @author      Haloweb team
 */
class AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_User extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render column
     *
     * @param Varien_Object $row
     * @return string html
     */
    public function render(Varien_Object $row)
    {
    	$id = $row->getUserId();
		$customer = Mage::getModel('customer/customer')->load($id);
		$href = Mage::helper('adminhtml')->getUrl('adminhtml/customer/edit', array("id"=>$id));
		$html='<a href="'.$href.'" title="">'.$customer->getName().'</a>';
    	return $html;
    }
}

?>