<?php
$this->startSetup();
$this->run("
	CREATE TABLE IF NOT EXISTS {$this->getTable('font_category')} (
	`font_cat_id` int(11) NOT NULL AUTO_INCREMENT,
	`category_name` varchar(255) NOT NULL,
	`position` int(11) NULL DEFAULT '0',
	`status` smallint(6) NOT NULL DEFAULT '1',
	`created_time` datetime DEFAULT NULL,
	`update_time` datetime DEFAULT NULL,
	PRIMARY KEY (`font_cat_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$this->run("
INSERT INTO `font_category` (`font_cat_id`, `category_name`, `position`, `status`, `created_time`, `update_time`) VALUES 
(1, 'Brushed', 1, 1, NULL, NULL),
(2, 'Calligraphy', 2, 1, NULL, NULL),
(3, 'Cartoon', 3, 1, NULL, NULL),
(4, 'Curly', 4, 1, NULL, NULL),
(5, 'Greek', 5, 1, NULL, NULL),
(6, 'Handwriting', 6, 1, NULL, NULL),
(7, 'Horror', 7, 1, NULL, NULL),
(8, 'Script', 8, 1, NULL, NULL),
(9, 'Sports', 9, 1, NULL, NULL);
");

$this->run("     
	CREATE TABLE IF NOT EXISTS {$this->getTable('fontmanagement')} (
	`font_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`font_category_id` int(11) NOT NULL,
    `font_category_name` varchar(255) NULL,
	`font_name` varchar(255) NOT NULL,
	`font_file` varchar(255) NOT NULL,	
	`status` smallint(6) NOT NULL DEFAULT '1',
	`font_image` varchar(255) NOT NULL,
	`created_time` datetime DEFAULT NULL,
	`update_time` datetime DEFAULT NULL,
	PRIMARY KEY (`font_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;     
");


$this->run(" 
INSERT INTO `fontmanagement` (`font_id`, `font_category_id`, `font_category_name`, `font_name`, `font_file`, `status`, `font_image`, `created_time`, `update_time`) VALUES 
(1, 1, 'Brushed', 'Blockography', 'Font_292056_blockography.swf', 1, '', NULL, NULL),
(2, 1, 'Brushed', 'bruxi', 'Font_292056_bruxi.swf', 1, '', NULL, NULL),
(3, 1, 'Brushed', 'Burning', 'Font_292056_burning_light.swf', 1, '', NULL, NULL),
(4, 1, 'Brushed', 'Charons Obol', 'Font_292056_charons_obol.swf', 1, '', NULL, NULL),
(5, 1, 'Brushed', 'CMSquish', 'Font_292056_cmsquish_medium.swf', 1, '', NULL, NULL),
(6, 1, 'Brushed', 'Nyctophobia', 'Font_292056_nyctophobia.swf', 1, '', NULL, NULL),
(7, 1, 'Brushed', 'Savia Filled Shadow', 'Font_292056_savia_filled_shadow.swf', 1, '', NULL, NULL),
(8, 2, 'Calligraphy', 'Beckasin', 'Font_292056_beckasin.swf', 1, '', NULL, NULL),
(9, 2, 'Calligraphy', 'KG All Things New', 'Font_292057_kg_all_things_new.swf', 1, '', NULL, NULL),
(10, 2, 'Calligraphy', 'Quincaille', 'Font_292058_quincaille.swf', 1, '', NULL, NULL),
(11, 2, 'Calligraphy', 'Vegas Desert', 'Font_292059_vegas_desert.swf', 1, '', NULL, NULL),
(12, 2, 'Calligraphy', 'Yoghurt', 'Font_292059_yoghurt.swf', 1, '', NULL, NULL),
(13, 2, 'Calligraphy', 'Zyphyte', 'Font_292059_zyphyte.swf', 1, '', NULL, NULL),
(14, 3, 'Cartoon', 'Brewsky', 'Font_292059_brewsky.swf', 1, '', NULL, NULL),
(15, 3, 'Cartoon', 'Cactus Sandwich Plain', 'Font_292059_cactus_sandwich_plain.swf', 1, '', NULL, NULL),
(16, 3, 'Cartoon', 'Children in Need', 'Font_292059_children_in_need.swf', 1, '', NULL, NULL),
(17, 3, 'Cartoon', 'Fontenay Fancy', 'Font_292059_fontenay_fancy.swf', 1, '', NULL, NULL),
(18, 3, 'Cartoon', 'GARFIELD the CAT', 'Font_292059_garfield_the_cat.swf', 1, '', NULL, NULL),
(19, 3, 'Cartoon', 'Janda Quirkygirl', 'Font_292059_janda_quirkygirl.swf', 1, '', NULL, NULL),
(20, 3, 'Cartoon', 'Janda Silly Monkey', 'Font_292059_janda_silly_monkey.swf', 1, '', NULL, NULL),
(21, 3, 'Cartoon', 'Janda Stylish Script', 'Font_292059_janda_stylish_script.swf', 1, '', NULL, NULL),
(22, 3, 'Cartoon', 'KG Be Still & Know', 'Font_292059_kg_be_still_know.swf', 1, '', NULL, NULL),
(23, 4, 'Curly', 'Janda Shine Your Light On Us', 'Font_292059_janda_shine_your_light_on_us.swf', 1, '', NULL, NULL),
(24, 4, 'Curly', 'Janda Swirlygirl', 'Font_292059_janda_swirlygirl.swf', 1, '', NULL, NULL),
(25, 4, 'Curly', 'KG Keep Your Head Up', 'Font_292059_kg_keep_your_head_up.swf', 1, '', NULL, NULL),
(26, 4, 'Curly', 'KG Kiss Me Slowly', 'Font_292059_kg_kiss_me_slowly.swf', 1, '', NULL, NULL),
(27, 4, 'Curly', 'KG Strawberry Limeade', 'Font_292059_kg_strawberry_limeade.swf', 1, '', NULL, NULL),
(28, 4, 'Curly', 'Leokadia Deco', 'Font_292059_leokadia_deco.swf', 1, '', NULL, NULL),
(29, 4, 'Curly', 'Milkmoustachio', 'Font_292059_milkmoustachio.swf', 1, '', NULL, NULL),
(30, 4, 'Curly', 'Monster Paparazzi', 'Font_292059_monster_paparazzi.swf', 1, '', NULL, NULL),
(31, 5, 'Greek', 'Adonais', 'Font_292059_adonais.swf', 1, '', NULL, NULL),
(32, 5, 'Greek', 'Ancient Geek', 'Font_292059_ancient_geek.swf', 1, '', NULL, NULL),
(33, 5, 'Greek', 'Dalek', 'Font_292059_dalek.swf', 1, '', NULL, NULL),
(34, 5, 'Greek', 'Diogenes', 'Font_292059_diogenes.swf', 1, '', NULL, NULL),
(35, 5, 'Greek', 'Futhark AOE', 'Font_292059_futhark_aoe.swf', 1, '', NULL, NULL),
(36, 5, 'Greek', 'Olympus', 'Font_292059_olympus.swf', 1, '', NULL, NULL),
(37, 5, 'Greek', 'Sanhedrin', 'Font_292059_sanhedrin.swf', 1, '', NULL, NULL),
(38, 6, 'Handwriting', 'A Year Without Rain', 'Font_292059_a_year_without_rain.swf', 1, '', NULL, NULL),
(39, 6, 'Handwriting', 'B de bonita shadow', 'Font_292059_b_de_bonita_shadow_rotulo.swf', 1, '', NULL, NULL),
(40, 6, 'Handwriting', 'Culita', 'Font_292059_culita.swf', 1, '', NULL, NULL),
(41, 6, 'Handwriting', 'Janda Someone Like You', 'Font_292059_janda_someone_like_you.swf', 1, '', NULL, NULL),
(42, 6, 'Handwriting', 'Just Realize', 'Font_292059_just_realize.swf', 1, '', NULL, NULL),
(43, 6, 'Handwriting', 'KG Mercy in the Morning', 'Font_292059_kg_mercy_in_the_morning.swf', 1, '', NULL, NULL),
(44, 6, 'Handwriting', 'KG Mullally', 'Font_292059_kg_mullally.swf', 1, '', NULL, NULL),
(45, 6, 'Handwriting', 'KG Seven Sixteen', 'Font_292059_kg_seven_sixteen.swf', 1, '', NULL, NULL),
(46, 6, 'Handwriting', 'soul handwriting_free-version', 'Font_292059_soul_handwriting.swf', 1, '', NULL, NULL),
(47, 6, 'Handwriting', 'Stars From Our Eyes', 'Font_292100_stars_from_our_eyes.swf', 1, '', NULL, NULL),
(48, 6, 'Handwriting', 'Talking to the Moon', 'Font_292100_talking_to_the_moon.swf', 1, '', NULL, NULL),
(49, 7, 'Horror', 'Arachnids', 'Font_292100_arachnids.swf', 1, '', NULL, NULL),
(50, 7, 'Horror', 'Exquisite Corpse', 'Font_292100_exquisite_corpse.swf', 1, '', NULL, NULL),
(51, 7, 'Horror', 'Ghoulish', 'Font_292100_ghoulish.swf', 1, '', NULL, NULL),
(52, 7, 'Horror', 'Nightbird', 'Font_292100_nightbird.swf', 1, '', NULL, NULL),
(53, 7, 'Horror', 'Scars Before Christmas', 'Font_292100_scars_before_christmas.swf', 1, '', NULL, NULL),
(54, 7, 'Horror', 'Spyced', 'Font_292100_spyced.swf', 1, '', NULL, NULL),
(55, 7, 'Horror', 'Swamp Witch', 'Font_292100_swamp_witch.swf', 1, '', NULL, NULL),
(56, 7, 'Horror', 'the haine au carre !', 'Font_292100_the_haine_au_carre.swf', 1, '', NULL, NULL),
(57, 8, 'Script', 'Annabel Script', 'Font_292100_annabel_script.swf', 1, '', NULL, NULL),
(58, 8, 'Script', 'Aspire', 'Font_292100_aspire.swf', 1, '', NULL, NULL),
(59, 8, 'Script', 'Brannboll Fet', 'Font_292100_brannboll_fet.swf', 1, '', NULL, NULL),
(60, 8, 'Script', 'Clipper Script (Personal Use)', 'Font_292100_clipper_script_personal_use.swf', 1, '', NULL, NULL),
(61, 8, 'Script', 'KG Eyes Wide Open', 'Font_292100_kg_eyes_wide_open.swf', 1, '', NULL, NULL),
(62, 8, 'Script', 'Pacifico', 'Font_292100_pacifico.swf', 1, '', NULL, NULL),
(63, 8, 'Script', 'Scriptina Pro', 'Font_292100_scriptina_pro.swf', 1, '', NULL, NULL),
(64, 8, 'Script', 'Shit Happens', 'Font_292100_shit_happens.swf', 1, '', NULL, NULL),
(65, 9, 'Sports', 'Allstar', 'Font_292100_allstar.swf', 1, '', NULL, NULL),
(66, 9, 'Sports', 'Ascent 2 Stardom', 'Font_292100_ascent_2_stardom.swf', 1, '', NULL, NULL),
(67, 9, 'Sports', 'Athletic', 'Font_292100_athletic.swf', 1, '', NULL, NULL),
(68, 9, 'Sports', 'Freshman', 'Font_292100_freshman.swf', 1, '', NULL, NULL),
(69, 9, 'Sports', 'Interceptor', 'Font_292100_interceptor.swf', 1, '', NULL, NULL),
(70, 9, 'Sports', 'Kroftsmann', 'Font_292100_kroftsmann.swf', 1, '', NULL, NULL),
(71, 9, 'Sports', 'Premier League with Lion Number', 'Font_292100_premier_league_with_lion_number.swf', 1, '', NULL, NULL),
(72, 9, 'Sports', 'Rainy Days', 'Font_292101_rainy_days.swf', 1, '', NULL, NULL),
(73, 9, 'Sports', 'Sports World', 'Font_292101_sports_world.swf', 1, '', NULL, NULL),
(74, 9, 'Sports', 'Varsity', 'Font_292101_varsity.swf', 1, '', NULL, NULL);
");
$this->endSetup();
?>
	