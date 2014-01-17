<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-10-21
 * Time: 1:11
 */

class Unirgy_StoreLocator_Block_Adminhtml_Export
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $buttonBlock = $element->getForm()->getParent()->getLayout()->createBlock('adminhtml/widget_button');
        $hlp = Mage::helper('ustorelocator');
        $params = array();
        if($id = $buttonBlock->getRequest()->getParam('store')) {
            $params['store'] = $id;
        } else if( $id = $buttonBlock->getRequest()->getParam('website')){
            $params['website'] = $id;
        }

        $data = array(
            'label'     => $hlp->__('Export Locations'),
            'onclick'   => 'setLocation(\'' . $this->getUrl("ustorelocatoradmin/adminhtml_location/export", $params) . '\')',
            'class'     => '',
        );

        $html = $buttonBlock->setData($data)->toHtml();

        return $html;
    }
}
