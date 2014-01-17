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


class AW_Autorelated_Test_Helper extends EcomDev_PHPUnit_Test_Case {
    protected static $_isAdminPrepared = null;

    public static function prepareAdminUser($renew = false) {
        if(self::$_isAdminPrepared === null || $renew) {
            Mage::getSingleton('core/session', array('name' => 'adminhtml'));
            $user = Mage::getModel('admin/user')->loadByUsername('master');
            if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                Mage::getSingleton('adminhtml/url')->renewSecretUrls();
            }
            $session = Mage::getSingleton('admin/session');
            $session->setIsFirstVisit(true);
            $session->setUser($user);
            $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
            self::$_isAdminPrepared = $session->isLoggedIn();
        }
        return self::$_isAdminPrepared;
    }

    public function preparePost($request) {
        $request->setMethod('POST');
        $request->setParam('form_key', $this->getFormKey());
    }

    public function getFormKey() {
        return Mage::getSingleton('core/session')->getFormKey();
    }
}
