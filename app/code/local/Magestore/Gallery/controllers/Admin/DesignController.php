<?php

class Magestore_Gallery_Admin_DesignController extends Mage_Adminhtml_Controller_action
{

	var $xmlString;
	
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('gallery/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Album Manager'), Mage::helper('adminhtml')->__('Album Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	
	
	public function productAction()
	{
		$categoryId = $this->getRequest()->getParam('cid', false);
		$productId = $this->getRequest()->getParam('pid', false);
		$user = $this->getRequest()->getParam('user', false);
		
		if($categoryId!='')
		{
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
			header ("Content-type: text/xml");
			header ("Content-Description: PHP/INTERBASE Generated Data" );
			echo Mage::getModel('design/design')->getProductsFromCategory($categoryId);
		}
		else
		{
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
			header ("Content-type: text/xml");
			header ("Content-Description: PHP/INTERBASE Generated Data" );
			echo Mage::getModel('design/design')->getProductFromId($productId,$admin);
		}
	}
	
	public function categoryAction()
	{
		header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
		header ("Cache-Control: no-cache, must-revalidate");
		header ("Pragma: no-cache");
		header ("Content-type: text/xml");
		header ("Content-Description: PHP/INTERBASE Generated Data" );
		$this->renderChildrenCategory(Mage::app()->getWebsite(true)->getDefaultStore()->getRootCategoryId());
		echo '<?xml version="1.0" encoding="iso-8859-1"?>';
		echo '<allCategory>';		
		echo $this->xmlString;		
		echo "</allCategory>";

	}
	
	public function renderChildrenCategory($catId)
	{		
		$children = Mage::getModel('catalog/category')->load($catId)->getChildren();
		
		if(!empty($children)) {
			$categories = explode(',', $children);
		
			foreach($categories as $categoryId) {
				$category = Mage::getModel('catalog/category')->load($categoryId);
				//echo '<category label="'.$category->getName().'" catName="'.$category->getName().'" orderNo="'.$category->getPosition().'" type="subcategory" catID="'.$category->getId().'" >';
				$this->xmlString .= '<category>';
				$this->xmlString .= '<catName>'.$category->getName().'</catName>';
				$this->xmlString .=  '<catID>'.$category->getId().'</catID>';
				$this->xmlString .=  '<orderNo>'.$category->getPosition().'</orderNo>';
				$this->xmlString .=  '<catDesc>'.$category->getDescription().'</catDesc>';		
				$this->xmlString .= Mage::getModel("catalog/category")->load($category->getId())->getImage();
				if( $categoryImage!='')
					$this->xmlString .=  '<catThumb>'.$path.'/media/catalog/category/'. $categoryImage.'</catThumb>';
					
				$this->xmlString .=  '<type>'.'subcategory'.'</type>';
				$this->renderChildrenCategory($categoryId);
				$this->xmlString .= "</category>";
			}
		
		}				
		//echo $this->xmlString;
		
	}
	
}
