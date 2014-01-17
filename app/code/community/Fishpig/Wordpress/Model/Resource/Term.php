<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Model_Resource_Term extends Fishpig_Wordpress_Model_Resource_Abstract
{
	public function _construct()
	{
		$this->_init('wordpress/term', 'term_id');
	}
	
	/**
	 * Custom load SQL to combine required tables
	 *
	 * @param string $field
	 * @param string|int $value
	 * @param Mage_Core_Model_Abstract $object
	 */
	protected function _getLoadSelect($field, $value, $object)
	{
		$select = $this->_getReadAdapter()->select()
			->from(array('main_table' => $this->getMainTable()));
		
		if (strpos($field, '.') !== false) {
			$select->where($field . '=?', $value);
		}
		else {
			$select->where("main_table.{$field}=?", $value);
		}
			
		$select->join(
			array('taxonomy' => $this->getTable('wordpress/term_taxonomy')),
			'`main_table`.`term_id` = `taxonomy`.`term_id`',
			array('term_taxonomy_id', 'taxonomy', 'description', 'count', 'parent')
		);
		
		if ($object->getTaxonomy()) {
			$select->where('taxonomy.taxonomy=?', $object->getTaxonomy());
		}

		return $select->limit(1);
	}
	
	/**
	 * Loads a category by an array of slugs
	 * The array should be the order of slugs found in the URI
	 * The whole slug array must match (including parent relationsips)
	 *
	 * @param array $slugs
	 * @param Fishpig_Wordpress_Model_Term $object
	 * @return false
	 */
	public function loadBySlugs(array $slugs, Fishpig_Wordpress_Model_Term $object)
	{
		$slugs = array_reverse($slugs);
		$primarySlug = array_shift($slugs);

		try {
			$object->loadBySlug($primarySlug);
			
			if ($object->getId()) {
				$category = $object;
				
				foreach($slugs as $slug) {
					$parent = Mage::getModel($object->getResourceName())->loadBySlug($slug);
					
					if ($parent->getId() !== $category->getParent()) {
						throw new Exception('This path just ain\'t right, bro!');
					}
					
					$category->setParentTerm($parent);
					$category = $parent;
				}

				if (!$category->getParentId()) {
					return true;
				}
			}
		}
		catch (Exception $e) {}
	
		$object->setData(array())->setId(null);

		return false;
	}
}
