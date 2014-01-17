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

class Magestore_Design_IndexController extends Mage_Core_Controller_Front_Action
{
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
		$session = $this->_getSession();
		$designId = $this->getRequest()->getParam('design_id', false);
		
		if($designId != '' && $this->_getSession()->isLoggedIn()== false)
		{
			$session->setAfterAuthUrl( Mage::helper('core/url')->getCurrentUrl() );
            $session->setBeforeAuthUrl( Mage::helper('core/url')->getCurrentUrl() );
            $this->_redirect('customer/account/login/');
            return $this;   
		}
		
		$this->loadLayout();     
		$this->renderLayout();
		
    }
	public function savedesignAction()
	{
		$session = $this->_getSession();
		if ($this->_getSession()->isLoggedIn()) {
            $this->loadLayout();     
			$this->renderLayout();	
        }
		else
		{			
            $session->setAfterAuthUrl( Mage::helper('core/url')->getCurrentUrl() );
            $session->setBeforeAuthUrl( Mage::helper('core/url')->getCurrentUrl() );
            $this->_redirect('customer/account/login/');
            return $this;       
		}
	}
	public function designdeleteAction()
	{
	   $designId = $this->getRequest()->getParam('id', false);
	   $imageDir = Mage::getBaseDir(). DS .'designtool' . DS .'saveimg'. DS;
       if ($designId) {
            $design = Mage::getModel('design/savedesign')->load($designId);

            // Validate address_id <=> customer_id
            if ($design->getCustomerId() != $this->_getSession()->getCustomerId()) {
             $this->_getSession()->addError($this->__('The design does not belong to this customer'));
                $this->getResponse()->setRedirect(Mage::getUrl('customer/account/retrivedesign'));
                return;
            }

            try {
				if (file_exists($imageDir.$design->getFrontImage())){
					unlink($imageDir.$design->getFrontImage());
				}
				if (file_exists($imageDir.$design->getBackImage())){
					unlink($imageDir.$design->getBackImage());
				}
				if (file_exists($imageDir.$design->getLeftImage())){
					unlink($imageDir.$design->getLeftImage());
				}
				if (file_exists($imageDir.$design->getRightImage())){
					unlink($imageDir.$design->getRightImage());
				}
                $design->delete();
                $this->_getSession()->addSuccess($this->__('The design was successfully deleted'));
            }
            catch (Exception $e){
                $this->_getSession()->addError($this->__('There was an error while deleting the design'));
            }
        }
        $this->getResponse()->setRedirect(Mage::getUrl('design/index/savedesign'));
	}
	
	public function myaddAction()
	{
		exit;
	}
	//update welcome message
	public function welcomeMessageAction()
	{
		$this->loadLayout();		
        $Top = Mage::app()->getLayout()->getBlock('header')->getWelcome();
        $this->getResponse()->setBody($Top);
	}
	//update top links
	public function updateTopLinksAction()
	{
		$this->loadLayout();
        $Top = $this->getLayout()->getBlock('top.links')->toHtml();
        $this->getResponse()->setBody($Top);
	}
	//update top carts
	public function topCartsAction()
	{
		$this->loadLayout();
        $Top = $this->getLayout()->createBlock('cms/block')->setBlockId('header-cart')->toHtml();
        $this->getResponse()->setBody($Top);
	}
	
} ?>