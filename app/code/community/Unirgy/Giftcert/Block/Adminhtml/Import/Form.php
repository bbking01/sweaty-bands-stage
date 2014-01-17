<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-8-11
 * Time: 11:51
 * To change this template use File | Settings | File Templates.
 */

class Unirgy_Giftcert_Block_Adminhtml_Import_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
                                          'id' => 'edit_form',
                                          'action' => $this->getUrl('*/*/saveimport'),
                                          'method' => 'post',
                                     )
        );
        $form->setUseContainer(true);

        $this->setForm($form);
        $hlp = Mage::helper('ugiftcert');

        $fieldSet = $form->addFieldset('giftcert_import_form', array('legend' => $hlp->__('Gift certificate import settings.')));

        $fieldSet->addField('delimiter', 'text', array(
            'name'      => 'delimiter',
            'label'     => $hlp->__('CSV field delimiter'),
            'class'     => 'required-entry',
            'required'  => true,
            'note'      => $hlp->__('Enter one character to be used as field delimiter when parsing imported CSV file.'),
            'value'     => Mage::getStoreConfig('ugiftcert/import/delimiter'),
        ));

        $fieldSet->addField('enclosure', 'text', array(
            'name'      => 'enclosure',
            'label'     => $hlp->__('CSV field enclosure'),
            'class'     => 'required-entry',
            'required'  => true,
            'note'      => $hlp->__('Enter one character to be used as field enclosure when parsing imported CSV file.'),
            'value'     => Mage::getStoreConfig('ugiftcert/import/enclosure'),
        ));

        $fieldSet->addField('file', 'text', array(
            'name'      => 'file',
            'label'     => $hlp->__('CSV file name'),
            'class'     => 'required-entry',
            'required'  => true,
            'note'      => $hlp->__('Enter name of file to be used for import. File should be placed in "/var/ugiftcert/".<br/>You can use file uploader bellow to upload your files, or you can upload via FTP.'),
            'value'     => Mage::getStoreConfig('ugiftcert/import/file'),
        ));

        $fieldSet->addField('action', 'select', array(
            'name'      => 'action',
            'label'     => $hlp->__('When importing allow only:'),
            'class'     => 'scalable',
            'value'     => Mage::getStoreConfig('ugiftcert/import/action'),
            'options'   => array(Unirgy_Giftcert_Helper_Import::INS     => $hlp->__('New certificates'),
                                 Unirgy_Giftcert_Helper_Import::UPD     => $hlp->__('Updates'),
                                 Unirgy_Giftcert_Helper_Import::BOTH    => $hlp->__('Updates and New certificates'))
        ));

        return parent::_prepareForm();
    }
}
