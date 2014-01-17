<?php			
require '../app/Mage.php';

Mage::app("default");
Mage::getSingleton("core/session", array("name" => "frontend"));
$session = Mage::getSingleton('customer/session');
$username = $_REQUEST['email_address'];
$password =  $_REQUEST['password'];
$allCustomers = Mage::getModel('customer/customer')->getCollection();
$a = $allCustomers->getData();
$total_cust = count($a);
for($i= 0; $i < $total_cust ; $i++)
{
	if($a[$i]['email'] == $username)
	{
		 $id = $a[$i]['entity_id'];
		$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
		$results = $conn->fetchAll('SELECT * FROM customer_entity_varchar WHERE  entity_id = '.$id.' AND attribute_id = 12;');
		foreach($results as $row) 
		{
			$pwd  = $row['value']; 
			$salt = substr($pwd, -2); // returns "key"
			$str = $password;
			$pass= md5($salt.$str).":".$salt;
			if($pass == $pwd)
			{
				$login = 'yes'; 
			}
			
		}
		
	}
}
if($login == 'yes')
{
	$session->login($username, $password);
	echo "&res=true&";
}
else
{
	echo "&res=false&";
}
?>


