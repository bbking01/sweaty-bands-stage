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
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Adminhtml_CampaignController extends Mage_Adminhtml_Controller_Action
{
    protected function _initCampaign($idFieldName = 'id')
    {
        $campaignId = (int)$this->getRequest()->getParam($idFieldName);
        $campaign = Mage::getModel('awaffiliate/campaign');

        if ($campaignId) {
            $campaign->load($campaignId);
        }

        if ($data = $this->_getSession()->getCampaignData()) {
            $campaign->addData($data);
            $campaign->getProfitModel()->addData($data);
            $campaign->getProfitModel()->setType($campaign->getProfitModel()->getRateType());
            $campaign->getConditionsModel()->loadPost($data);

            $this->_getSession()->setCampaignData(null);
        }

        Mage::register('current_campaign', $campaign);
        return $this;
    }

    protected function _initAction()
    {
        $this->_title($this->__("Magento Affiliate"))
            ->_title($this->__("Manage Campaigns"));
        $this->loadLayout()
            ->_setActiveMenu('awaffiliate');
        return $this;
    }

    protected function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    protected function newAction()
    {
        return $this->_redirect('*/*/edit');
    }

    protected function editAction()
    {
        $this->_initCampaign();
        $this->_initAction();
        $_campaign = Mage::registry('current_campaign');
        $this->_title($_campaign->hasId() ? $_campaign->getName() : $this->__("New Campaign"));
        $this->renderLayout();
    }

    protected function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if ($data) {
            $redirectBack = $this->getRequest()->getParam('back', false);
            $this->_initCampaign('campaign_id');

            /* @var $campaign AW_Affiliate_Model_Campaign */
            $campaign = Mage::registry('current_campaign');

            if (Mage::app()->isSingleStoreMode()) {
                $data['store_ids'] = Mage::app()->getWebsite(true)->getId();
            }

            //pre save validate
            $_helper = Mage::helper('awaffiliate');
            if (!$_helper->campaignEditPreSaveValidateData($data)) {
                $this->_getSession()->addError(Mage::helper('awaffiliate')->__('Invalid data for save'));
                $this->_getSession()->setCampaignData($data);
                $this->_redirect('*/*/edit', array('id' => $campaign->getId(), '_current' => true));
                return;
            }

            if (isset($data["profit_rate_cur"])) {
                $data['profit_rate_cur'] = floatval($data['profit_rate_cur']);
            }
            if (isset($data["profit_rate"])) {
                $data['profit_rate'] = floatval($data['profit_rate']);
            }

            $campaign->addData($data);

            /* @var $campaign AW_Affiliate_Model_Profit */
            $profit = $campaign->getProfitModel();
            $profit
                ->setType($data['rate_type'])
                ->setProfitRate($data['profit_rate'])
                ->setProfitRateCur($data['profit_rate_cur'])
                ->addData($data['rate_settings']);

            //tier pricing process
            if (($data['rate_type'] == AW_Affiliate_Model_Source_Profit_Type::TIER) && isset($data['tier_price'])) {
                $tiersToSave = array();
                foreach ($data['tier_price'] as $tierItem) {
                    if ($tierItem['delete'])
                        continue;
                    $tiersToSave[] = array(
                        'profit_rate' => floatval($tierItem['rate']),
                        'profit_amount' => $tierItem['amount'],
                        'affiliate_group_id' => $tierItem['cust_group'],
                    );
                }
                if (count($tiersToSave)) {
                    $profit->unsetData('profit_rate');
                }
            }

            if (($data['rate_type'] == AW_Affiliate_Model_Source_Profit_Type::TIER) && !isset($data['tier_price'])) {
                $this->_getSession()->addError(Mage::helper('awaffiliate')->__('"Amount" field shouldn\'t be empty'));
                $this->_getSession()->setCampaignData($data);
                $this->_redirect('*/*/edit', array('id' => $campaign->getId(), '_current' => true));
                return;
            }


            //tier pricing currency process
            if (($data['rate_type'] == AW_Affiliate_Model_Source_Profit_Type::TIER_CUR) && isset($data['tier_price_cur'])) {
                $tiersToSave = array();
                foreach ($data['tier_price_cur'] as $tierItem) {
                    if ($tierItem['delete'])
                        continue;
                    $tiersToSave[] = array(
                        'profit_rate' => floatval($tierItem['rate']),
                        'profit_amount' => $tierItem['amount'],
                        'affiliate_group_id' => $tierItem['cust_group'],
                    );
                }
                if (count($tiersToSave)) {
                    $profit->unsetData('profit_rate');
                }
            }

            if (($data['rate_type'] == AW_Affiliate_Model_Source_Profit_Type::TIER_CUR) && !isset($data['tier_price_cur'])) {
                $this->_getSession()->addError(Mage::helper('awaffiliate')->__('"Amount" field shouldn\'t be empty'));
                $this->_getSession()->setCampaignData($data);
                $this->_redirect('*/*/edit', array('id' => $campaign->getId(), '_current' => true));
                return false;
            }

            if (($data['rate_type'] == AW_Affiliate_Model_Source_Profit_Type::FIXED) && !($data['profit_rate'] > 0)) {
                $this->_getSession()->addError(Mage::helper('awaffiliate')->__('"Amount" field should be greater than 0'));
                $this->_getSession()->setCampaignData($data);
                $this->_redirect('*/*/edit', array('id' => $campaign->getId(), '_current' => true));
                return false;
            }

            if (($data['rate_type'] == AW_Affiliate_Model_Source_Profit_Type::FIXED_CUR) && !($data['profit_rate_cur'] > 0)) {
                $this->_getSession()->addError(Mage::helper('awaffiliate')->__('"Amount" field should be greater than 0'));
                $this->_getSession()->setCampaignData($data);
                $this->_redirect('*/*/edit', array('id' => $campaign->getId(), '_current' => true));
                return false;
            }

            if (isset($data['active_from']) && $data['active_from'] && ($_activeFrom = strtotime($data['active_from']))) {
                $data['active_from'] = date(AW_Affiliate_Model_Resource_Campaign::MYSQL_DATE_FORMAT, $_activeFrom);
            } else {
                $data['active_from'] = null;
            }
            if (isset($data['active_to']) && $data['active_to'] && ($_activeTo = strtotime($data['active_to']))) {
                $data['active_to'] = date(AW_Affiliate_Model_Resource_Campaign::MYSQL_DATE_FORMAT, $_activeTo);
            } else {
                $data['active_to'] = null;
            }

            $campaign->addData($data);

            //conditions process
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            unset($data['rule']);
            $campaign->getConditionsModel()->loadPost($data);

            try {
                $campaign->save();
                if ($profit->isObjectNew()) {
                    $profit->setCampaignId($campaign->getId());
                }

                $profit->save();
                if (isset($tiersToSave)) {
                    $tierRates = Mage::getModel('awaffiliate/profit_tier_rate');
                    $tierRates->removeAllTiersByProfitId($profit->getId());
                    foreach ($tiersToSave as $data) {
                        $tierRates->unsetData()
                            ->setProfitId($profit->getId())
                            ->addData($data)
                            ->save();
                    }
                }

                $this->_getSession()->addSuccess(Mage::helper('awaffiliate')->__('The campaign has been saved.'));
                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array('id' => $campaign->getId(), '_current' => true));
                    return;
                }
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('awaffiliate')->__('An error occurred while saving the campaign.'));

                $this->_getSession()->setCampaignData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('id' => $campaign->getId())));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/adminhtml_campaign'));
    }

    protected function deleteAction()
    {
        $this->_initCampaign();
        /* @var $campaign AW_Affiliate_Model_Campaign */
        $campaign = Mage::registry('current_campaign');
        if ($campaign->hasId()) {
            /* @var $campaign AW_Affiliate_Model_Profit */
            $profit = $campaign->getProfitModel();
            try {
                $campaign->delete();
                if ($profit->hasId()) {
                    $profit->delete();
                    $tierRates = Mage::getModel('awaffiliate/profit_tier_rate');
                    $tierRates->removeAllTiersByProfitId($profit->getId());
                }
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                    Mage::helper('awaffiliate')->__('An error occurred while removing the campaign.'));
                $this->getResponse()->setRedirect($this->getUrl('*/*/edit', array('id' => $campaign->getId())));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/adminhtml_campaign'));
    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (Mage::helper('awaffiliate')->isMageVersionGreathOrEqual('1.4.0.0')) {
            return parent::_title($text, $resetIfExists);
        }
        return $this;
    }

    protected function _isAllowed()
    {
        $acl = 'awaffiliate/campaigns';
        return Mage::getSingleton('admin/session')->isAllowed($acl);
    }
}
