<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Block_Adminhtml_Shopperslideshow_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'shopperslideshow';
        $this->_controller = 'adminhtml_shopperslideshow';
        
        $this->_updateButton('save', 'label', Mage::helper('shopperslideshow')->__('Save Slide'));
        $this->_updateButton('delete', 'label', Mage::helper('shopperslideshow')->__('Delete Slide'));
		
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

    public function getHeaderText()
    {
        if( Mage::registry('shopperslideshow_data') && Mage::registry('shopperslideshow_data')->getId() ) {
            return Mage::helper('shopperslideshow')->__("Edit Slide");
        } else {
            return Mage::helper('shopperslideshow')->__('Add Slide');
        }
    }
}