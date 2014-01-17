<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shopperslideshow_Model_Config_Revolution_Timeline
{
    public function toOptionArray()
    {
	    $options = array();
	    $options[] = array(
            'value' => 'top',
            'label' => 'top',
        );
        $options[] = array(
            'value' => 'bottom',
            'label' => 'bottom',
        );

        return $options;
    }

}
