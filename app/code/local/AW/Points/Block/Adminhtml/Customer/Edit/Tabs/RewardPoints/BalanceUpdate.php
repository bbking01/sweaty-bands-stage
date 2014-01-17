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
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Adminhtml_Customer_Edit_Tabs_RewardPoints_BalanceUpdate extends Mage_Adminhtml_Block_Widget_Form {

    public function initForm() {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_points');

        $fieldset = $form->addFieldset('points_fieldset', array('legend' => Mage::helper('points')->__('Update Reward Points Balance')));

        $fieldset->addField('update_points', 'text', array(
            'label' => Mage::helper('points')->__('Update Points'),
            'name' => 'aw_update_points',
            'note' => Mage::helper('points')->__('Enter a negative number to subtract from balance')
                )
        );

        $fieldset->addField('comment', 'text', array(
            'label' => Mage::helper('points')->__('Comment'),
            'name' => 'aw_update_points_comment'
                )
        );

        $this->setForm($form);
        return $this;
    }

}

