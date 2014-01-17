<?php			
require '../app/Mage.php';
Mage::app("default");
	 
// You have two options here,
// "frontend" for frontend session or "adminhtml" for admin session
Mage::getSingleton("core/session", array("name" => "frontend"));
$session = Mage::getSingleton("customer/session");

if($session->isLoggedIn())
{
	echo "&login=true&";
}else{
	echo "&login=false&";
}
?>

