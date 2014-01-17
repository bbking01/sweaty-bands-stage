<?php
/**
 * Unirgy_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_StoreLocator
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_StoreLocator
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_StoreLocator_Block_Adminhtml_Location_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'ustorelocator';
        $this->_controller = 'adminhtml_location';

        $this->_updateButton('save', 'label', Mage::helper('ustorelocator')->__('Save Location'));
        $this->_updateButton('delete', 'label', Mage::helper('ustorelocator')->__('Delete Location'));
        $this->_addButton('save_edit', array(
                                       'label'     => Mage::helper('catalog')->__('Save and Continue Edit'),
                                       'onclick'   => 'editForm.submit(\''.$this->getSaveAndContinueUrl().'\');',
                                       'class'     => 'save',
                                  ), 1);

        if( $this->getRequest()->getParam($this->_objectId) ) {
            $model = Mage::getModel('ustorelocator/location')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('location_data', $model);
        }


    }

    public function getHeaderText()
    {
        if( Mage::registry('location_data') && Mage::registry('location_data')->getId() ) {
            return Mage::helper('ustorelocator')->__("Edit Location", $this->htmlEscape(Mage::registry('location_data')->getTitle()));
        } else {
            return Mage::helper('ustorelocator')->__('New Location');
        }
    }

    private function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
                                              'back'       => 'edit',
                                              $this->_objectId => $this->getRequest()->getParam($this->_objectId)
                                         ));
    }
}
