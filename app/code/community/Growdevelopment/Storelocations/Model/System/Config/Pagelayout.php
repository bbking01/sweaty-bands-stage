<?php
/**
 * Grow Development - Store Locations Pro
 *
 * @category   Growdevelopment
 * @package    Growdevelopment_StoreLocations
 * @copyright  Copyright (c) 2012 Grow Development (http://www.growdevelopment.com)
 * @license    http://www.growdevelopment.com/docs/eula.txt
 * @author     Daniel Espinoza <modules@growdevelopment.com>
 *
 */
class Growdevelopment_Storelocations_Model_System_Config_Pagelyout {

	protected $_options;
    
    public function toOptionArray()
    {
        if (!$this->_options) {
            $layouts = array();
			$node = Mage::getConfig()->getNode('global/cms/layouts') ? Mage::getConfig()->getNode('global/cms/layouts') : Mage::getConfig()->getNode('global/page/layouts');
			
			foreach ($node->children() as $layoutConfig) {
				$this->_options[] = array(
				   'value'=>(string)$layoutConfig->template,
				   'label'=>(string)$layoutConfig->label
				);
			}
			
		}
        return $this->_options;
    }
}
