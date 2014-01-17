<?php

class Magestore_Printcolormanagement_Block_Adminhtml_Printcolorimport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
    {
        parent::__construct();
        $this->setTemplate('printcolor/importPrintcolor.phtml');
    }

}