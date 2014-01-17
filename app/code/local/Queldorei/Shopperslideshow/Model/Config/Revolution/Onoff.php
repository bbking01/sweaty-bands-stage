<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Model_Config_Revolution_Onoff
{
    public function toOptionArray()
    {
	    $options = array();
	    $options[] = array(
            'value' => 'on',
            'label' => 'On',
        );
        $options[] = array(
            'value' => 'off',
            'label' => 'Off',
        );

        return $options;
    }

}
