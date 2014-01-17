<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-8-11
 * Time: 11:24
 * To change this template use File | Settings | File Templates.
 */

class Unirgy_Giftcert_Block_Adminhtml_Import
    extends Mage_Adminhtml_Block_Widget_Container
{
    protected function _construct()
    {
        $hlp = Mage::helper('ugiftcert');
        $this->setTemplate('ugiftcert/import.phtml');
        $this->_headerText = $hlp->__('Import Gift Certificates');

        $this->_addButton('reset', array(
                                        'label' => Mage::helper('adminhtml')->__('Reset'),
                                        'onclick' => 'setLocation(window.location.href)',
                                   ), -1);

        $this->_addButton('save', array(
                                       'label' => $hlp->__('Save Settings'),
                                       'onclick' => 'editForm.submit();',
                                       'class' => 'save',
                                  ));
        $this->_addButton('save_import', array(
                                              'label' => $hlp->__('Save and Import'),
                                              'onclick' => 'editForm.submit(\'' . $this->getUrl('*/*/saveimport', array('doimport' => 1)) . '\');',
                                              'class' => 'save',
                                         ));
    }

    protected function _prepareLayout()
    {
        $this->setChild('uploader',
                        $this->getLayout()->createBlock('adminhtml/media_uploader')
        );

        $this->setChild('form',
                        $this->getLayout()->createBlock('ugiftcert/adminhtml_import_form'));

        $this->getUploader()->getConfig()
                ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/upload'))
                ->setFileField('file')
                ->setFilters(array(
                                  'csv' => array(
                                      'label' => Mage::helper('adminhtml')->__('CSV and Tab Separated files (.csv, .txt)'),
                                      'files' => array('*.csv', '*.txt')
                                  ),
                                  'all' => array(
                                      'label' => Mage::helper('adminhtml')->__('All Files'),
                                      'files' => array('*.*')
                                  )
                             ));

        return parent::_prepareLayout();
    }


    /**
     * Retrive uploader block
     *
     * @return Mage_Adminhtml_Block_Media_Uploader
     */
    public function getUploader()
    {
        return $this->getChild('uploader');
    }
}
