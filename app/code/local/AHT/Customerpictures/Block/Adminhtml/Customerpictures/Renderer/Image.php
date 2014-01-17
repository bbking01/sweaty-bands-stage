<?php
/**
 * Grid column renderer
 *
 * @category    Halo
 * @package     Halo_Autopacks
 * @author      Haloweb team
 */
class AHT_Customerpictures_Block_Adminhtml_Customerpictures_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render column
     *
     * @param Varien_Object $row
     * @return string html
     */
    public function render(Varien_Object $row)
    {
    	$imageName = $row->getImageName();
        $customerId = $row->getUserId();
		$width = Mage::getStoreConfig('customerpictures/view/width');
		$height = Mage::getStoreConfig('customerpictures/view/height');
		$url = Mage::getBaseDir('media').DS."customerpictures".DS."images".DS.$customerId.DS."thumb".DS.$imageName; 
		$src = Mage::helper('customerpictures/data')->reSize($url, $imageName, 'images', $customerId, 80, 80);
		$href = Mage::helper('customerpictures/data')->reSize($url, $imageName, 'images', $customerId, $width, $height);
		$html = '<a href="'.$href.'" rel="lightbox" title="'.$row->getImageTitle().'"><img src="'.$src.'" alt=""/></a>';
    	return $html;
    }
}

?>