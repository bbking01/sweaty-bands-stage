<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 */

class Unirgy_Giftcert_Block_Adminhtml_Report_Renderer_Currency
    extends Mage_Adminhtml_Block_Report_Grid_Column_Renderer_Currency
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $value = parent::_getValue($row);
        if ($value) {
            $row->setData($this->getColumn()->getIndex(), $value);
        }
        return parent::render($row);
    }
}
