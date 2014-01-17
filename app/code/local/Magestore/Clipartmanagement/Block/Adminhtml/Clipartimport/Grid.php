<?php

class Magestore_Clipartmanagement_Block_Adminhtml_Clipartimport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
    {
        parent::__construct();
        $this->setTemplate('clipart/importClipart.phtml');
    }

}