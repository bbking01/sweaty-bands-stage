<?php
class VladimirPopov_WebFormsCRF_CheckoutController extends Mage_Core_Controller_Front_Action{
	
	public function billingAction(){
		$webform = Mage::getModel('webforms/webforms')->load(Mage::app()->getRequest()->getPost('webform_id'));
		$webform->setData('disable_captcha',!Mage::helper('webformscrf')->showCaptchaCheckout());
		
		$result = array(
			'success' => false, 
			'errors' => array(), 
			'success_text' => '',
			'redirect_url' => '',
			'script' => '',
		);
		
		$errors = $webform->validatePostResult();
		
		if(!count($errors)){
			$result_id = $webform->savePostResult();
			if($result_id){
				Mage::getSingleton('customer/session')->setData('webformscrf_result_id',$result_id);
				$result['success'] = true;
				$result['script'] = "
					billing.save();
					$('result_id').setValue({$result_id});
				";
			} 
		} 
		$result['script'] .= "
			$$('#billing-buttons-container .button')[0].enable();
			Recaptcha.reload();
		";		
		
	
		$result['errors'] = implode('\n',$errors);

		$this->getResponse()->setBody(htmlspecialchars(Mage::helper('core')->jsonEncode($result), ENT_NOQUOTES));		
	}
}
?>
