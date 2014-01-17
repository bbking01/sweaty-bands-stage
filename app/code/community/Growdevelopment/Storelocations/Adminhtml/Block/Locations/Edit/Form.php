<?php
/**
 * Grow Development - Store Locations Pro
 *
 * @category   Growdevelopment
 * @package    Growdevelopment_StoreLocations
 * @copyright  Copyright (c) 2012 Grow Development (http://www.growdevelopment.com)
 * @license    http://www.growdevelopment.com/docs/eula.txt
 * @author     Daniel Espinoza <modules@growdevelopment.com>
 *
 */
class Growdevelopment_Storelocations_Adminhtml_Block_Locations_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{


    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                                        'id' => 'edit_form',
                                        'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                                        'method' => 'post',
        							    'enctype' => 'multipart/form-data'
                                     )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        $this->setTemplate('storelocations/edit/form.phtml');
        
        return parent::_prepareForm();
    }
    
    public function getProductsJson()
    {

		$store_id = $this->getRequest()->getParam('id');
        $products = $this->getRequest()->getPost('in_store');
        
        if (is_null($products)) {
	        $ids = Mage::getModel('storeproduct/storeproduct')->getCollection()
        						->addFieldToFilter('store_id', array('eq'=> $store_id ));
        						
        	if ( 0 < count($ids)){					
	            foreach($ids as $id){
	            	$products[$id->getProductId()] = 0;
	            }
            
	        }
        }
    		    
        if (!empty($products)) {
            return Mage::helper('core')->jsonEncode($products);
        }
        return '{}';

    }
    
}


