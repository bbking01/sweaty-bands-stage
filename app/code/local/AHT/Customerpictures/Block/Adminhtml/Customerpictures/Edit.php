<?php

class AHT_Customerpictures_Block_Adminhtml_Customerpictures_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'customerpictures';
        $this->_controller = 'adminhtml_customerpictures';
        
        $this->_updateButton('save', 'label', Mage::helper('customerpictures')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('customerpictures')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('customerpictures_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'customerpictures_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'customerpictures_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('customerpictures_data') && Mage::registry('customerpictures_data')->getId() ) {
            return Mage::helper('customerpictures')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('customerpictures_data')->getTitle()));
        } else {
            return Mage::helper('customerpictures')->__('Add Item');
        }
    }
}