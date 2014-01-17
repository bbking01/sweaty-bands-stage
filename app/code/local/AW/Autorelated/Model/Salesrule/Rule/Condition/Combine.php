<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Autorelated
 * @version    2.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Autorelated_Model_SalesRule_Rule_Condition_Combine extends Mage_SalesRule_Model_Rule_Condition_Combine
{
    public function getConditions()
    {
        if ($this->getData($this->getPrefix()) === null)
            $this->setData($this->getPrefix(), array());
        return $this->getData($this->getPrefix());
    }

    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();
        foreach ($conditions as $index => $condition) {
            if (isset($condition['value']) && $condition['value'] == 'salesrule/rule_condition_combine') {
                $conditions[$index]['value'] = 'awautorelated/salesrule_rule_condition_combine';
                break;
            }
        }
        return $conditions;
    }
}
