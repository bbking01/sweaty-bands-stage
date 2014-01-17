<?php
class Magestore_Gallery_Block_Ajax extends Mage_Core_Block_Template{

public function checkEmailDuplicationAjax()
    {
        return $this->helper('gallery')->checkEmailDuplicationAjaxUrl();
    } 
}	
?>	