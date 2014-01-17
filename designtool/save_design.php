<?php
require '../app/Mage.php';
Mage::app();
$images= '';
$message = '';				
$flashvars = $_REQUEST['flashvars']; 	
$xml = simplexml_load_string($flashvars); 
$action =  $_REQUEST['action'];//$xml->action;
$designname =  $_REQUEST['designname'];//$xml->action;
$saveImageDir = Mage::getBaseDir(). DS .'designtool' . DS .'saveimg'. DS;
if(isset($action)){
	
	$site_fullpath  = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	$image_path = $site_fullpath . 'designtool/saveimg/';	
 	Mage::getSingleton("core/session", array("name" => "frontend"));
	$session = Mage::getSingleton("customer/session");
	$customer_email = $session->getCustomer()->getEmail();
	$customer_id = $session->getCustomerId();
	
	$front_image = $xml->front;	
	$customer = $session->getCustomer()->getFirstname();
	 
	$noofsides = $_REQUEST['nos'];	
	$save_string =  $xml->savestr;	
	
	$cPath = $xml->cPath;
	
	// $products_id = $xml->products_id;	
	$model = Mage::getModel("admin/user");
	$admin_email = Mage::getStoreConfig('trans_email/ident_sales/email');
	
	$store = Mage::app()->getStore();	
	$name = '';
	
	$images = '<table><tbody><tr>';
	$images .= '<td><a href="'.$image_path.$front_image.'" ><img height="125" src="'.$image_path.$front_image.'"></a></td>';
	if($xml->back != "" )
 	{
		$images .= '<td ><a href="'.$image_path.$xml->back.'" ><img height="125" src="'.$image_path .$xml->back.'"></a></td>';
 	}
	if($xml->left != "" && $noofsides == ('3' || '4'  ))
	{				
		$images .= '<td  ><a href="'.$image_path.$xml->left.'" ><img height="125" src="'.$image_path .$xml->left.'"></a></td>';		
	}
	if($xml->right != "" && $noofsides == ( '4' ) )
	{			
		$images .= '<td ><a href="'.$image_path.$xml->right.'" ><img height="125" src="'.$image_path .$xml->right.'"></a></td>';
	}
	$images .= '</tr></tbody></table>';
	
    
	if($action  == 'save' )
	{	
		$template_id =  $_REQUEST['template_id'];
		if( isset($template_id) && $template_id != "null")
		{
			$model  = Mage::getModel('gallery/gallery')->load($template_id);
            $model->setDesigndata($save_string);
            $model->save();            			
		}
		else
		{			
			$model  = Mage::getModel('design/savedesign');
			$designCollection  = Mage::getModel('design/savedesign')
						->getCollection()
						->addFieldToFilter('customer_id', $customer_id)
						->addFieldToFilter('design_name', $xml->designname);
						//->getFirstItem();
			if($designCollection->count()>0)
			{
				$existedDesignData = $designCollection->getFirstItem();	
				if (file_exists($saveImageDir.$existedDesignData->getFrontImage())){
					unlink($saveImageDir.$existedDesignData->getFrontImage());
				}
				if (file_exists($saveImageDir.$existedDesignData->getBackImage())){
					unlink($saveImageDir.$existedDesignData->getBackImage());
				}
				if (file_exists($saveImageDir.$existedDesignData->getLeftImage())){
					unlink($saveImageDir.$existedDesignData->getLeftImage());
				}
				if (file_exists($saveImageDir.$existedDesignData->getRightImage())){
					unlink($saveImageDir.$existedDesignData->getRightImage());
				}
				$model->setDesignId($existedDesignData->getDesignId());
			}	
			
			
			$model->setCustomer_id($customer_id);
			$model->setProducts_id($xml->productid);
			$model->setDesign_name($xml->designname);			
			$model->setFront_image($xml->front);
			$model->setBack_image($xml->back);
			$model->setLeft_image($xml->left);
			$model->setRight_image($xml->right);			
			$model->setSave_string($save_string);
			$model->setAction($action);
			$model->save();
	
			$design_id =  $model->getDesign_id();
			
			$path = $site_fullpath.'design/index/index/design_id/'.$design_id;
			/*get the save design email template from app\locale\en_US\template\email\save_design_email_template.html*/
			$saveDesignEmailTemplate  = Mage::getModel('core/email_template')->loadDefault('save_design_email_template');			
            $templateId = $saveDesignEmailTemplate->getData('template_id');
			//$templateId = 2; 
			$sender = Array('name'  => $session->getCustomer()->getFirstname(),
							'email' => $session->getCustomer()->getEmail());
							  
			$vars = Array();
			$vars = Array('path'=>$path,
						'design_name' =>urldecode($xml->designname),
						'images'=>$images,
						'sendername'=>$customer);
			$email = $session->getCustomer()->getEmail();
		    $name = $email;
			$storeId = Mage::app()->getStore()->getId(); 
			/* $translate  = Mage::getSingleton('core/translate');
			Mage::getModel('core/email_template')
				  ->setTemplateSubject($mailSubject)
				  ->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
			$translate->setTranslateInline(true); */	
			$processedTemplate = $saveDesignEmailTemplate->getProcessedTemplate($vars);    
			$saveDesignEmailTemplate->setSenderName($senderName);
			$saveDesignEmailTemplate->setSenderEmail($senderEmail);
			$saveDesignEmailTemplate->setTemplateSubject('Save Design');
			//$emailTemplate->send("ajay.makwana@rightwaysolution.com","ajay", $emailTemplateVariables);
			$saveDesignEmailTemplate->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
			echo $design_id;
		}
		
	}
	else if($action  == 'share'){
			$model  = Mage::getModel('design/savedesign');
			$model->setCustomer_id($customer_id);
			$model->setProducts_id($xml->productid);
			$model->setDesign_name($xml->designname);			
			$model->setFront_image($xml->front);
			$model->setBack_image($xml->back);
			$model->setLeft_image($xml->left);
			$model->setRight_image($xml->right);			
			$model->setSave_string($save_string);
			$model->setAction($action);
			$model->save();	
			echo $design_id =  $model->getDesign_id();
			
	}else if($action  == 'validate'){	
			$model  = Mage::getModel('design/savedesign')
						->getCollection()
						->addFieldToFilter('customer_id', $customer_id)
						->addFieldToFilter('design_name', rawurlencode($designname));
						//->getFirstItem();
			if($model->count()>0)
			{
				echo "true";
			}
			else
			{
				echo "false";
			}
	}
	else{
		
		$model  = Mage::getModel('design/savedesign');
		//For Update design
		if(isset($_REQUEST['designId']) && $_REQUEST['designId']!='')
		{
			$design_id =  $_REQUEST['designId'];
			//$model->setDesignId($designId);
		}
		else
		{
			$model->setCustomer_id($customer_id);
			$model->setProducts_id($xml->productid);
			$model->setDesign_name($xml->designname);
			$model->setFront_image($xml->front);
			$model->setBack_image($xml->back);
			$model->setLeft_image($xml->left);
			$model->setRight_image($xml->right);	
			$model->setSave_string($save_string);
			$model->setAction($action);
			$model->save();
			$design_id =  $model->getDesign_id();
		}
		
		$path = $site_fullpath.'design/index/index/design_id/'.$design_id;		
		
		$frd_msg = '';
		if(isset($xml->comments) && $xml->comments != '' )
		{		
			$frd_msg = '<span style="font-size: 9pt;">'.urldecode($xml->comments).'</span>';
		}	 		
		
		/*get the send to friend email template from app\locale\en_US\template\email\friend_email_template.html*/
		$friendEmailTemplate  = Mage::getModel('core/email_template')->loadDefault('friend_email_template');
        $templateId = $friendEmailTemplate->getData('template_id');
		//$templateId = 1; 
		$allemail = explode(",",$xml->sendto);
		$name = $allemail;
		$sender = Array('name'  => $session->getCustomer()->getFirstname(),
						  'email' => $session->getCustomer()->getEmail());
		$vars = Array();
		$vars = Array('path'=>$path,
						'design_name' =>urldecode($xml->designname),
						'frd_msg'=>$frd_msg,
						'images'=>$images,
						'sendername'=>$customer);
		$storeId = Mage::app()->getStore()->getId(); 
		/* $translate  = Mage::getSingleton('core/translate');
		Mage::getModel('core/email_template')
					  ->setTemplateSubject($mailSubject)
					  ->sendTransactional($templateId, $sender, $allemail, $name, $vars, $storeId); */
		$processedTemplate = $friendEmailTemplate->getProcessedTemplate($vars);                
		$friendEmailTemplate->setSenderName($senderName);
		$friendEmailTemplate->setSenderEmail($senderEmail);
		$friendEmailTemplate->setTemplateSubject('Check the Design');
		//$emailTemplate->send("ajay.makwana@rightwaysolution.com","ajay", $emailTemplateVariables);
		$friendEmailTemplate->sendTransactional($templateId, $sender, $allemail, $name, $vars, $storeId);
		echo $design_id;
	}
}
?>