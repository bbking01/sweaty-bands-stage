<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Helper_Image extends Mage_Core_Helper_Abstract
{
    protected $_defaultWidth = 494;
    protected $_defaultHeight = 494;
    protected $_mainWidth;
    protected $_mainHeight;
    protected $_aspect;
    protected $_config;

    public function __construct(){
        $this->_mainWidth = $this->_defaultWidth;
        $this->_mainHeight = $this->_defaultHeight;

        $this->_config = Mage::getStoreConfig('shoppersettings');

        if ($this->_config['images']['width'] > 0) {
            $this->_mainWidth = intval($this->_config['images']['width']);
            $this->_mainHeight = intval($this->_config['images']['width']);
        }
        if ($this->_config['images']['height'] > 0) {
            $this->_mainHeight = intval($this->_config['images']['height']);
        }

        $this->_aspect = $this->_mainWidth / $this->_mainHeight;
    }
    
    public function getDefaultSize()
    {
        return array($this->_defaultWidth, $this->_defaultHeight);
    }

    public function getMainSize()
    {
        return array($this->_mainWidth, $this->_mainHeight);
    }

    public function getThumbSize()
    {
        $thumbArea = $this->_mainWidth - 92 - 8*3;
        $thumbWidth = round($thumbArea / 3);
        return array($thumbWidth, $this->_calculateHeight($thumbWidth));
    }

    protected function _calculateHeight($width)
    {
        return round($width / $this->_aspect);
    }

    public function calculateHeight($width)
    {
        if ($this->_config['images']['keep_ratio']) {
            return round($width / $this->_aspect);
        } else {
            return round($width / ($this->_defaultWidth / $this->_defaultHeight));
        }
    }
}