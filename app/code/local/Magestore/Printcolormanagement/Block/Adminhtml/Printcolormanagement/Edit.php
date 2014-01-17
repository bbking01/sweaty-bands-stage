<?php

class Magestore_Printcolormanagement_Block_Adminhtml_Printcolormanagement_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'printcolormanagement';
        $this->_controller = 'adminhtml_printcolormanagement';
        
        $this->_updateButton('save', 'label', Mage::helper('printcolormanagement')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('printcolormanagement')->__('Delete Item'));
		
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
        if( Mage::registry('printcolormanagement_data') && Mage::registry('printcolormanagement_data')->getId() ) {
            return Mage::helper('printcolormanagement')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('printcolormanagement_data')->getColor_name()));
        } else {
            return Mage::helper('printcolormanagement')->__('Add Item');
        }
    }
}