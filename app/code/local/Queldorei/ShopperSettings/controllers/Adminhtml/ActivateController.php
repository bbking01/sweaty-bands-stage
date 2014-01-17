<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Adminhtml_ActivateController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('queldorei/shopper/activate');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('queldorei/shopper/activate')
            ->_addBreadcrumb(Mage::helper('shoppersettings')->__('Activate Shopper Theme'),
                Mage::helper('shoppersettings')->__('Activate Shopper Theme'));

        return $this;
    }

	public function indexAction()
	    {
	        $this->_initAction();
	        $this->_title($this->__('Queldorei'))
	            ->_title($this->__('Shopper'))
	            ->_title($this->__('Activate Shopper Theme'));

	        $this->_addContent($this->getLayout()->createBlock('shoppersettings/adminhtml_activate_edit'));
	        $block = $this->getLayout()->createBlock('core/text', 'activate-desc')
	                ->setText('<big><b>Activate will update following settings:</b></big>
	                        <br/><br/>
	                        <big>System > Config</big><br/><br/>
	                        <b>Web > Default pages</b>
	                        <ul>
	                            <li>CMS Home Page</li>
	                            <li>CMS No Route Page</li>
	                        </ul>
	                        <b>Design > Package</b>
	                        <ul>
	                            <li>Shopper</li>
	                        </ul>
							<b>Design > Themes</b>
	                        <ul>
	                            <li>Default</li>
	                        </ul>
	                        <b>Design > Footer</b>
	                        <ul>
	                            <li>Copyright</li>
	                        </ul>
	                        <b>Currency Setup > Currency Options</b>
	                        <ul>
	                            <li>Allowed currencies</li>
	                        </ul>
	                        ');
	        $this->_addLeft($block);

	        $this->renderLayout();
	    }

	public function activateAction()
    {
        $stores = $this->getRequest()->getParam('stores', array(0));
        $update_currency = $this->getRequest()->getParam('update_currency', 0);
        $setup_cms = $this->getRequest()->getParam('setup_cms', 0);
        
        try {
	        foreach ($stores as $store) {
                $scope = ($store ? 'stores' : 'default');
		        //web > default pages
                Mage::getConfig()->saveConfig('web/default/cms_home_page', 'shopper_home_2col', $scope, $store);
                Mage::getConfig()->saveConfig('web/default/cms_no_route', 'shopper_no_route', $scope, $store);
		        //design > package
                Mage::getConfig()->saveConfig('design/package/name', 'shopper', $scope, $store);
				//design > themes
                Mage::getConfig()->saveConfig('design/theme/default', 'default', $scope, $store);
                //design > header
                //Mage::getConfig()->saveConfig('design/header/logo_src', 'images/logo.png', $scope, $store);
                //design > footer
                Mage::getConfig()->saveConfig('design/footer/copyright', 'Shopper &copy; 2012 <a href="http://queldorei.com" >Premium Magento Themes</a> by Queldorei', $scope, $store);
                //Currency Setup > Currency Options
                if ($update_currency) {
                    Mage::getConfig()->saveConfig('currency/options/allow', 'GBP,EUR,USD', $scope, $store);
                }
            }

	        if ($setup_cms) {
                Mage::getModel('shoppersettings/settings')->setupCms();
	        }

		    Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('shoppersettings')->__('Shopper Theme has been activated.<br/>
                Please clear cache (System > Cache management) if you do not see changes in storefront.<br/>
                To update currencies rates please go to System -> Manage Currency Rates. Press import.
                Wait for message "All rates were fetched..." and press save.<br/>
                <b>IMPORTANT !!!. Log out from magento admin panel ( if you logged in ). This step is required to reset magento
                access control cache and avoid 404 message on theme options page</b>
                '));
        }
        catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shoppersettings')->__('An error occurred while activating theme. '.$e->getMessage()));
        }

        $this->getResponse()->setRedirect($this->getUrl("*/*/"));
    }

	private function _updateNewest()
	{

	}

	private function _updateSale()
	{

	}

}