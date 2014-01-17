<?php
/**
 * Feel free to contact me via Facebook
 * http://www.facebook.com/rebimol
 *
 *
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2011 Vladimir Popov
 */

class VladimirPopov_WebForms_Model_Captcha_Api extends Mage_Core_Model_Abstract{

	public function _construct()
	{
		parent::_construct();
		$this->_init('webforms/captcha_api');
	}
	
	public function toOptionArray(){
		$options = array(
			array('value' => 'standard' , 'label' => Mage::helper('webforms')->__('Standard')),
			array('value' => 'ajax' , 'label' => Mage::helper('webforms')->__('Ajax')),
		);
		return $options;
	}
	
}
?>
