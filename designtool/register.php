<?php
require '../app/Mage.php';
Mage::app("default");
// Customer Information
$firstname = $_REQUEST['firstname'];
$lastname = $_REQUEST['lastname'];
$email = $_REQUEST['email'];
$password = $_REQUEST['password'];

// Website and Store details
$websiteId = Mage::app()->getWebsite()->getId();
$store = Mage::app()->getStore();

$customer = Mage::getModel("customer/customer");
Mage::getSingleton("core/session", array("name" => "frontend"));
$session = Mage::getSingleton('customer/session');
$customer->website_id = $websiteId;
$customer->setStore($store);

try {
	$encryption = Mage::getModel('core/encryption');
	$salt = $encryption->hash($password);
	 
	$salt = substr($salt, -2); // returns "key"
	$str = $password;
	$newPassword = md5($salt.$str).":".$salt;
	// If new, save customer information
	$customer->firstname = $firstname;
	$customer->lastname = $lastname;
	$customer->email = $email;
	$customer->password_hash = $newPassword;
	if($customer->save()){
		$session->login($email, $password);
		echo "true";
		//echo $customer->firstname." ".$customer->lastname." information is saved!";
	}
	/*else{
		echo "An error occured while saving customer";
	}*/
}catch(Exception $e){
	//echo $e->getMessage();
	
	// If customer already exists, initiate login
	if(preg_match('/This customer email already exists/', $e)){
		echo "false";
		/*$customer->loadByEmail($email);
		
		$session->login($email, $password);

		echo $session->isLoggedIn() ? $session->getCustomer()->getName().' is online!' : 'not logged in';*/
	}
}