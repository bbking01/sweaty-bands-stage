<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Block_Navigation extends Mage_Catalog_Block_Navigation
{

	/**
	 * columns html
	 *
	 * @var array
	 */
	protected $_columnHtml;

	/*
	 * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection
	 */
	protected $_leftCategories;

	/**
	 * top level parent category for current category
	 *
	 * @var int
	 */
	protected $_parent;

	protected function _construct()
    {
        $path = $this->getCurrentCategoryPath();
        $parent = $path[count($path)-1];
        if (!$parent) {
            $parent = Mage::app()->getStore()->getRootCategoryId();
        }
        $this->_parent = $parent;
    }


	/**
	 * render category html
	 *
	 * @param Mage_Catalog_Model_Category $category
	 * @param integer $level
	 * @param array $levelClass
	 * @return string
	 */
	public function drawOpenCategoryItem($category, $level = 0, array $levelClass = null)
	{
		$html = array();

		if (!$category->getIsActive()) return '';

		if (!isset($levelClass)) $levelClass = array();
		$combineClasses = array();

		$combineClasses[] = 'level' . $level;
		if ($this->_isCurrentCategory($category)) {
			$combineClasses[] = 'active';
		} else {
			$combineClasses[] = $this->isCategoryActive($category) ? 'parent' : 'inactive';
		}
		$levelClass[] = implode('-', $combineClasses);
		$levelClass = array_merge($levelClass, $combineClasses);
		$levelClass[] = $this->_getClassNameFromCategoryName($category);

		$html[1] = '<a href="' . $this->getCategoryUrl($category) . '">' . $this->escapeHtml($category->getName()) . '</a>' . "\n";

		if ( in_array($category->getId(), $this->getCurrentCategoryPath()) ) {
			$children = $this->_getLeftCategoryCollection()
					->addIdFilter($category->getChildren());

			$hasChildren = $children && ($childrenCount = count($children));
			if ($hasChildren) {
				$htmlChildren = '';

				foreach ($children as $i => $child)
				{
					$class = array();
					if ($childrenCount == 1) {
						$class[] = 'only';
					}
					else
					{
						if (!$i) $class[] = 'first';
						if ($i == $childrenCount - 1) $class[] = 'last';
					}
					$htmlChildren .= $this->drawOpenCategoryItem($child, $level + 1, $class);
				}

				if (!empty($htmlChildren)) {
					$levelClass[] = 'open';

					$html[2] = '<ul>' . "\n"
							. $htmlChildren . "\n"
							. '</ul>';
				}
			}
		}

		$html[0] = sprintf('<li class="%s">', implode(" ", $levelClass)) . "\n";
		$html[3] = "\n" . '</li>' . "\n";

		ksort($html);
		return implode('', $html);
	}

	/**
	 * Convert the category name into a string that can be used as a css class
	 *
	 * @param Mage_Catalog_Model_Category $category
	 * @return string
	 */
	protected function _getClassNameFromCategoryName($category)
	{
		$name = $category->getName();
		$name = preg_replace('/-{2,}/', '-', preg_replace('/[^a-z-]/', '-', strtolower($name)));
		while ($name && $name{0} == '-') $name = substr($name, 1);
		while ($name && substr($name, -1) == '-') $name = substr($name, 0, -1);
		return $name;
	}

	/**
	 * Check if the current category matches the passed in category
	 *
	 * @param Mage_Catalog_Model_Category $category
	 * @return bool
	 */
	protected function _isCurrentCategory($category)
	{
		return ($cat = $this->getCurrentCategory()) && $cat->getId() == $category->getId();
	}

	/**
	 * return top level category name
	 *
	 * @return string
	 */
	public function getBlockTitle() {
		if ( $this->_parent == Mage::app()->getStore()->getRootCategoryId() ) {
			return '';
		} else {
			return Mage::getModel('catalog/category')->load($this->_parent)->getName();
		}
	}

	/**
	 * Get sibling catagories
	 *
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection
	 */
	public function getLeftCategories()
	{
        $parent = Mage::getModel('catalog/category')->load($this->_parent);
        if ( $parent['is_active'] == 0 || $parent['include_in_menu'] == 0 )
            return null;

		if (!isset($this->_leftCategories)) {
			$this->_leftCategories = $this->_getLeftCategoryCollection()
				->addIdFilter($parent->getChildren());
		}
		return $this->_leftCategories;
	}

	/**
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection
	 */
	protected function _getLeftCategoryCollection()
	{
		$collection = Mage::getResourceModel('catalog/category_collection');
		$collection->addAttributeToSelect('url_key')
				->addAttributeToSelect('name')
				->addAttributeToSelect('all_children')
				->addAttributeToFilter('is_active', 1)
				->addAttributeToFilter('include_in_menu', 1)
				->setOrder('position', 'ASC')
				->joinUrlRewrite();
		return $collection;
	}

	/**
	 * Render category to html
	 *
	 * @param Mage_Catalog_Model_Category $category
	 * @param int Nesting level number
	 * @param boolean Whether ot not this item is last, affects list item class
	 * @param boolean Whether ot not this item is first, affects list item class
	 * @param boolean Whether ot not this item is outermost, affects list item class
	 * @param string Extra class of outermost list items
	 * @param string If specified wraps children list in div with this class
	 * @param boolean Whether ot not to add on* attributes to list item
	 * @return string
	 */
	protected function _renderCategoryMenuItemHtml($category, $level = 0, $isLast = false, $isFirst = false,
	                                               $isOutermost = false, $outermostItemClass = '', $childrenWrapClass = '', $noEventAttributes = false)
	{
		if (!$category->getIsActive()) {
			return '';
		}
		$html = array();

		// get all children
		if (Mage::helper('catalog/category_flat')->isEnabled()) {
			$children = (array)$category->getChildrenNodes();
			$childrenCount = count($children);
		} else {
			$children = $category->getChildren();
			$childrenCount = $children->count();
		}
		$hasChildren = ($children && $childrenCount);

		// select active children
		$activeChildren = array();
		foreach ($children as $child) {
			if ($child->getIsActive()) {
				$activeChildren[] = $child;
			}
		}
		$activeChildrenCount = count($activeChildren);
		$hasActiveChildren = ($activeChildrenCount > 0);

		// prepare list item html classes
		$classes = array();
		$classes[] = 'level' . $level;
		$classes[] = 'nav-' . $this->_getItemPosition($level);
		if ($this->isCategoryActive($category)) {
			$classes[] = 'active';
		}
		$linkClass = '';
		if ($isOutermost && $outermostItemClass) {
			$classes[] = $outermostItemClass;
			$linkClass = ' class="' . $outermostItemClass . '"';
		}
		if ($isFirst) {
			$classes[] = 'first';
		}
		if ($isLast) {
			$classes[] = 'last';
		}
		if ($hasActiveChildren) {
			$classes[] = 'parent';
		}

		// prepare list item attributes
		$attributes = array();
		if (count($classes) > 0) {
			$attributes['class'] = implode(' ', $classes);
		}
		if ($hasActiveChildren && !$noEventAttributes) {
			$attributes['onmouseover'] = 'toggleMenu(this,1)';
			$attributes['onmouseout'] = 'toggleMenu(this,0)';
		}

		// assemble list item with attributes
		$htmlLi = '<li';
		foreach ($attributes as $attrName => $attrValue) {
			$htmlLi .= ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
		}
		$htmlLi .= '>';
		$html[] = $htmlLi;

		$html[] = '<a href="' . $this->getCategoryUrl($category) . '"' . $linkClass . '>';
		$html[] = '<span>' . $this->escapeHtml($category->getName()) . '</span>';
		$html[] = '</a>';

        $columnItemsNum = array();
		if ($level == 0 && $activeChildrenCount) {
			$items_per_column = Mage::getStoreConfig('shoppersettings/navigation/column_items');
            $columns = ceil($activeChildrenCount / $items_per_column);
			$columnItemsNum = array_fill(0, $columns, $items_per_column);
			$this->_columnHtml = array();
		}

		// render children
		$htmlChildren = '';
		$j = 0; //child index
		$i = 0; //column index
		$itemsCount = $activeChildrenCount;
        if (isset($columnItemsNum[$i])) {
            $itemsCount = $columnItemsNum[$i];
        }
		foreach ($activeChildren as $child) {

			if ($level == 0) {
				$isLast = (($j + 1) == $itemsCount || $j == $activeChildrenCount - 1);
				if ($isLast) {
					$i++;
                    if (isset($columnItemsNum[$i])) {
                        $itemsCount += $columnItemsNum[$i];
                    }
				}
			} else {
				$isLast = ($j == $activeChildrenCount - 1);
			}

			$childHtml = $this->_renderCategoryMenuItemHtml(
				$child,
				($level + 1),
				$isLast,
				($j == 0),
				false,
				$outermostItemClass,
				$childrenWrapClass,
				$noEventAttributes
			);
			if ($level == 0) {
				$this->_columnHtml[] = $childHtml;
			} else {
				$htmlChildren .= $childHtml;
			}
			$j++;
		}

		if ($level == 0 && $this->_columnHtml) {
			$i = 0;
			foreach ($columnItemsNum as $columnNum) {
				$chunk = array_slice($this->_columnHtml, $i, $columnNum);
				$i += $columnNum;
				$htmlChildren .= '<li ' . (count($this->_columnHtml) == $i ? 'class="last"' : '') . '><ol>';
				foreach ($chunk as $item) {
					$htmlChildren .= $item;
				}
				$htmlChildren .= '</ol></li>';
			}
		}

		if (!empty($htmlChildren)) {
			if ($childrenWrapClass) {
				$html[] = '<div class="' . $childrenWrapClass . '">';
			}
			$html[] = '<ul class="level' . $level . '">';
			$html[] = $htmlChildren;
			$html[] = '</ul>';
			if ($childrenWrapClass) {
				$html[] = '</div>';
			}
		}

		$html[] = '</li>';

		$html = implode("\n", $html);
		return $html;
	}


    /**
     * Render categories menu in selectbox element
     *
     * @param int Level number for list item class to start from
     * @param string Extra class of outermost list items
     * @param string If specified wraps children list in div with this class
     * @return string
     */
    public function renderCategoriesSelectOptions($level = 0, $outermostItemClass = '', $childrenWrapClass = '')
    {
        $activeCategories = array();
        foreach ($this->getStoreCategories() as $child) {
            if ($child->getIsActive()) {
                $activeCategories[] = $child;
            }
        }
        $activeCategoriesCount = count($activeCategories);
        $hasActiveCategoriesCount = ($activeCategoriesCount > 0);

        if (!$hasActiveCategoriesCount) {
            return '';
        }

        $html = '<option value="">' . $this->__('- Please select category -') . '</option>';
        $j = 0;
        foreach ($activeCategories as $category) {
            $html .= $this->_renderCategorySelectOption(
                $category,
                $level,
                ($j == $activeCategoriesCount - 1),
                ($j == 0),
                true,
                $outermostItemClass,
                $childrenWrapClass,
                true
            );
            $j++;
        }

        return $html;
    }


    /**
     * Render category to html
     *
     * @param Mage_Catalog_Model_Category $category
     * @param int Nesting level number
     * @param boolean Whether ot not this item is last, affects list item class
     * @param boolean Whether ot not this item is first, affects list item class
     * @param boolean Whether ot not this item is outermost, affects list item class
     * @param string Extra class of outermost list items
     * @param string If specified wraps children list in div with this class
     * @param boolean Whether ot not to add on* attributes to list item
     * @return string
     */
    protected function _renderCategorySelectOption($category, $level = 0, $isLast = false, $isFirst = false,
                                                   $isOutermost = false, $outermostItemClass = '', $childrenWrapClass = '', $noEventAttributes = false)
    {
        if (!$category->getIsActive()) {
            return '';
        }
        $html = array();

        // get all children
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $children = (array)$category->getChildrenNodes();
        } else {
            $children = $category->getChildren();
        }

        // select active children
        $activeChildren = array();
        foreach ($children as $child) {
            if ($child->getIsActive()) {
                $activeChildren[] = $child;
            }
        }

        $active = '';
        if ($this->isCategoryActive($category)) {
            $active = 'selected="selected"';
        }
        // assemble list item with attributes
        $html[] = '<option value="'.$this->getCategoryUrl($category).'" '.$active.'>' . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;',$level) . $this->escapeHtml($category->getName()) . '</option>';

        // render children
        $htmlChildren = '';
        foreach ($activeChildren as $child) {
            $childHtml = $this->_renderCategorySelectOption(
                $child,
                ($level + 1),
                0,
                0,
                false,
                $outermostItemClass,
                $childrenWrapClass,
                $noEventAttributes
            );
            $htmlChildren .= $childHtml;
        }

        if (!empty($htmlChildren)) {
            $html[] = $htmlChildren;
        }

        $html = implode("\n", $html);
        return $html;
    }


}