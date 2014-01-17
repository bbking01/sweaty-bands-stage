<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pp
 * Date: 11-10-21
 * Time: 0:10
 */

class Unirgy_StoreLocator_Model_Settings_Url
    extends Mage_Core_Model_Config_Data
{
    /**
     * @var string
     */
    protected $default_url = 'ustorelocator/location/map';

    /**
     * @return void
     */
    protected function _afterSave()
    {
        $target_url = $this->getDefaultUrl();
        $source_url = $this->getData('value');
        $app        = Mage::app();
        $storeIds   = array();
        switch ($this->getData('scope')) {
            case 'websites':
                $website  = $app->getWebsite($this->getData('website_code'));
                $storeIds = $website->getStoreIds();
                break;
            case 'stores' :
                $storeIds = (array)$app->getStore($this->getStoreCode())->getId();
                break;
            default :
                $storeIds = (array)$app->getStore()->getId();
                break;
        }
        try {
            /* @var $rewrite Mage_Core_Model_Url_Rewrite */
            $rewrite = Mage::getModel('core/url_rewrite');

            foreach ($storeIds as $storeId) {
                $id_path     = 'storelocator/' . $storeId;
                $cur_rewrite = clone $rewrite;
                $cur_rewrite->loadByIdPath($id_path);
                if (empty($source_url)) {
                    $cur_rewrite->delete();
                } else {
                    $cur_rewrite->setData('store_id', $storeId)
                        ->setData('id_path', $id_path)
                        ->setData('is_system', 0)
                        ->setData('target_path', $target_url)
                        ->setData('request_path', $source_url)
                        ->save();
                }
            }

        } catch (Exception $e) {
            return;
        }
    }

    /**
     * @return string
     */
    public function getDefaultUrl()
    {
        return $this->default_url;
    }

    /**
     * @param string $url
     * @return Unirgy_StoreLocator_Model_Settings_Url
     */
    public function setDefaultUrl($url)
    {
        $url               = trim($url, '/');
        $this->default_url = $url;
        return $this;
    }
}
