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


class AW_Points_Block_Adminhtml_Customer_Edit_Tabs_RewardPoints_Notifications extends Mage_Adminhtml_Block_Widget_Form {

    protected function _construct() {
        parent::_construct();
        $this->setData('customer', Mage::registry('current_customer'));
        $this->addData(
                Mage::getModel('points/summary')
                        ->loadByCustomer($this->getCustomer())->getData());
    }

    public function initForm() {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_aw_points_notification');

        $fieldset = $form->addFieldset('notification_fieldset', array('legend' => Mage::helper('points')->__('Reward Points Notification')));

        $fieldset->addField('balance_update_notification', 'checkbox', array(
            'label' => Mage::helper('points')->__('Subscribe to balance update'),
            'name' => 'balance_update_notification',
            'id' => 'aw_points_balance_update_notification',
            'value' => 1,
            'checked' => (bool) (int) $this->getBalanceUpdateNotification()
        ));

        $fieldset->addField('points_expire_notification', 'checkbox', array(
            'label' => Mage::helper('points')->__('Subscribe to points expiration notification'),
            'name' => 'points_expire_notification',
            'id' => 'aw_points_points_expire_notification',
            'value' => 1,
            'checked' => (bool) (int) $this->getPointsExpirationNotification()
        ));

        $this->setForm($form);
        return $this;
    }

}

