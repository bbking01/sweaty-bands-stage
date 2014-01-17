<?php
/**
 * Unirgy_Giftcert extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @copyright  Copyright (c) 2008 Unirgy LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Unirgy
 * @package    Unirgy_Giftcert
 * @author     Boris (Moshe) Gurevich <moshe@unirgy.com>
 */
class Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId   = 'id';
        $this->_blockGroup = 'ugiftcert';
        $this->_controller = 'adminhtml_pdf';

        $this->_updateButton('save', 'label', Mage::helper('ugiftcert')->__('Save PDF Template'));
        $this->_updateButton('delete', 'label', Mage::helper('ugiftcert')->__('Delete PDF Template'));
        $this->_addButton('save_and_edit_button', array(
                'label'     => Mage::helper('catalog')->__('Save and Continue Edit'),
                'onclick'   => 'editForm.submit(\'' . $this->getSaveAndContinueUrl() . '\')',
                'class'     => 'save'
            ), 2
        );
    }

    public function getHeaderText()
    {
        if (Mage::registry('giftcert_pdf_tpl') && Mage::registry('giftcert_pdf_tpl')->getId()) {
            return Mage::helper('ugiftcert')->__("Edit: '%s'", $this->htmlEscape(Mage::registry('giftcert_pdf_tpl')->getData('title')));
        } else {
            return Mage::helper('ugiftcert')->__('New PDF Template');
        }
    }

    private function getSaveAndContinueUrl()
    {
        $model = Mage::registry('giftcert_pdf_tpl');
        return $this->getUrl('*/*/save', array(
            'back'           => 'edit',
            $this->_objectId => $model->getId()
        ));
    }
}
