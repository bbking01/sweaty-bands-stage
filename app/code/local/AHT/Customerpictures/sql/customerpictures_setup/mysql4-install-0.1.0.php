<?php

$installer = $this;

$installer->startSetup();

if (!$installer->tableExists($installer->getTable('customerpictures_images'))) {
	$installer->run("

		-- DROP TABLE IF EXISTS {$this->getTable('customerpictures_images')};
		CREATE TABLE {$this->getTable('customerpictures_images')} (
		  `customerpictures_image_id` int(11) unsigned NOT NULL auto_increment,
		  `user_id` int(11) NOT NULL default '0',
		  `image_name` varchar(255) NOT NULL default '',
		  `image_title` varchar(255) NOT NULL default '',
		  `image_description` text NOT NULL default '',
		  `status` smallint(6) NOT NULL default '0',
		  `user_status` smallint(6) NOT NULL default '0',
		  `liked` int(11) NOT NULL default '0',
		  `viewed` int(11) NOT NULL default '0',
		  `position_x` varchar(255) NOT NULL default '',
		  `position_y` varchar(255) NOT NULL default '',
		  `position_w` varchar(255) NOT NULL default '',
		  `position_h` varchar(255) NOT NULL default '',
		  `winner_time` varchar(255) NOT NULL default '',
		  `created_time` varchar(255) NOT NULL default '',
		  `update_time` varchar(255) NOT NULL default '',
		  PRIMARY KEY (`customerpictures_image_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;

		-- DROP TABLE IF EXISTS {$this->getTable('customerpictures_user')};
		CREATE TABLE {$this->getTable('customerpictures_user')} (
		  `user_id` int(11) unsigned NOT NULL,
		  `avatar` varchar(255) NOT NULL default '',
		  `term_condition` smallint(6) NOT NULL default '0',
		  PRIMARY KEY (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
}

$configValuesMap = array(
  'customerpictures/general/email' =>'customerpictures_coupon_email_template',
  'customerpictures/general/notice_email'=>'customerpictures_notice_email_template',
);

foreach ($configValuesMap as $configPath=>$configValue) {
    $installer->setConfigData($configPath, $configValue);
}

$installer->endSetup(); 