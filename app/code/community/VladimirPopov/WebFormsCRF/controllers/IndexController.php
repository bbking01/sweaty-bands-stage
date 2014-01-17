<?php
require_once 'Mage/Customer/controllers/AccountController.php';

class VladimirPopov_WebFormsCRF_IndexController extends Mage_Customer_AccountController{

	public function createAction(){
		
		$session = $this->_getSession();
		$webform = Mage::getModel('webforms/webforms')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load(Mage::app()->getRequest()->getPost('webform_id'));
		$webform->setData('disable_captcha',!Mage::helper('webformscrf')->showCaptchaRegistration());

		$result = array(
			'success' => false, 
			'errors' => array(), 
			'success_text' => '',
			'redirect_url' => '',
		);
		
		$result['redirect_url'] = Mage::getUrl('customer/account/index', array('_secure'=>true));  

		if($webform->getRedirectUrl())
				$result['redirect_url'] = Mage::getUrl($webform->getRedirectUrl(), array('_secure'=>true));

		$errors = $webform->validatePostResult();
		Mage::app()->getLayout()->createBlock('webforms/results','resultsblock',array('webform_id'=>1,'template'=>'webforms/results/default.phtml'));
		$collection = Mage::getResourceModel('customer/customer_collection')
			->addAttributeToSelect('email')
			->addAttributeToFilter('email',$this->getRequest()->getPost('email'));

        if(Mage::getStoreConfig('customer/account_share/scope')){
            $collection->addAttributeToSelect('website_id')->addAttributeToFilter('website_id',Mage::app()->getWebsite()->getId());
        }
			
		if($collection->getFirstItem()->getData('entity_id')){
			$errors[]= $this->__('Customer email already exists');
		}
		
		if(!count($errors)){
			// register customer
			$this->createPostAction();
			$register_errors = $session->getMessages()->getItems();
			foreach($register_errors as $err){
				if($err->getType() == Mage_Core_Model_Message::ERROR){
					$message = $err->getCode();
					
					// fix for inactive accounts
					if($message != Mage::helper('core')->__('This account is not activated.'))
						$errors[]= $message;
					
					// clear error
                    if(is_object($message) && get_class($message) == 'Mage_Core_Model_Message_Error' && method_exists($message, 'clear')) $message->clear();
				}
			}
		}
		
		if(!count($errors)){
			$result['success'] = true;				
		}
		
		$result['errors'] = implode('\n',$errors);

		$this->getResponse()->setBody(htmlspecialchars(json_encode($result), ENT_NOQUOTES));
	}
	
	public function editAction(){
		
		$session = $this->_getSession();
		$webform = Mage::getModel('webforms/webforms')->load(Mage::app()->getRequest()->getPost('webform_id'));
		$webform->setData('disable_captcha',true);
		
		$result = array(
			'success' => false, 
			'errors' => array(), 
			'success_text' => '',
			'redirect_url' => '',
		);
		
		$errors = $webform->validatePostResult();
		
		if(!count($errors)){
			// register customer
			$this->editPostAction();
			$register_errors = $session->getMessages()->getItems();
			foreach($register_errors as $err){
				if($err->getType() == Mage_Core_Model_Message::ERROR){
					$errors[] = $err->getCode()->clear();
				}
			}
		}
		
		if(!count($errors)){
			$result['success'] = true;
			$result['redirect_url'] = Mage::getUrl('customer/account/edit', array('_secure'=>true));
		}
		
		$result['errors'] = implode('\n',$errors);

		$this->getResponse()->setBody(htmlspecialchars(json_encode($result), ENT_NOQUOTES));
	}
	
	
	protected function _redirectError($defaultUrl){
		return $this;
	}
	
	protected function _redirectSuccess($defaultUrl){
		return $this;
	}
	
	 protected function _redirect($path, $arguments=array()){
		return $this;
	 }
}
?>
