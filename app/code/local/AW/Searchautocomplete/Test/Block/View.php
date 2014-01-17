<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Searchautocomplete
 * @version    3.2.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AW_Searchautocomplete_Test_Block_View extends EcomDev_PHPUnit_Test_Case {

    /**
     * @test
     * @static
     * @dataProvider provider__decorateWords
     * 
     */
    public function decorateWords($info) {

        $resultStr = AW_Searchautocomplete_Block_View::decorateWords(
                        $info['words'], $info['subject'], $info['before'], $info['after']
        );

        $subject = $info['subject'];

        foreach ($info['words'] as $k => $v) {
            $replacement = $info['before'] . $v . $info['after'];
            $subject = preg_replace("#" . $v . "#is", $replacement, $subject);
        }
        $expectedStr = $subject;

        $this->assertEquals($resultStr, $expectedStr);
    }

    public function provider__decorateWords() {

        return array(
            array(array('words' => array('word'), 'subject' => 'This is some word subject', 'before' => 'before', 'after' => 'after')),
            array(array('words' => array('test world'), 'subject' => 'This is some test world subject', 'before' => 'b', 'after' => 'a')),
            array(array('words' => array('test world', 'best day'), 'subject' => 'This is some test world and best day subject', 'before' => 'b', 'after' => 'a'))
        );
    }

    /**
     * @test
     * Incomplete test
     */
    public function getItems() {

        Mage::app()->getRequest()->setParam('q', 'testquery&books=12');
        Mage::app()->getStore()->setId(1);

        $sacBlock = new AW_Searchautocomplete_Block_View();
        $sacBlock->getItems();
    }

}