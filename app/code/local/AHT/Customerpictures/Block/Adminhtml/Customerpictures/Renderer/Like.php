<?php
/**
 * Grid column renderer
 *
 * @category    Halo
 * @package     Halo_Autopacks
 * @author      Haloweb team
 */
class AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_Like extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
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
    	return Mage::helper('customerpictures/data')->getFacebookLike($id);
    }
}

?>