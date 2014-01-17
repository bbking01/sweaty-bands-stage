<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-7-25
 * Time: 16:29
 */

/**
 * Replacement class
 *
 * This class has to replace
 * Mage_Adminhtml_Block_System_Config_Form_Field_Heading
 * which is missing in 1.3 and early 1.4 Magento verisons.
 */
class Unirgy_Giftcert_Block_Adminhtml_Pdf_Heading
 extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
    }
}
