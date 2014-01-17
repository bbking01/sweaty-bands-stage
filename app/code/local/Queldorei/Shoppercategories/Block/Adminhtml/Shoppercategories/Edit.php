<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shoppercategories_Block_Adminhtml_Shoppercategories_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'shoppercategories';
        $this->_controller = 'adminhtml_shoppercategories';
        
        $this->_updateButton('save', 'label', Mage::helper('shoppercategories')->__('Save Scheme'));
        $this->_updateButton('delete', 'label', Mage::helper('shoppercategories')->__('Delete Scheme'));
		
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
        if( Mage::registry('shoppercategories_data') && Mage::registry('shoppercategories_data')->getId() ) {
            return Mage::helper('shoppercategories')->__("Edit Scheme '%s'", $this->escapeHtml(Mage::registry('shoppercategories_data')->getCategoryId()));
        } else {
            return Mage::helper('shoppercategories')->__('Add Scheme');
        }
    }
}