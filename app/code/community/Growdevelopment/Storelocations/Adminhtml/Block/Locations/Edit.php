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
class Growdevelopment_Storelocations_Adminhtml_Block_Locations_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

	protected function _prepareLayout()
    {
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled() && ($block = $this->getLayout()->getBlock('head'))) 	{
            $block->setCanLoadTinyMce(true);
        }
        parent::_prepareLayout();
    }
    
    
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'growdev_adminhtml';
        $this->_controller = 'locations';
        

        $this->_updateButton('save', 'label', Mage::helper('growdevstorelocations')->__('Save Location'));
        $this->_updateButton('delete', 'label', Mage::helper('growdevstorelocations')->__('Delete Location'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);


        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            
        ";
        
    }

    /**
     * Get Header Text
     *
     */
    public function getHeaderText()
    {
        if( Mage::registry('storelocation_data') && Mage::registry('storelocation_data')->getId() ) {
            return Mage::helper('growdevstorelocations')->__("Edit Location '%s'", $this->htmlEscape(Mage::registry('storelocation_data')->getStoreName()));
        } else {
            return Mage::helper('growdevstorelocations')->__('Add Location');
        }
    }


}