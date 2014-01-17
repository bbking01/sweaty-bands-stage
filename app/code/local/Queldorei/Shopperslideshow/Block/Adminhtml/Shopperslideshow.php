<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Block_Adminhtml_Shopperslideshow extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_shopperslideshow';
		$this->_blockGroup = 'shopperslideshow';
		$this->_headerText = Mage::helper('shopperslideshow')->__('Flexslider Slides Manager');
		$this->_addButtonLabel = Mage::helper('shopperslideshow')->__('Add Slide');
		parent::__construct();
	}
}