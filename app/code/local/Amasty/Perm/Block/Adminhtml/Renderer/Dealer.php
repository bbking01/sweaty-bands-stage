<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Perm
*/
class Amasty_Perm_Block_Adminhtml_Renderer_Dealer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    public function getColumn()
    {
    	$options = $this->_column->getOptions();
    	if (!$options){
    	    $options = Mage::helper('amperm')->getSalesPersonList();  	    
            $this->_column->setOptions($options);
    	}
        return $this->_column;
    }
}