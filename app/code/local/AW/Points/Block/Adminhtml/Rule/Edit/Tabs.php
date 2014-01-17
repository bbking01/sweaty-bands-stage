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


class AW_Points_Block_Adminhtml_Rule_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('rule_id');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('points')->__('Points & Reward Rule'));
    }

    protected function _beforeToHtml() {
        $helper = Mage::helper('points');

        $this->addTab('main_section', array(
            'label' => $helper->__('Rule Information'),
            'title' => $helper->__('Rule Information'),
            'content' => $this->getLayout()->createBlock('points/adminhtml_rule_edit_tab_main')->toHtml(),
            'active' => true
        ));

        $this->addTab('conditions_section', array(
            'label' => $helper->__('Conditions'),
            'title' => $helper->__('Conditions'),
            'content' => $this->getLayout()->createBlock('points/adminhtml_rule_edit_tab_conditions')->toHtml(),
        ));

        $this->addTab('actions_section', array(
            'label' => $helper->__('Actions'),
            'title' => $helper->__('Actions'),
            'content' => $this->getLayout()->createBlock('points/adminhtml_rule_edit_tab_actions')->toHtml(),
        ));

        $relatedGridBlock = $this->getLayout()
                ->createBlock('points/adminhtml_rule_edit_tab_related');

        $relatedBlock = $this->getLayout()
                ->createBlock('points/adminhtml_rule_edit_tab_related')
                ->setTemplate('aw_points/rule/edit/tab/related.phtml')
                ->setChild('related.grid', $relatedGridBlock);

        $this->addTab('related_blocks', array(
            'label' => $helper->__('Related Blocks'),
            'title' => $helper->__('Related Blocks'),
            'content' => $relatedBlock->toHtml()
        ));

        return parent::_beforeToHtml();
    }

}
