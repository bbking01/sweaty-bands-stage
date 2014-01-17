<?php
class VladimirPopov_WebFormsCRF_Block_Form extends VladimirPopov_WebForms_Block_Webforms
{
    protected function _construct(){
        Mage::unregister('show_form_name');
        parent::_construct();
    }

    protected function isEnabled(){
        $customer = Mage::helper('customer')->getCustomer();
        if($customer->getEntityId()){
            $group = Mage::getModel('customer/group')->load($customer->getGroupId());
            if($group->getWebformId())
                return true;
        }
        return Mage::getStoreConfig('webformscrf/registration/enable') && Mage::getStoreConfig('webformscrf/registration/form');
    }

    protected function _prepareLayout(){
        if($this->isEnabled()){
            Mage::register('show_form_name',true);
            // save current title
            $title = $this->getLayout()->getBlock('head')->getTitle();
            parent::_prepareLayout();

            // restore title
            $this->getLayout()->getBlock('head')->setTitle($title);

        }
    }

    protected function _toHtml(){
        if($this->isEnabled())
            return parent::_toHtml();
    }

    public function getFormData(){

        $data = $this->getData('form_data');

        if (is_null($data)) {
            $data = new Varien_Object(Mage::getSingleton('customer/session')->getCustomerFormData(true));
            $this->setData('form_data', $data);
        }

        if(!is_array($data)) $data = array();

        $data['webform_id'] = $this->getWebformId();

        $form_data = new Varien_Object($data);

        Mage::dispatchEvent('webformscrf_get_form_data',array('form_data' => $form_data));

        return $form_data;
    }

    public function getWebformId(){
        $webform_id = Mage::getStoreConfig('webformscrf/registration/form');
        if(in_array('customer_account_edit',$this->getLayout()->getUpdate()->getHandles())){
            $customer = $this->getCustomer();
            if($customer->getGroupId()){
                $group = Mage::getModel('customer/group')->load($customer->getGroupId());
                if($group->getWebformId()){
                    $webform_id = $group->getWebformId();
                }
            }
        }
        return $webform_id;
    }

    public function isNewsletterEnabled(){
        if (Mage::getStoreConfigFlag('advanced/modules_disable_output/Mage_Newsletter')) return false;
        return true;
    }

    public function getFormAction(){
        if($this->isAjax()){
            $secure = false;
            if(isset($_SERVER['HTTPS'])){
                $secure = $_SERVER['HTTPS'];
            }
            return $this->getUrl('webformscrf/index/create',array('_secure'=> $secure));
        }
        return Mage::helper('core/url')->getCurrentUrl();
    }

    public function isShow(){
        return true;
    }

    public function isAjax(){
        return true;
    }

    public function getCustomer(){
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    public function getCustomerResult(){
        $collection = Mage::getModel('webforms/results')->getCollection()
            ->addFilter('webform_id',$this->getWebformId())
            ->addFilter('customer_id',Mage::getSingleton('customer/session')->getCustomerId());

        $collection->getSelect()->order('created_time desc')->limit('1');

        return $collection->getFirstItem();
    }

    public function getCountryHtmlSelect(){
        return $this->getLayout()->createBlock('customer/form_register','form_register')->getCountryHtmlSelect();
    }

    public function getShowAddressFields(){
        return Mage::getStoreConfig('webformscrf/registration/address');
    }
}
?>
