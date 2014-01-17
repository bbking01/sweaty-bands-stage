<?php

class MW_Rewardpoints_Block_Adminhtml_Renderer_Rewardpoints extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
    	$result = '';
    	$id = $row['entity_id'];
    	//$class = "input-text validate-number validate-digits";
    	$name = 'reward_point_product[mw_'.$id.']';
    	$value = $row['reward_point_product'];
    	//$result = "<span style='display: block; margin: 0px 0px 0px 8px;'>".$value."</span><input type=".$type." class=".$class." name=".$name." value=".$value."></input>";
		$result = $value."  <input type='text' class='input-text validate-number validate-digits' name=".$name." value=".$value."></input>";
    	return $result;
    }

}