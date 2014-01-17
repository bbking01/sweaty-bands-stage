<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Sendfriend
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magestore_Design_DesignController extends Mage_Core_Controller_Front_Action
{
    var $xmlString;
	/**
     * Initialize product instance
     *
     */  
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
	public function indexAction()
    {   		

		$this->xmlString = '';
		$this->loadLayout();     
		$this->renderLayout();

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
			echo Mage::getModel('design/design')->getProductFromId($productId,$user);
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
		$this->renderChildrenCategory(Mage::app()->getStore()->getRootCategoryId());
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
				$this->xmlString .= '<catName><![CDATA['.$category->getName().']]></catName>';
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
	public function getConfigAction()
	{
		$facebook = Mage::getStoreConfig('designtool_options/setup_app_config/facebook');
		$flickr = Mage::getStoreConfig('designtool_options/setup_app_config/flickr');
		$namePrice =(float) Mage::getStoreConfig('designtool_options/name_number_price/name_price');	
		$numberPrice = (float)Mage::getStoreConfig('designtool_options/name_number_price/number_price');		
		$symbol =  Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(); 	
		$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><configuration>';
		$xmlString .= '	<facebook>'.$facebook.'</facebook>';
		$xmlString .= '	<flickr>'.$flickr.'</flickr>';
		$xmlString .= '	<nameprice>'.Mage::helper('core')->currency($namePrice,true,false).'</nameprice>';
		$xmlString .= '	<numberprice>'.Mage::helper('core')->currency($numberPrice,true,false).'</numberprice>';		
		$xmlString .= '</configuration>';
		header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
		header ("Cache-Control: no-cache, must-revalidate");
		header ("Pragma: no-cache");
		header ("Content-type: text/xml");
		header ("Content-Description: PHP/INTERBASE Generated Data" );
		echo $xmlString;
	}
	
} ?>