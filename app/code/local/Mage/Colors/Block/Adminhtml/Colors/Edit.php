<?php

class Mage_Colors_Block_Adminhtml_Colors_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'colors';
        $this->_controller = 'adminhtml_colors';
        
        $this->_updateButton('save', 'label', Mage::helper('colors')->__('Save Color Counter'));
        $this->_updateButton('delete', 'label', Mage::helper('colors')->__('Delete Color Counter'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('colors_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'colors_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'colors_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('colors_data') && Mage::registry('colors_data')->getId() ) {
            return Mage::helper('colors')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('colors_data')->getTitle()));
        } else {
            return Mage::helper('colors')->__('Add Color Counter');
        }
    }
}