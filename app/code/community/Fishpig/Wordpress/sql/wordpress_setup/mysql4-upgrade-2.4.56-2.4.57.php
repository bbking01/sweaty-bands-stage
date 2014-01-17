<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

	$this->startSetup();
	
	try {
		$this->run("
			SELECT * FROM {$this->getTable('wordpress/association_type')} LIMIT 1;
			SELECT * FROM {$this->getTable('wordpress/association')} LIMIT 1;
		");
	}
	catch (Exception $e) {
		$types = array(
			1 => array('product', 'post'),
			2 => array('product', 'category'),
			3 => array('category', 'post'),
			4 => array('category', 'category'),
			5 => array('cms_page', 'post'),
			6 => array('cms_page', 'category'),
		);
		
		$sql = "
		DROP TABLE IF EXISTS {$this->getTable('wordpress/association_type')};
		
		CREATE TABLE {$this->getTable('wordpress/association_type')} (
			`type_id` int(11) unsigned NOT NULL auto_increment,
			`object` varchar(16) NOT NULL default '',
			`wordpress_object` varchar(16) NOT NULL default '',
			PRIMARY KEY(type_id)
		)  ENGINE=InnoDB DEFAULT CHARSET=utf8;
		";
	
		$table = $this->getTable('wordpress/association_type');
		
		foreach($types as $typeId => $data) {
			$sql .= sprintf("INSERT INTO %s VALUES (%d, '%s', '%s');\n", $table, $typeId, $data[0], $data[1]);
		}
	
		$sql .= "DROP TABLE IF EXISTS {$this->getTable('wordpress/association')};
	
			CREATE TABLE {$this->getTable('wordpress/association')} (
				`assoc_id` int(11) unsigned NOT NULL auto_increment,
				`type_id` int(3) unsigned NOT NULL default 0,
				`object_id` int(11) unsigned NOT NULL default 0,
				`wordpress_object_id` int(11) unsigned NOT NULL default 0,
				`position` int(4) unsigned NOT NULL default 4444,
				`store_id` smallint(5) unsigned NOT NULL default 0,
				PRIMARY KEY (`assoc_id`),
				CONSTRAINT `FK_WP_ASSOC_TYPE_ID_WP_ASSOC_TYPE` FOREIGN KEY (`type_id`) REFERENCES `{$this->getTable('wordpress/association_type')}` (`type_id`) ON DELETE CASCADE ON UPDATE CASCADE,
				KEY `FK_WP_ASSOC_TYPE_ID_WP_ASSOC_TYPE` (`type_id`),
				KEY `FK_STORE_ID_WP_ASSOC` (`store_id`),
				CONSTRAINT `FK_STORE_ID_WP_ASSOC` FOREIGN KEY (`store_id`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";	
	
		try {
			$this->run($sql);
			
			try {
				$db = $this->getConnection();
				
				$tables = array(
					'wordpress_product_post' => array(
						'object' => 'product', 'wordpress_object' => 'post',
					),
					'wordpress_product_category' => array(
						'object' => 'product', 'wordpress_object' => 'category',
					),
					'wordpress_category_post' => array(
						'object' => 'category', 'wordpress_object' => 'post',
					),
					'wordpress_category_category' => array(
						'object' => 'category', 'wordpress_object' => 'category',
					),
				);
				
				foreach($tables as $table => $data) {
					$sql = sprintf('SELECT * FROM %s', $this->getTable($table));
					
					if (($results = $db->fetchAll($sql)) !== array()) {
						$object = $data['object'];
						$wpObject = $data['wordpress_object'];
						
						foreach($types as $typeId => $tData) {
							if ($tData[0] === $object && $tData[1] === $wpObject) {
								foreach($results as $result) {
									$db->insert($this->getTable('wordpress/association'), array(
										'type_id' => $typeId,
										'object_id' => $result[$object . '_id'],
										'wordpress_object_id' => $result[$wpObject . '_id'],
										'store_id' => $result['store_id'],
										'position' => $result['position'] ? $result['position'] : 4444,
									));
								}
							}
						}
					}
				}
			}
			catch (Exception $e) {
				Mage::helper('wordpress')->log($e);
			}
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);
			throw $e;
		}
	}
		
	$this->endSetup();
