<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Model_Config_Revolution_Navbar
{
    public function toOptionArray()
    {
	    $options = array();
        $options[] = array(
            'value' => 'none',
            'label' => 'none',
        );
	    $options[] = array(
            'value' => 'bullet',
            'label' => 'bullet',
        );
        $options[] = array(
            'value' => 'thumb',
            'label' => 'thumb',
        );
        $options[] = array(
            'value' => 'both',
            'label' => 'both',
        );

        return $options;
    }

}
