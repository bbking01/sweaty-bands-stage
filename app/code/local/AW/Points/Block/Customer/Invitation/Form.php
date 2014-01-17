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


class AW_Points_Block_Customer_Invitation_Form extends Mage_Core_Block_Template {
    const MAX_INVITATIONS_PER_SEND = 5;

    /**
     * Get back url to invitations
     *
     * @return string
     */
    public function getBackUrl() {
        return $this->getUrl('points/invitation/');
    }

    /**
     * Get number of inputboxes for email adresses
     *
     * @return int
     */
    public function getMaxInvitationsPerSend() {
        return self::MAX_INVITATIONS_PER_SEND;
    }

    protected function _toHtml() {

        $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_14;

        if (Mage::helper('points')->magentoLess14())
            $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_13;

        $this->setTemplate("aw_points/customer/" . $magentoVersionTag . "/invitation/form.phtml");

        $html = parent::_toHtml();
        return $html;
    }

}
