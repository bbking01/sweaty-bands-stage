<?php
	$error = "";
	$msg = "";
	$item_id = $_REQUEST['item_id'];
	$fileElementName = $item_id;
	if(!empty($_FILES[$fileElementName]['error']))
	{
		switch($_FILES[$fileElementName]['error'])
		{
			case '1':
				$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case '2':
				$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case '3':
				$error = 'The uploaded file was only partially uploaded';
				break;
			case '4':
				$error = 'No file was uploaded.';
				break;

			case '6':
				$error = 'Missing a temporary folder';
				break;
			case '7':
				$error = 'Failed to write file to disk';
				break;
			case '8':
				$error = 'File upload stopped by extension';
				break;
			case '999':
			default:
				$error = 'No error code avaiable';
		}
	}elseif(empty($_FILES[$item_id]['tmp_name']) || $_FILES[$item_id]['tmp_name'] == 'none')
	{
		$error = 'No file was uploaded..';
	}
	elseif($_FILES[$item_id]['type']!='application/pdf' and $_FILES[$item_id]['type']!= 'application/binary')
	{	
		$error = 'Please upload PDF file only';
	}
	else 
	{			
			require '../app/Mage.php';
			
			Mage::app();
			$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('core_read');
			$filepath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'designtool/';	
			//for security reason, we force to remove all uploaded file
			$item_id = $_REQUEST['item_id'];
			$filename = time().$_FILES[$item_id]['name'];			
			$file_parts = explode(".", $filename);
			$file_parts_rev = array_reverse($file_parts);
			$file_extension = $file_parts_rev[0];
			
			$pdf = Mage::getModel('sales/order_item')->load($item_id);
			
			$pdfFile =	$pdf->getpdf_file();
			
			if($pdfFile!= '')
			{
				if(file_exists('pdf_files/'.$pdfFile))
					unlink('pdf_files/'.$pdfFile);	
			}
			
			$filename = 'order-'.$pdf->getOrder_id().'-'.$item_id.".".$file_extension;		
			$target_path = "pdf_files/".$filename; 
			move_uploaded_file($_FILES[$item_id]['tmp_name'], $target_path);
		
			$db = Mage::getSingleton('core/resource')->getConnection('core_write');
			$db->query("update sales_flat_order_item set pdf_file='".$filename."' where item_id=".$item_id);
			
			if(file_exists($target_path) && $filename != '')
			{ 
				$msg = "<a href=".$filepath."download.php?item_id=".$item_id.">".$filename."</a>";
			}	
		//	@unlink($_FILES['fileToUpload']);		
	}		
	echo "{";
	echo				"error: '" . $error . "',\n";
	echo				"msg: '" . $msg. "'\n";
	echo "}";
?>