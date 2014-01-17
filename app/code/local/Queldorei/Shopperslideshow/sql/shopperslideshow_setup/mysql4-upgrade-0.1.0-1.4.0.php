<?php
/**
 * @version   1.0 12.0.2012
 * @author    queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 queldorei
 */

$installer = $this;
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer->startSetup();
/* flexslider */
$installer->setConfigData('shopperslideshow/config/slider', 'flexslider');
$installer->setConfigData('shopperslideshow/flexslider/height', '');
$installer->setConfigData('shopperslideshow/flexslider/animation', 'slide');
$installer->setConfigData('shopperslideshow/flexslider/slideshow', 'true');
$installer->setConfigData('shopperslideshow/flexslider/animation_loop', 'true');
$installer->setConfigData('shopperslideshow/flexslider/mousewheel', 'false');
$installer->setConfigData('shopperslideshow/flexslider/smoothheight', 'false');
$installer->setConfigData('shopperslideshow/flexslider/slideshow_speed', '7000');
$installer->setConfigData('shopperslideshow/flexslider/animation_speed', '400');
$installer->setConfigData('shopperslideshow/flexslider/control_nav', 'false');
$installer->setConfigData('shopperslideshow/flexslider/direction_nav', 'true');
$installer->setConfigData('shopperslideshow/flexslider/timeline', 'true');
/* revolution slider */
$installer->setConfigData('shopperslideshow/revolutionslider/delay', '9000');
$installer->setConfigData('shopperslideshow/revolutionslider/startheight', '460');
$installer->setConfigData('shopperslideshow/revolutionslider/startwidth', '1170');
$installer->setConfigData('shopperslideshow/revolutionslider/hideThumbs', '200');
$installer->setConfigData('shopperslideshow/revolutionslider/navigationType', 'bullet');
$installer->setConfigData('shopperslideshow/revolutionslider/navigationArrows', 'verticalcentered');
$installer->setConfigData('shopperslideshow/revolutionslider/navigationStyle', 'round');
$installer->setConfigData('shopperslideshow/revolutionslider/touchenabled', 'on');
$installer->setConfigData('shopperslideshow/revolutionslider/navOffsetHorizontal', '0');
$installer->setConfigData('shopperslideshow/revolutionslider/navOffsetVertical', '-14');
$installer->setConfigData('shopperslideshow/revolutionslider/onHoverStop', 'on');
$installer->setConfigData('shopperslideshow/revolutionslider/thumbWidth', '100');
$installer->setConfigData('shopperslideshow/revolutionslider/thumbHeight', '50');
$installer->setConfigData('shopperslideshow/revolutionslider/thumbAmount', '5');
$installer->setConfigData('shopperslideshow/revolutionslider/hideCaptionAtLimit', '');
$installer->setConfigData('shopperslideshow/revolutionslider/hideAllCaptionAtLilmit', '');
$installer->setConfigData('shopperslideshow/revolutionslider/hideSliderAtLimit', '');
$installer->setConfigData('shopperslideshow/revolutionslider/stopAtSlide', '-1');
$installer->setConfigData('shopperslideshow/revolutionslider/stopAfterLoops', '-1');
$installer->setConfigData('shopperslideshow/revolutionslider/timeline', '1');
$installer->setConfigData('shopperslideshow/revolutionslider/timeline_position', 'top');

$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('shopperslideshow/revolution_slides')}`;
CREATE TABLE `{$this->getTable('shopperslideshow/revolution_slides')}` (
  `slide_id` int(11) unsigned NOT NULL auto_increment,
  `transition` text NOT NULL default '',
  `masterspeed` text NOT NULL default '',
  `slotamount` text NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `thumb` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `text` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `sort_order` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`slide_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{$this->getTable('shopperslideshow/revolution_slides')}` (`slide_id`, `transition`, `masterspeed`, `slotamount`, `link`, `thumb`, `image`, `text`, `status`, `sort_order`, `created_time`, `update_time`) VALUES
	(1, 'papercut', '460', '1', '', '', 'queldorei/shopper/revolution/features_02.jpg', '', 1, 2, '2013-01-05 16:16:16', '2013-01-05 16:16:16'),
	(2, 'fade', '300', '1', '', '', 'queldorei/shopper/revolution/features_05.jpg', '', 1, 1, '2013-01-05 16:17:06', '2013-01-05 16:17:06'),
	(3, 'slideleft', '300', '1', '', '', 'queldorei/shopper/revolution/unique_magento_theme.jpg', '', 1, 2, '2013-01-05 16:18:06', '2013-01-05 16:18:06'),
	(4, 'slidedown', '300', '7', '', '', 'queldorei/shopper/revolution/black.jpg', '', 1, 3, '2013-01-05 16:21:20', '2013-01-05 16:21:20');

");

/**
 * Drop 'slides_store' table
 */
$conn = $installer->getConnection();
$conn->dropTable($installer->getTable('shopperslideshow/revolution_slides_store'));

/**
 * Create table for stores
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('shopperslideshow/revolution_slides_store'))
    ->addColumn('slide_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'nullable'  => false,
    'primary'   => true,
), 'Slide ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Store ID')
    ->addIndex($installer->getIdxName('shopperslideshow/revolution_slides_store', array('store_id')),
    array('store_id'))
    ->addForeignKey($installer->getFkName('shopperslideshow/revolution_slides_store', 'slide_id', 'shopperslideshow/revolution_slides', 'slide_id'),
    'slide_id', $installer->getTable('shopperslideshow/revolution_slides'), 'slide_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('shopperslideshow/revolution_slides_store', 'store_id', 'core/store', 'store_id'),
    'store_id', $installer->getTable('core/store'), 'store_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Slide To Store Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Assign 'all store views' to existing slides
 */
$installer->run("INSERT INTO {$this->getTable('shopperslideshow/revolution_slides_store')} (`slide_id`, `store_id`) SELECT `slide_id`, 0 FROM {$this->getTable('shopperslideshow/revolution_slides')};");

$installer->endSetup();

/**
 * add slide data
 */
$data = array(
    1 => '
<div class="caption sfr shopper_caption_light"  data-x="530" data-y="90" data-speed="500" data-start="500" data-easing="easeOutBack">ELEGANT & FLEXIBLE</div>

<div class="caption sfl shopper_caption_light"  data-x="636" data-y="150" data-speed="500" data-start="500" data-easing="easeOutBack">SLIDESHOWS</div>

<div class="caption fade shopper_medium_text_light" style="color:#706f71;" data-x="600" data-y="286" data-speed="500" data-start="1100" data-easing="easeOutExpo">This theme comes with two flexible slideshows!</div>

<div class="caption fade shopper_medium_text_light" style="color:#706f71;" data-x="609" data-y="316" data-speed="500" data-start="1100" data-easing="easeOutExpo">that gives you full control over your slideshow</div>

<div class="caption fade shopper_medium_text_light" style="color:#706f71;" data-x="712" data-y="346" data-speed="500" data-start="1100" data-easing="easeOutExpo">content and animation.</div>\r\n\r\n<div class="caption sfb shopper_white_bg_bold"  data-x="584" data-y="230" data-speed="500" data-start="800" data-easing="easeOutExpo">FLEXSLIDER & REVOLUTION</div>',
    '
<div class="caption fade shopper_caption_underline_light"  data-x="650" data-y="138" data-start="100" data-speed="800"  data-easing="easeOutExpo">TRUE BEAUTY</div>

<div class="caption fade shopper_small_text_light" data-x="689" data-y="222" data-speed="800" data-start="300" data-easing="easeOutExpo">This unique magento theme is the combination </div>

<div class="caption fade shopper_small_text_light" data-x="702" data-y="242" data-speed="800" data-start="600" data-easing="easeOutExpo">of technical excellence and aesthetic vision.</div>

<div class="caption fade shopper_small_text_light" data-x="768" data-y="275" data-speed="800" data-start="800" data-easing="easeOutExpo"><a href="http://themeforest.net/item/shopper-magento-theme-responsive-retina-ready/3139960"><img src="{{media url="queldorei/shopper/samples/button.png"}}" alt="buy now"></a></div>',
    '
<div class="caption shopper_large_caption_bold sft" data-x="611" data-y="80" data-speed="500" data-start="600" data-easing="easeOutExpo"  >UNIQUE</div>

<div class="caption large_black_text sft"  data-x="612" data-y="161" data-speed="500" data-start="700" data-easing="easeOutExpo"  >MAGENTO THEME</div>

<div class="caption sfb"  data-x="610" data-y="223" data-speed="350" data-start="1000" data-easing="easeOutBack"  ><img src="{{media url="queldorei/shopper/samples/img_01.png"}}" alt="image"></div>

<div class="caption sfb"  data-x="850" data-y="223" data-speed="350" data-start="1100" data-easing="easeOutBack"  ><img src="{{media url="queldorei/shopper/samples/img_02.png"}}" alt="image"></div>',
    '
<div class="caption fade fullscreenvideo" data-autoplay="false" data-x="0" data-y="0" data-speed="500" data-start="10" data-easing="easeOutBack"><iframe src="http://player.vimeo.com/video/22775048?title=0&amp;byline=0&amp;portrait=0;api=1" width="50" height="50"></iframe></div>

<div class="caption big_white sft stt" data-x="327" data-y="25" data-speed="300" data-start="500" data-easing="easeOutExpo" data-end="4000" data-endspeed="300" data-endeasing="easeInSine" >Have Fun Watching the Video</div>',
);

$model = Mage::getModel('shopperslideshow/shopperrevolution');
foreach ( $data as $k => $v ) {
    $model->load($k)
        ->setText($v)
        ->save();
}