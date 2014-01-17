<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-11-11
 * Time: 20:31
 */
 
abstract class Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit_Tab_Abstract
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * @var Varien_Data_Form_Element_Abstract
     */
    protected $_element;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return Unirgy_Giftcert_Block_Adminhtml_Pdf_Edit_Tab_Abstract
     */
    public function setElement($element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        $values = array();
        $data   = $this->getElement()->getValue();

        if (is_array($data)) {
//            usort($data, array($this, '_sortData'));
            $values = $data;
        }

        return $values;
    }

    /**
     * @abstract
     * @param array $a
     * @param array $b
     * @return int
     */
    abstract protected function _sortData($a, $b);

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
}
