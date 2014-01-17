<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shoppercategories_Block_Adminhtml_Shoppercategories extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_shoppercategories';
		$this->_blockGroup = 'shoppercategories';
		$this->_headerText = Mage::helper('shoppercategories')->__('Color Scheme Manager');
		$this->_addButtonLabel = Mage::helper('shoppercategories')->__('Add Scheme');
		parent::__construct();
	}
}