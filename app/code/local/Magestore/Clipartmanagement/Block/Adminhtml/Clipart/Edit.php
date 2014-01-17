<?php

class Magestore_Clipartmanagement_Block_Adminhtml_Clipart_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'clipartmanagement';
        $this->_controller = 'adminhtml_clipart';
        
        $this->_updateButton('save', 'label', Mage::helper('clipartmanagement')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('clipartmanagement')->__('Delete Item'));
		
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
        if( Mage::registry('clipartmanagement_data') && Mage::registry('clipartmanagement_data')->getId() ) {
            return Mage::helper('clipartmanagement')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('clipartmanagement_data')->getClipart_name()));
        } else {
            return Mage::helper('clipartmanagement')->__('Add Item');
        }
    }
}