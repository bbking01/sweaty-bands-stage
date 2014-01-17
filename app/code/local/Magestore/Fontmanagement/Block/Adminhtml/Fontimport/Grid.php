<?php

class Magestore_Fontmanagement_Block_Adminhtml_Fontimport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
    {
        parent::__construct();
        $this->setTemplate('font/importFont.phtml');
    }

}