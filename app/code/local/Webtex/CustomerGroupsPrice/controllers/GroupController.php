<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @copyright  Copyright (c) 2010 Webtex (http://www.webtex.com/)
 * @license    http://www.webtex.com/LICENSE-1.0.html
 */

/**
 * Customer Groups Price extension
 *
 * @category   Webtex
 * @package    Webtex_CustomerGroupsPrice
 * @author     Webtex Dev Team <dev@webtex.com>
 */

class Webtex_CustomerGroupsPrice_GroupController extends Mage_Adminhtml_Controller_Action
{
    public function saveAction()
    {
        $customerGroup = Mage::getModel('customer/group');
        $id = $this->getRequest()->getParam('id');
        $websiteId = Mage::app()->getStore($this->getRequest()->getParam('store'))->getWebsiteId();
        if (!is_null($id)) {
            $customerGroup->load($id);
        }

        if ($taxClass = $this->getRequest()->getParam('tax_class')) {
            try {
                $customerGroup->setCode($this->getRequest()->getParam('code'))
                    ->setTaxClassId($taxClass)
                    ->save();

                $request = Mage::app()->getRequest();
                $price = $request->getParam('price');
                $priceType = $request->getParam('price_type');
                $db = Mage::getModel('core/resource')->getConnection('core_write');

                if($id){
                    $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
                    $globalPrice = Mage::getModel('customergroupsprice/globalprices')->loadPrice($id);
                    if($price && $price !== 0){
                        if($globalPrice && $globalPrice->getId() && $globalPrice->getWebsiteId() == $websiteId){
                            $query = 'update '.$tablePrefix.'customergroupsprice_prices_global set
                                price = "'.$price.'", price_type = '.$priceType.'
                                where group_id = '.$id.' and website_id = '.$websiteId;
                        } else {
                            $query = 'insert into '.$tablePrefix.'customergroupsprice_prices_global(group_id, price, price_type, website_id)
                                            values('.$id.', "'.$price.'", '.$priceType.', '. $websiteId .')';
                        }
                    } elseif($globalPrice){
                        $query = 'delete from '.$tablePrefix.'customergroupsprice_prices_global where group_id = '.$id;
                    }

                    $db->query($query);
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customer')->__('The customer group has been saved.'));
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCustomerGroupData($customerGroup->getData());
                $this->getResponse()->setRedirect($this->getUrl('*/customer_group/edit', array('id' => $id)));
                return;
            }
        } else {
            $this->_forward('new');
        }

    }
}
 
