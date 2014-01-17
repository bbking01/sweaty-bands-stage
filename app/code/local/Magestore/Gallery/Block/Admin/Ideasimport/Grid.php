<?php

class Magestore_Gallery_Block_Admin_Ideasimport_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
    {
       	parent::__construct();
        $this->setTemplate('designidea/importIdeas.phtml');
    }

}